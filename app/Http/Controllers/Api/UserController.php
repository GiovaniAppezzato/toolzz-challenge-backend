<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreUserRequest;
use App\Http\Requests\Api\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index(): JsonResource
    {
        return UserResource::collection(User::paginate());
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function store(StoreUserRequest $request): UserResource
    {
        return DB::transaction(function () use ($request) {
            $user = $this->userService->create($request->validated());

            return new UserResource($user, 201);
        });
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        return DB::transaction(function () use ($request, $user) {
            $user = $this->userService->update($user, $request->validated());

            return new UserResource($user);
        });
    }

    public function destroy(User $user): Response
    {
        $user->delete();

        return response()->noContent();
    }
}
