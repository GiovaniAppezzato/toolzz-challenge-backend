<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Actions\StoreBase64File;

class UserService
{
    public function create(array $data): User
    {
        $user = User::create([
            ...$data,
            'password' => bcrypt($data['password'])
        ]);

        if(isset($data['photo'])) {
            (new StoreBase64File($data['photo'], $user))->handle();
        }

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        if(isset($data['photo'])) {
            if($user->photo) {
                Storage::delete($user->photo->path);
                $user->photo->delete();
            }

            (new StoreBase64File($data['photo'], $user))->handle();
        }

        return $user;
    }
}
