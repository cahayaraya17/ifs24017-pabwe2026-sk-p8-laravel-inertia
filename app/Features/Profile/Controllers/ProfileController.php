<?php

namespace App\Features\Profile\Controllers;

use App\Features\Profile\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController
{
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'profile' => $request->user()->only('name', 'username', 'email', 'role', 'profile_photo_path'),
        ]);
    }

    public function update(Request $request, ProfileService $service): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $service->update($request->user(), $data, $request->file('profile_photo'));

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }
}