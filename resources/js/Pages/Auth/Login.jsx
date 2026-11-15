import { Head, Link, useForm } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import Spinner from '../../Components/Spinner';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        username: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post('/login');
    };

    return (
        <PublicLayout>
            <Head title="Masuk" />
            <div className="mx-auto mt-6 max-w-sm rounded border bg-white p-6 shadow-sm">
                <h1 className="mb-4 text-xl font-bold">Masuk</h1>
                <form onSubmit={submit} className="space-y-4">
                    <div>
                        <label htmlFor="login-username" className="mb-1 block text-sm font-medium">
                            Username
                        </label>
                        <input
                            id="login-username"
                            type="text"
                            value={data.username}
                            onChange={(e) => setData('username', e.target.value)}
                            className="w-full rounded border px-3 py-2"
                            autoFocus
                        />
                        {errors.username && <p className="mt-1 text-sm text-red-600">{errors.username}</p>}
                    </div>
                    <div>
                        <label htmlFor="login-password" className="mb-1 block text-sm font-medium">
                            Password
                        </label>
                        <input
                            id="login-password"
                            type="password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            className="w-full rounded border px-3 py-2"
                        />
                        {errors.password && <p className="mt-1 text-sm text-red-600">{errors.password}</p>}
                    </div>
                    <label className="flex cursor-pointer items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                        />
                        Ingat saya
                    </label>
                    <button
                        id="login-submit"
                        type="submit"
                        disabled={processing}
                        className="flex w-full cursor-pointer items-center justify-center gap-2 rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 disabled:opacity-60"
                    >
                        {processing && <Spinner />}
                        Masuk
                    </button>
                </form>
                <p className="mt-4 text-sm text-slate-500">
                    Belum punya akun?{' '}
                    <Link href="/register" className="text-indigo-700 underline hover:text-indigo-800">
                        Daftar sebagai penulis
                    </Link>
                </p>
                <p className="mt-2 text-xs text-slate-600">Akun uji: admin / admin atau member / member</p>
            </div>
        </PublicLayout>
    );
}