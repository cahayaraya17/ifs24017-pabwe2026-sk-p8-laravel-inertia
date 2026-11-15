import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import KebabMenu from '../../Components/KebabMenu';
import PostFormModal from '../../Components/PostFormModal';
import ImportModal from '../../Components/ImportModal';
import GenerateModal from '../../Components/GenerateModal';
import Spinner from '../../Components/Spinner';

const fmt = (d) =>
    d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '';

export default function Index({ posts, stats, categories }) {
    const [showCreate, setShowCreate] = useState(false);
    const [showImport, setShowImport] = useState(false);
    const [showGenerate, setShowGenerate] = useState(false);
    const [editingPost, setEditingPost] = useState(null);
    const [togglingId, setTogglingId] = useState(null);
    const [deletingId, setDeletingId] = useState(null);

    const handleTogglePublish = (post) => {
        router.patch(
            `/dashboard/posts/${post.id}/publish`,
            {},
            {
                preserveScroll: true,
                onStart: () => setTogglingId(post.id),
                onFinish: () => setTogglingId(null),
            }
        );
    };

    const handleDelete = (post) => {
        if (!window.confirm(`Hapus artikel "${post.title}"? Tindakan ini tidak dapat dibatalkan.`)) {
            return;
        }
        router.delete(`/dashboard/posts/${post.id}`, {
            preserveScroll: true,
            onStart: () => setDeletingId(post.id),
            onFinish: () => setDeletingId(null),
        });
    };

    return (
        <AppLayout
            title="Dashboard Penulis"
            actions={
                <>
                    <button
                        type="button"
                        onClick={() => setShowGenerate(true)}
                        className="cursor-pointer rounded-lg bg-purple-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm hover:bg-purple-700"
                    >
                        AI Generator
                    </button>
                    <button
                        type="button"
                        onClick={() => setShowImport(true)}
                        className="cursor-pointer rounded-lg bg-emerald-700 px-3.5 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-800"
                    >
                        Impor Excel
                    </button>
                    <a
                        href="/dashboard/posts/export"
                        className="cursor-pointer rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"
                    >
                        Ekspor Excel
                    </a>
                    <button
                        type="button"
                        onClick={() => setShowCreate(true)}
                        className="cursor-pointer rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700"
                    >
                        + Tulis Artikel Baru
                    </button>
                </>
            }
        >
            <Head title="Dashboard Penulis" />

            {/* 3 Kartu Ringkasan Statistik Artikel */}
            <div className="mb-8 grid gap-4 sm:grid-cols-3">
                <div className="rounded-xl border bg-white p-5 shadow-sm">
                    <div className="text-sm font-medium text-slate-500">Total Artikel</div>
                    <div className="mt-2 text-3xl font-extrabold text-slate-900">{stats.total}</div>
                    <div className="mt-1 text-xs text-slate-600">Seluruh tulisan yang Anda miliki</div>
                </div>
                <div className="rounded-xl border border-emerald-100 bg-white p-5 shadow-sm">
                    <div className="text-sm font-medium text-emerald-700">Total Selesai (Terbit)</div>
                    <div className="mt-2 text-3xl font-extrabold text-emerald-600">{stats.done}</div>
                    <div className="mt-1 text-xs text-slate-600">Tampil di halaman blog publik</div>
                </div>
                <div className="rounded-xl border border-amber-100 bg-white p-5 shadow-sm">
                    <div className="text-sm font-medium text-amber-700">Belum Selesai (Draf)</div>
                    <div className="mt-2 text-3xl font-extrabold text-amber-600">{stats.open}</div>
                    <div className="mt-1 text-xs text-slate-600">Masih tersimpan sebagai draf</div>
                </div>
            </div>

            {/* Daftar Artikel Penulis (Format List View, bukan card) */}
            {posts.length === 0 ? (
                <div className="rounded-xl border bg-white p-12 text-center shadow-sm">
                    <h2 className="text-lg font-semibold text-slate-800">Belum ada artikel</h2>
                    <p className="mt-1 text-sm text-slate-500">
                        Mulai tulis gagasan pertamamu sekarang, impor dari Excel, atau gunakan AI Generator.
                    </p>
                    <div className="mt-4 flex flex-wrap justify-center gap-2">
                        <button
                            type="button"
                            onClick={() => setShowCreate(true)}
                            className="cursor-pointer rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                        >
                            + Tulis Artikel Pertama
                        </button>
                        <button
                            type="button"
                            onClick={() => setShowGenerate(true)}
                            className="cursor-pointer rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700"
                        >
                            Buat dengan AI
                        </button>
                    </div>
                </div>
            ) : (
                <div className="overflow-visible rounded-xl border bg-white shadow-sm">
                    <ul className="divide-y divide-slate-200">
                        {posts.map((post) => (
                            <li
                                key={post.id}
                                className="flex flex-col justify-between gap-4 p-4 transition hover:bg-slate-50/70 sm:flex-row sm:items-center"
                            >
                                <div className="flex min-w-0 flex-1 items-center gap-4">
                                    {post.cover_image_path ? (
                                        <img
                                            src={`/storage/${post.cover_image_path}`}
                                            alt={post.title}
                                            className="h-14 w-20 shrink-0 rounded-lg border object-cover"
                                        />
                                    ) : (
                                        <div className="flex h-14 w-20 shrink-0 items-center justify-center rounded-lg border bg-gradient-to-br from-indigo-50 to-purple-50 text-xs font-medium text-indigo-400">
                                            No Cover
                                        </div>
                                    )}

                                    <div className="min-w-0 flex-1">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <Link
                                                href={`/dashboard/posts/${post.id}`}
                                                className="truncate font-semibold text-slate-900 hover:text-indigo-600"
                                            >
                                                {post.title}
                                            </Link>
                                            <span className="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">
                                                {post.category}
                                            </span>
                                            {post.is_published ? (
                                                <span className="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
                                                    Terbit
                                                </span>
                                            ) : (
                                                <span className="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">
                                                    Draf
                                                </span>
                                            )}
                                        </div>
                                        <div className="mt-1 text-xs text-slate-500">
                                            Slug: <code className="text-slate-600">{post.slug}</code>
                                            {post.published_at && <> &middot; Terbit {fmt(post.published_at)}</>}
                                        </div>
                                    </div>
                                </div>

                                <div className="flex shrink-0 items-center justify-end gap-2">
                                    <button
                                        type="button"
                                        disabled={togglingId === post.id || deletingId === post.id}
                                        onClick={() => handleTogglePublish(post)}
                                        className={`flex cursor-pointer items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium transition disabled:opacity-60 ${
                                            post.is_published
                                                ? 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100'
                                                : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                                        }`}
                                    >
                                        {togglingId === post.id && <Spinner />}
                                        {post.is_published ? 'Jadikan Draf' : 'Publikasikan'}
                                    </button>

                                    <KebabMenu
                                        onDetail={() => router.visit(`/dashboard/posts/${post.id}`)}
                                        onEdit={() => setEditingPost(post)}
                                        onDelete={() => handleDelete(post)}
                                    />
                                </div>
                            </li>
                        ))}
                    </ul>
                </div>
            )}

            {/* Modal Full-Screen Tambah Artikel */}
            {showCreate && (
                <PostFormModal
                    post={null}
                    categories={categories}
                    onClose={() => setShowCreate(false)}
                />
            )}

            {/* Modal Full-Screen Ubah Artikel */}
            {editingPost && (
                <PostFormModal
                    post={editingPost}
                    categories={categories}
                    onClose={() => setEditingPost(null)}
                />
            )}

            {/* Modal Full-Screen Impor Excel (Tahap 6) */}
            {showImport && <ImportModal onClose={() => setShowImport(false)} />}

            {/* Modal Full-Screen AI Generator (Tahap 7) */}
            {showGenerate && (
                <GenerateModal
                    categories={categories}
                    onClose={() => setShowGenerate(false)}
                />
            )}
        </AppLayout>
    );
}
