import { Head, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Spinner from '../../Components/Spinner';

export default function Edit({ profile }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        _method: 'put',
        name: profile.name ?? '',
        email: profile.email ?? '',
        password: '',
        password_confirmation: '',
        profile_photo: null,
    });

    const submit = (e) => {
        e.preventDefault();
        post('/profile', {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => reset('password', 'password_confirmation', 'profile_photo'),
        });
    };

    const field = 'w-full rounded-lg border px-3 py-2';
    const err = (k) => errors[k] && <p className="mt-1 text-sm text-red-600">{errors[k]}</p>;

    return (
        <AppLayout title="Profil Saya">
            <Head title="Profil Saya" />

            <form onSubmit={submit} className="mx-auto max-w-2xl space-y-5 rounded-xl border bg-white p-6 shadow-sm">
                <div className="flex items-center gap-4">
                    {profile.profile_photo_path ? (
                        <img
                            src={`/storage/${profile.profile_photo_path}`}
                            alt={profile.name}
                            className="h-20 w-20 rounded-full border object-cover"
                        />
                    ) : (
                        <span className="flex h-20 w-20 items-center justify-center rounded-full bg-indigo-600 text-2xl font-bold text-white">
                            {profile.name.charAt(0).toUpperCase()}
                        </span>
                    )}
                    <div>
                        <div className="text-lg font-semibold text-slate-900">@{profile.username}</div>
                        <div className="text-sm capitalize text-slate-500">Role: {profile.role}</div>
                    </div>
                </div>

                <div>
                    <label htmlFor="profile-name" className="mb-1 block text-sm font-medium">Nama</label>
                    <input
                        id="profile-name"
                        type="text"
                        className={field}
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                    />
                    {err('name')}
                </div>

                <div>
                    <label htmlFor="profile-email" className="mb-1 block text-sm font-medium">Email</label>
                    <input
                        id="profile-email"
                        type="email"
                        className={field}
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                    />
                    {err('email')}
                </div>

                <div>
                    <label htmlFor="profile-photo" className="mb-1 block text-sm font-medium">Foto profil (maks. 2 MB)</label>
                    <input
                        id="profile-photo"
                        type="file"
                        accept="image/*"
                        className="block w-full text-sm"
                        onChange={(e) => setData('profile_photo', e.target.files[0] ?? null)}
                    />
                    {err('profile_photo')}
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <div>
                        <label htmlFor="profile-password" className="mb-1 block text-sm font-medium">Password baru (opsional)</label>
                        <input
                            id="profile-password"
                            type="password"
                            className={field}
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                        />
                        {err('password')}
                    </div>
                    <div>
                        <label htmlFor="profile-password-confirmation" className="mb-1 block text-sm font-medium">Ulangi password baru</label>
                        <input
                            id="profile-password-confirmation"
                            type="password"
                            className={field}
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                        />
                    </div>
                </div>

                <div className="flex justify-end">
                    <button
                        id="profile-submit"
                        type="submit"
                        disabled={processing}
                        className="flex cursor-pointer items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-60"
                    >
                        {processing && <Spinner />}
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </AppLayout>
    );
}
