import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import PublicLayout from '../../Layouts/PublicLayout';
import { LogIn, UserPlus } from '../../Components/Icons';

const fmt = (d) =>
    d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '';

const pageLabel = (label) =>
    label.replace('&laquo; Previous', '‹ Sebelumnya').replace('Next &raquo;', 'Berikutnya ›');

export default function PostIndex({ posts, filters, categories, hero }) {
    const { auth } = usePage().props;
    const [q, setQ] = useState(filters.q ?? '');

    const go = (params) =>
        router.get('/articles', params, { preserveState: true, replace: true });

    const search = (e) => {
        e.preventDefault();
        go({ q: q || undefined, category: filters.category || undefined });
    };

    const chip = (active) =>
        `cursor-pointer rounded-full border px-3 py-1 text-sm ${
            active ? 'border-indigo-600 bg-indigo-600 text-white' : 'bg-white hover:bg-slate-50'
        }`;

    return (
        <PublicLayout>
            <Head title={hero ? 'Beranda' : 'Artikel'} />

            {!hero && <h1 className="mb-4 text-3xl font-extrabold text-slate-900">Semua Artikel</h1>}

            {hero && (
                <section className="mb-10 rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-700 px-6 py-12 text-center text-white">
                    <h1 className="text-3xl font-extrabold md:text-5xl">BlogVerse Pro</h1>
                    <p className="mx-auto mt-3 max-w-xl text-indigo-100">
                        Platform publikasi artikel: baca tulisan terbaru dari para penulis, atau mulai
                        menulis dan bagikan gagasanmu.
                    </p>
                    {!auth.user && (
                        <div className="mt-6 flex flex-wrap justify-center gap-3">
                            <Link
                                href="/login"
                                className="flex cursor-pointer items-center gap-2 rounded bg-white px-5 py-2 font-medium text-indigo-700 hover:bg-indigo-50"
                            >
                                <LogIn /> Masuk
                            </Link>
                            <Link
                                href="/register"
                                className="flex cursor-pointer items-center gap-2 rounded border border-white px-5 py-2 font-medium hover:bg-white/10"
                            >
                                <UserPlus /> Daftar Penulis
                            </Link>
                        </div>
                    )}
                </section>
            )}

            <form onSubmit={search} className="mb-4 flex gap-2">
                <input
                    type="search"
                    aria-label="Cari artikel"
                    placeholder="Cari judul atau isi artikel..."
                    value={q}
                    onChange={(e) => setQ(e.target.value)}
                    className="flex-1 rounded border px-3 py-2"
                />
                <button
                    type="submit"
                    className="cursor-pointer rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
                >
                    Cari
                </button>
            </form>

            <div className="mb-6 flex flex-wrap gap-2">
                <button
                    type="button"
                    className={chip(!filters.category)}
                    onClick={() => go({ q: q || undefined })}
                >
                    Semua
                </button>
                {categories.map((c) => (
                    <button
                        key={c}
                        type="button"
                        className={chip(filters.category === c)}
                        onClick={() => go({ q: q || undefined, category: c })}
                    >
                        {c}
                    </button>
                ))}
            </div>

            {posts.data.length === 0 ? (
                <div className="rounded border bg-slate-50 p-10 text-center text-slate-500">
                    Belum ada artikel yang cocok.
                </div>
            ) : (
                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {posts.data.map((p) => (
                        <Link
                            key={p.id}
                            href={`/articles/${p.slug}`}
                            className="group flex cursor-pointer flex-col overflow-hidden rounded-xl border bg-white transition hover:shadow-lg"
                        >
                            {p.cover_image_path ? (
                                <img
                                    src={`/storage/${p.cover_image_path}`}
                                    alt=""
                                    className="h-44 w-full object-cover"
                                />
                            ) : (
                                <div className="h-44 w-full bg-gradient-to-br from-indigo-100 to-purple-100" />
                            )}
                            <div className="flex flex-1 flex-col p-4">
                                <span className="mb-2 w-fit rounded-full bg-indigo-50 px-2 py-0.5 text-xs text-indigo-700">
                                    {p.category}
                                </span>
                                <h2 className="font-bold group-hover:text-indigo-700">{p.title}</h2>
                                {p.excerpt && (
                                    <p className="mt-1 line-clamp-3 text-sm text-slate-600">{p.excerpt}</p>
                                )}
                                <div className="mt-auto pt-3 text-xs text-slate-500">
                                    {p.user?.name} &middot; {fmt(p.published_at)}
                                </div>
                            </div>
                        </Link>
                    ))}
                </div>
            )}

            {posts.last_page > 1 && (
                <nav aria-label="Paginasi artikel" className="mt-8 flex flex-wrap justify-center gap-1">
                    {posts.links.map((l, i) =>
                        l.url ? (
                            <Link
                                key={i}
                                href={l.url}
                                preserveState
                                className={`rounded border px-3 py-1 text-sm ${
                                    l.active ? 'bg-indigo-600 text-white' : 'bg-white hover:bg-slate-50'
                                }`}
                            >
                                {pageLabel(l.label)}
                            </Link>
                        ) : (
                            <span key={i} className="rounded border px-3 py-1 text-sm text-slate-300">
                                {pageLabel(l.label)}
                            </span>
                        )
                    )}
                </nav>
            )}
        </PublicLayout>
    );
}