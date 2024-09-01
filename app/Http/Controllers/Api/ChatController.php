<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\{StoreMessageRequest,GetMessagesRequest,GetUsersForConversationRequest,SetMessagesAsReadRequest};
use App\Http\Resources\{MessageResource,UserForConversationResource};
use App\Models\User;
use App\Events\MessageSent;
use App\Services\ChatService;

class ChatController extends Controller
{
    public function __construct(
        protected ChatService $chatService
    ) {}

    public function index(GetUsersForConversationRequest $request): JsonResource
    {
        return UserForConversationResource::collection(
            $this->chatService->getUsersForConversation($request->validated())
        );
    }

    public function show(GetMessagesRequest $request): JsonResource
    {
        $user = User::findOrFail($request->input('user_id'));

        $messages = $this->chatService->getMessages($user, $request->input('search'));

        return MessageResource::collection($messages);
    }

    public function store(StoreMessageRequest $request): MessageResource
    {
        return DB::transaction(function () use ($request) {
            $validated = $request->validated();

            /** @var User $user */
            $user = Auth::user();

            $message = $user->messagesSent()->create($validated);

            broadcast(new MessageSent($message));

            return new MessageResource($message, 201);
        });
    }

    public function setMessagesAsRead(SetMessagesAsReadRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $user->messagesReceived()
            ->where('sender_id', $request->input('user_id'))
            ->update(['is_read' => true]);

        return response()->noContent();
    }
}
