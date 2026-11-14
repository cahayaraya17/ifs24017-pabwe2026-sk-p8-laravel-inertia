import { Link, router, usePage } from '@inertiajs/react';

export default function AppLayout({ title, actions, children }) {
    const { auth, flash } = usePage().props;
    const user = auth.user;

    const navClass = (active) =>
        `flex cursor-pointer items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition ${
            active
                ? 'bg-indigo-50 text-indigo-700'
                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
        }`;

    const currentUrl = typeof window !== 'undefined' ? window.location.pathname : '';

    return (
        <div className="flex min-h-screen flex-col bg-slate-50 text-slate-800">
            <header className="sticky top-0 z-40 border-b bg-white/95 backdrop-blur">
                <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
                    <div className="flex items-center gap-6">
                        <Link href="/" className="text-xl font-extrabold tracking-tight text-indigo-700">
                            BlogVerse<span className="text-slate-900"> Pro</span>
                        </Link>
                        <nav className="hidden items-center gap-1 md:flex">
                            <Link
                                href="/dashboard/posts"
                                className={navClass(currentUrl.startsWith('/dashboard/posts'))}
                            >
                                Dashboard Penulis
                            </Link>
                            {user?.role === 'admin' && (
                                <Link
                                    href="/admin/users"
                                    className={navClass(currentUrl.startsWith('/admin'))}
                                >
                                    Panel Admin
                                </Link>
                            )}
                            <Link href="/articles" className={navClass(false)}>
                                Lihat Blog Publik
                            </Link>
                        </nav>
                    </div>

                    <div className="flex items-center gap-3">
                        <Link href="/profile" title="Edit profil" className="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1 text-sm hover:bg-slate-100">
                            {user?.profile_photo_path ? (
                                <img
                                    src={`/storage/${user.profile_photo_path}`}
                                    alt={user.name}
                                    className="h-8 w-8 rounded-full object-cover"
                                />
                            ) : (
                                <span className="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">
                                    {user?.name?.charAt(0).toUpperCase()}
                                </span>
                            )}
                            <div className="hidden text-left sm:block">
                                <div className="font-semibold leading-tight text-slate-800">{user?.name}</div>
                                <div className="text-xs capitalize text-slate-500">{user?.role}</div>
                            </div>
                        </Link>

                        <button
                            type="button"
                            onClick={() => router.post('/logout')}
                            className="cursor-pointer rounded-lg border bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-100"
                        >
                            Keluar
                        </button>
                    </div>
                </div>
            </header>

            <main className="mx-auto w-full max-w-6xl flex-1 px-4 py-8">
                {flash?.success && (
                    <div className="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        {flash.success}
                    </div>
                )}
                {flash?.error && (
                    <div className="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        {flash.error}
                    </div>
                )}

                {(title || actions) && (
                    <div className="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                        {title && <h1 className="text-2xl font-bold text-slate-900">{title}</h1>}
                        {actions && <div className="flex flex-wrap items-center gap-2">{actions}</div>}
                    </div>
                )}

                {children}
            </main>

            <footer className="border-t bg-white">
                <div className="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 py-5 text-xs text-slate-500 sm:flex-row">
                    <span>&copy; BlogVerse Pro &mdash; Dashboard Penulis</span>
                    <div className="flex gap-4">
                        <Link href="/" className="hover:text-indigo-600">Beranda Publik</Link>
                        <Link href="/articles" className="hover:text-indigo-600">Semua Artikel</Link>
                    </div>
                </div>
            </footer>
        </div>
    );
}
