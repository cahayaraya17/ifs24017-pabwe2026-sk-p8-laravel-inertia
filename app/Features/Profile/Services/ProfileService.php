<?php

namespace App\Features\Profile\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function update(User $user, array $data, ?UploadedFile $photo = null): User
    {
        $payload = ['name' => $data['name'], 'email' => $data['email']];

        if (! empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        if ($photo) {
            $user->profile_photo_path && Storage::disk('public')->delete($user->profile_photo_path);
            $payload['profile_photo_path'] = $photo->store('profiles', 'public');
        }

        $user->update($payload);

        return $user;
    }
}