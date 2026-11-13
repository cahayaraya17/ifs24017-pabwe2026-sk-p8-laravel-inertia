import { Head, usePage } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';

// SEMENTARA (Tahap 3): hanya untuk membuktikan login berhasil.
// Dihapus saat dashboard asli dibuat di Tahap 5 dan 8.
export default function Panel({ area }) {
    const { auth } = usePage().props;

    return (
        <PublicLayout>
            <Head title={area} />
            <div className="rounded border bg-white p-6 shadow-sm">
                <h1 className="text-2xl font-bold text-indigo-700">{area}</h1>
                <p className="mt-2 text-slate-600">
                    Login berhasil sebagai <strong>{auth.user.name}</strong> ({auth.user.username}) dengan role{' '}
                    <strong id="current-role">{auth.user.role}</strong>.
                </p>
                <p className="mt-1 text-sm text-slate-400">Halaman sementara Tahap 3.</p>
            </div>
        </PublicLayout>
    );
}
