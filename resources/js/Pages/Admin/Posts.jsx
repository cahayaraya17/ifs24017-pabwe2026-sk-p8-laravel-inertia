import { Head } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import AdminTabs from '../../Components/AdminTabs';
import AdminPostTable from '../../Components/AdminPostTable';

export default function Posts({ posts, stats }) {
    return (
        <AppLayout
            title="Panel Admin"
            actions={
                <a
                    href="/admin/posts/export"
                    className="cursor-pointer rounded-lg bg-emerald-700 px-3.5 py-2 text-sm font-medium text-white hover:bg-emerald-800"
                >
                    Ekspor Semua Artikel
                </a>
            }
        >
            <Head title="Semua Artikel" />
            <AdminTabs />

            <div className="mb-6 grid gap-4 sm:grid-cols-3">
                <div className="rounded-xl border bg-white p-5 shadow-sm">
                    <div className="text-sm font-medium text-slate-500">Total Artikel</div>
                    <div className="mt-2 text-3xl font-extrabold text-slate-900">{stats.total}</div>
                </div>
                <div className="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm">
                    <div className="text-sm font-medium text-emerald-700">Terbit</div>
                    <div className="mt-2 text-3xl font-extrabold text-emerald-600">{stats.done}</div>
                </div>
                <div className="rounded-xl border border-amber-100 bg-white p-5 shadow-sm">
                    <div className="text-sm font-medium text-amber-700">Draf</div>
                    <div className="mt-2 text-3xl font-extrabold text-amber-600">{stats.open}</div>
                </div>
            </div>

            <AdminPostTable posts={posts} showAuthor />
        </AppLayout>
    );
}
