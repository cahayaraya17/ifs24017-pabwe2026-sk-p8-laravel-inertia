import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import AdminTabs from '../../Components/AdminTabs';

export default function Users({ users }) {
    return (
        <AppLayout title="Panel Admin">
            <Head title="Kelola Pengguna" />
            <AdminTabs />

            <div className="overflow-x-auto rounded-xl border bg-white shadow-sm">
                <table className="w-full text-left text-sm">
                    <thead className="border-b bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th className="px-4 py-3">Pengguna</th>
                            <th className="px-4 py-3">Email</th>
                            <th className="px-4 py-3">Role</th>
                            <th className="px-4 py-3">Artikel</th>
                            <th className="px-4 py-3">Terbit</th>
                            <th className="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {users.map((u) => (
                            <tr key={u.id} className="hover:bg-slate-50/70">
                                <td className="px-4 py-3">
                                    <div className="font-medium text-slate-900">{u.name}</div>
                                    <div className="text-xs text-slate-500">@{u.username}</div>
                                </td>
                                <td className="px-4 py-3">{u.email}</td>
                                <td className="px-4 py-3">
                                    <span
                                        className={`rounded-full px-2.5 py-0.5 text-xs font-medium ${
                                            u.role === 'admin'
                                                ? 'bg-purple-50 text-purple-700'
                                                : 'bg-indigo-50 text-indigo-700'
                                        }`}
                                    >
                                        {u.role}
                                    </span>
                                </td>
                                <td className="px-4 py-3">{u.posts_count}</td>
                                <td className="px-4 py-3">{u.published_posts_count}</td>
                                <td className="px-4 py-3">
                                    <div className="flex justify-end gap-2">
                                        <Link
                                            href={`/admin/users/${u.id}/posts`}
                                            className="cursor-pointer rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-100"
                                        >
                                            Lihat Artikel
                                        </Link>
                                        <a
                                            href={`/admin/users/${u.id}/posts/export`}
                                            className="cursor-pointer rounded-lg border bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                        >
                                            Ekspor
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
