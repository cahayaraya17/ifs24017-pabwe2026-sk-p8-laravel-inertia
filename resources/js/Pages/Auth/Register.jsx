import { Head, Link, useForm } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import Spinner from '../../Components/Spinner';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        username: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post('/register');
    };

    const fields = [
        ['name', 'Nama lengkap', 'text'],
        ['username', 'Username', 'text'],
        ['email', 'Email', 'email'],
        ['password', 'Password (min. 8 karakter)', 'password'],
        ['password_confirmation', 'Ulangi password', 'password'],
    ];

    return (
        <PublicLayout>
            <Head title="Daftar" />
            <div className="mx-auto mt-6 max-w-sm rounded border bg-white p-6 shadow-sm">
                <h1 className="mb-4 text-xl font-bold">Daftar Penulis</h1>
                <form onSubmit={submit} className="space-y-4">
                    {fields.map(([key, label, type]) => (
                        <div key={key}>
                            <label htmlFor={`register-${key}`} className="mb-1 block text-sm font-medium">
                                {label}
                            </label>
                            <input
                                id={`register-${key}`}
                                type={type}
                                value={data[key]}
                                onChange={(e) => setData(key, e.target.value)}
                                className="w-full rounded border px-3 py-2"
                            />
                            {errors[key] && <p className="mt-1 text-sm text-red-600">{errors[key]}</p>}
                        </div>
                    ))}
                    <button
                        id="register-submit"
                        type="submit"
                        disabled={processing}
                        className="flex w-full cursor-pointer items-center justify-center gap-2 rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 disabled:opacity-60"
                    >
                        {processing && <Spinner />}
                        Daftar
                    </button>
                </form>
                <p className="mt-4 text-sm text-slate-500">
                    Sudah punya akun?{' '}
                    <Link href="/login" className="text-indigo-700 underline hover:text-indigo-800">
                        Masuk
                    </Link>
                </p>
            </div>
        </PublicLayout>
    );
}