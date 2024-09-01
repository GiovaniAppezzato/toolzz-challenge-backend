<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\{User,Message};

class ChatService
{
    /**
     * Get users for conversation with unread messages count and last message.
     *
     * @param array<string, mixed> $filters
     */
    public function getUsersForConversation(array $filters): \Illuminate\Database\Eloquent\Collection
    {
        /** @var User $user */
        $user = Auth::user();

        return User::query()
            ->with('photo')
            ->withoutMe()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('email', $search);
            })
            ->withCount([
                'messagesSent as unread_messages_count' => function ($query) use($user) {
                    $query->where('is_read', false)
                        ->where('receiver_id', $user->id);
                }
            ])
            ->selectSub(function ($query) {
                $query->from('messages')
                    ->select('content')
                    ->whereColumn('sender_id', 'users.id')
                    ->orWhereColumn('receiver_id', 'users.id')
                    ->orderByDesc('created_at')
                    ->limit(1);
            }, 'last_message')
            ->orderBy('unread_messages_count', 'desc')
            ->get();

        /* return User::query()
            ->withoutMe()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('email', $search);
            })
            ->withCount([
                'messagesSent as unread_messages_count' => function ($query) use($user) {
                    $query->where('is_read', false)
                        ->where('receiver_id', $user->id);
                }
            ])
            ->with([
                'allMessages' => function ($query) use ($user) {
                    $query->where(function($q) use ($user) {
                        $q->where('sender_id', $user->id)
                        ->orWhere('receiver_id', $user->id);
                    })
                    ->latest()
                    ->limit(1);
                }
            ])
            ->orderBy('unread_messages_count', 'desc')
            ->get(); */
    }

    public function getMessages(User $user, ?string $search = null): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Message::query()
            ->with('sender', 'receiver')
            ->where(function ($query) use ($user) {
                $query->where('receiver_id', Auth::user()->id)
                    ->where('sender_id', $user->id);
            })
            ->orWhere(function ($query) use ($user) {
                $query->where('sender_id', Auth::user()->id)
                    ->where('receiver_id', $user->id);
            })
            ->when($search, function ($query, $search) {
                $query->where('content', 'like', "%$search%");
            })
            ->orderBy('created_at', 'desc');

        // Fix to reverse the messages order and keep the pagination.
        $paginatedMessages = $query->paginate(25);
        return $paginatedMessages->setCollection($paginatedMessages->getCollection()->reverse());
    }
}
