import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import AdminTabs from '../../Components/AdminTabs';
import AdminPostTable from '../../Components/AdminPostTable';

export default function UserPosts({ owner, posts, stats }) {
    return (
        <AppLayout
            title={`Artikel ${owner.name}`}
            actions={
                <>
                    <Link
                        href="/admin/users"
                        className="cursor-pointer rounded-lg border bg-white px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        &larr; Kembali
                    </Link>
                    <a
                        href={`/admin/users/${owner.id}/posts/export`}
                        className="cursor-pointer rounded-lg bg-emerald-700 px-3.5 py-2 text-sm font-medium text-white hover:bg-emerald-800"
                    >
                        Ekspor Excel
                    </a>
                </>
            }
        >
            <Head title={`Artikel ${owner.name}`} />
            <AdminTabs />

            <p className="mb-4 text-sm text-slate-600">
                @{owner.username} &middot; {stats.total} artikel ({stats.done} terbit, {stats.open} draf). Tampilan
                hanya-baca.
            </p>
            <AdminPostTable posts={posts} />
        </AppLayout>
    );
}
