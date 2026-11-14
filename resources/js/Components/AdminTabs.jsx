import { Link, usePage } from '@inertiajs/react';

export default function AdminTabs() {
    const { url } = usePage();
    const tab = (active) =>
        `cursor-pointer rounded-lg px-4 py-2 text-sm font-medium transition ${
            active ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 border hover:bg-slate-100'
        }`;

    return (
        <div className="mb-6 flex gap-2">
            <Link href="/admin/users" className={tab(url.startsWith('/admin/users'))}>
                Pengguna
            </Link>
            <Link href="/admin/posts" className={tab(url.startsWith('/admin/posts'))}>
                Semua Artikel
            </Link>
        </div>
    );
}
