const fmt = (d) =>
    d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';

export default function AdminPostTable({ posts, showAuthor = false }) {
    if (posts.length === 0) {
        return (
            <div className="rounded-xl border bg-white p-10 text-center text-sm text-slate-500 shadow-sm">
                Belum ada artikel.
            </div>
        );
    }

    return (
        <div className="overflow-x-auto rounded-xl border bg-white shadow-sm">
            <table className="w-full text-left text-sm">
                <thead className="border-b bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th className="px-4 py-3">Judul</th>
                        {showAuthor && <th className="px-4 py-3">Penulis</th>}
                        <th className="px-4 py-3">Kategori</th>
                        <th className="px-4 py-3">Status</th>
                        <th className="px-4 py-3">Terbit</th>
                    </tr>
                </thead>
                <tbody className="divide-y">
                    {posts.map((p) => (
                        <tr key={p.id} className="hover:bg-slate-50/70">
                            <td className="px-4 py-3 font-medium text-slate-900">{p.title}</td>
                            {showAuthor && <td className="px-4 py-3">{p.user?.name ?? '-'}</td>}
                            <td className="px-4 py-3">{p.category}</td>
                            <td className="px-4 py-3">
                                {p.is_published ? (
                                    <span className="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
                                        Terbit
                                    </span>
                                ) : (
                                    <span className="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">
                                        Draf
                                    </span>
                                )}
                            </td>
                            <td className="px-4 py-3 text-slate-500">{fmt(p.published_at)}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
