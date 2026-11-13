import { Head } from '@inertiajs/react';

export default function Welcome({ appName }) {
    return (
        <>
            <Head title="Selamat Datang" />
            <main className="flex min-h-screen items-center justify-center bg-slate-50">
                <div className="rounded-2xl bg-white p-10 text-center shadow-lg">
                    <h1 className="text-3xl font-bold text-indigo-600">{appName}</h1>
                    <p className="mt-2 text-slate-600">
                        Inertia + React sudah jalan. Tahap 2 beres.
                    </p>
                </div>
            </main>
        </>
    );
}
