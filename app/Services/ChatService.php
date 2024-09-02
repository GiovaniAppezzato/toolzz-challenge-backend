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
            ->with(['messagesSent' => function ($query) use ($user) {
                $query->where('receiver_id', $user->id)
                    ->orWhere('sender_id', $user->id)
                    ->latest('created_at')
                    ->limit(1);
            }, 'messagesReceived' => function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id)
                    ->latest('created_at')
                    ->limit(1);
            }])
            ->orderBy('unread_messages_count', 'desc')
            ->get()
            ->map(function ($user) {
                return $this->addLastMessage($user);
            });
    }

    protected function addLastMessage($user): User
    {
        $user->last_message = $user->messagesSent
            ->merge($user->messagesReceived)
            ->sortByDesc('created_at')
            ->first();

        return $user;
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

        return $this->paginateReverse($query, 25);
    }

    private function paginateReverse($query, int $perPage): \Illuminate\Pagination\LengthAwarePaginator
    {
        $paginatedData = $query->paginate($perPage);
        return $paginatedData->setCollection($paginatedData->getCollection()->reverse());
    }
}
