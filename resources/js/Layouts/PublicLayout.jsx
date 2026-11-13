import { Link, router, usePage } from '@inertiajs/react';
import { LogIn, UserPlus } from '../Components/Icons';

export default function PublicLayout({ children }) {
    const { auth, flash } = usePage().props;
    const user = auth.user;
    const panel =
        user?.role === 'admin'
            ? { href: '/admin/users', label: 'Panel Admin' }
            : { href: '/dashboard/posts', label: 'Dashboard Penulis' };

    const link = 'flex cursor-pointer items-center gap-2 rounded px-3 py-2 text-sm';

    return (
        <div className="flex min-h-screen flex-col bg-white text-slate-800">
            <header className="sticky top-0 z-40 border-b bg-white/90 backdrop-blur">
                <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
                    <Link href="/" className="text-xl font-extrabold tracking-tight text-indigo-700">
                        BlogVerse<span className="text-slate-900"> Pro</span>
                    </Link>
                    <nav className="flex items-center gap-2">
                        <Link href="/articles" className={`${link} hover:bg-slate-100`}>
                            Artikel
                        </Link>
                        {user ? (
                            <>
                                <Link href={panel.href} className={`${link} bg-indigo-50 text-indigo-700`}>
                                    <span className="flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">
                                        {user.name.charAt(0).toUpperCase()}
                                    </span>
                                    {panel.label}
                                </Link>
                                <button
                                    type="button"
                                    onClick={() => router.post('/logout')}
                                    className={`${link} border hover:bg-slate-100`}
                                >
                                    Keluar
                                </button>
                            </>
                        ) : (
                            <>
                                <Link href="/login" className={`${link} border hover:bg-slate-100`}>
                                    <LogIn /> Masuk
                                </Link>
                                <Link href="/register" className={`${link} bg-indigo-600 text-white hover:bg-indigo-700`}>
                                    <UserPlus /> Daftar
                                </Link>
                            </>
                        )}
                    </nav>
                </div>
            </header>

            <main className="mx-auto w-full max-w-6xl flex-1 px-4 py-8">
                {flash?.success && (
                    <div className="mb-4 rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        {flash.success}
                    </div>
                )}
                {children}
            </main>

            <footer className="border-t bg-slate-50">
                <div className="mx-auto flex max-w-6xl flex-col items-center justify-between gap-3 px-4 py-6 text-sm text-slate-500 md:flex-row">
                    <span>&copy; BlogVerse Pro. Ruang menulis untuk semua.</span>
                    <nav className="flex gap-4">
                        <Link href="/" className="hover:text-indigo-600">Beranda Publik</Link>
                        <Link href="/login" className="hover:text-indigo-600">Halaman Login</Link>
                        <Link href="/register" className="hover:text-indigo-600">Halaman Register</Link>
                    </nav>
                </div>
            </footer>
        </div>
    );
}