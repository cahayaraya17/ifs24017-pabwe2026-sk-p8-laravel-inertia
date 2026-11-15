import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import MarkdownViewer from '../../Components/MarkdownViewer';
import PostFormModal from '../../Components/PostFormModal';
import Spinner from '../../Components/Spinner';

const fmt = (d) =>
    d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '';

const CATEGORIES = ['Teknologi', 'Pendidikan', 'Gaya Hidup', 'Bisnis', 'Opini', 'Umum'];

export default function Show({ post }) {
    const [editing, setEditing] = useState(false);
    const [toggling, setToggling] = useState(false);
    const [deleting, setDeleting] = useState(false);
    const [removingCover, setRemovingCover] = useState(false);

    const coverForm = useForm({ cover_image: null });

    const handleUploadCover = (e) => {
        e.preventDefault();
        if (!coverForm.data.cover_image) return;
        coverForm.post(`/dashboard/posts/${post.id}/cover`, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => coverForm.reset('cover_image'),
        });
    };

    const handleRemoveCover = () => {
        if (!window.confirm('Hapus foto sampul artikel ini?')) return;
        router.delete(`/dashboard/posts/${post.id}/cover`, {
            preserveScroll: true,
            onStart: () => setRemovingCover(true),
            onFinish: () => setRemovingCover(false),
        });
    };

    const handleTogglePublish = () => {
        router.patch(
            `/dashboard/posts/${post.id}/publish`,
            {},
            {
                preserveScroll: true,
                onStart: () => setToggling(true),
                onFinish: () => setToggling(false),
            }
        );
    };

    const handleDelete = () => {
        if (!window.confirm(`Hapus artikel "${post.title}"?`)) return;
        router.delete(`/dashboard/posts/${post.id}`, {
            onStart: () => setDeleting(true),
            onFinish: () => setDeleting(false),
        });
    };

    return (
        <AppLayout
            title="Detail Artikel"
            actions={
                <>
                    <Link
                        href="/dashboard/posts"
                        className="cursor-pointer rounded-lg border bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        &larr; Kembali
                    </Link>
                    <button
                        type="button"
                        disabled={toggling}
                        onClick={handleTogglePublish}
                        className={`flex cursor-pointer items-center gap-1.5 rounded-lg border px-3 py-2 text-sm font-medium disabled:opacity-60 ${
                            post.is_published
                                ? 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100'
                                : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                        }`}
                    >
                        {toggling && <Spinner />}
                        {post.is_published ? 'Jadikan Draf' : 'Publikasikan'}
                    </button>
                    <button
                        type="button"
                        onClick={() => setEditing(true)}
                        className="cursor-pointer rounded-lg bg-amber-500 px-3 py-2 text-sm font-medium text-white hover:bg-amber-600"
                    >
                        Ubah Artikel
                    </button>
                    <button
                        type="button"
                        disabled={deleting}
                        onClick={handleDelete}
                        className="flex cursor-pointer items-center gap-1.5 rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-60"
                    >
                        {deleting && <Spinner />}
                        Hapus
                    </button>
                </>
            }
        >
            <Head title={post.title} />

            <div className="mx-auto max-w-3xl space-y-6">
                {/* Manajemen Foto Sampul (Cover) */}
                <div className="rounded-xl border bg-white p-5 shadow-sm">
                    <h2 className="text-sm font-semibold text-slate-800">Foto Sampul Artikel</h2>
                    {post.cover_image_path ? (
                        <div className="mt-3 space-y-3">
                            <img
                                src={`/storage/${post.cover_image_path}`}
                                alt={post.title}
                                className="max-h-80 w-full rounded-lg object-cover"
                            />
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <form onSubmit={handleUploadCover} className="flex flex-wrap items-center gap-2">
                                    <input
                                        type="file"
                                        accept="image/*"
                                        onChange={(e) => coverForm.setData('cover_image', e.target.files[0] ?? null)}
                                        className="text-xs text-slate-600"
                                    />
                                    <button
                                        type="submit"
                                        disabled={!coverForm.data.cover_image || coverForm.processing}
                                        className="flex cursor-pointer items-center gap-1.5 rounded bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                                    >
                                        {coverForm.processing && <Spinner />}
                                        Ganti Sampul
                                    </button>
                                </form>
                                <button
                                    type="button"
                                    disabled={removingCover}
                                    onClick={handleRemoveCover}
                                    className="flex cursor-pointer items-center gap-1.5 rounded border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-100 disabled:opacity-60"
                                >
                                    {removingCover && <Spinner />}
                                    Hapus Sampul
                                </button>
                            </div>
                        </div>
                    ) : (
                        <form onSubmit={handleUploadCover} className="mt-3 flex flex-wrap items-center gap-3">
                            <input
                                type="file"
                                accept="image/*"
                                onChange={(e) => coverForm.setData('cover_image', e.target.files[0] ?? null)}
                                className="text-sm text-slate-600"
                            />
                            <button
                                type="submit"
                                disabled={!coverForm.data.cover_image || coverForm.processing}
                                className="flex cursor-pointer items-center gap-1.5 rounded-lg bg-indigo-600 px-3.5 py-2 text-xs font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                            >
                                {coverForm.processing && <Spinner />}
                                Unggah Foto Sampul
                            </button>
                        </form>
                    )}
                    {coverForm.errors.cover_image && (
                        <p className="mt-2 text-xs text-red-600">{coverForm.errors.cover_image}</p>
                    )}
                </div>

                {/* Konten Utama Artikel dengan TOAST UI Viewer */}
                <article className="rounded-xl border bg-white p-6 shadow-sm md:p-8">
                    <div className="flex flex-wrap items-center gap-2">
                        <span className="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">
                            {post.category}
                        </span>
                        {post.is_published ? (
                            <span className="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
                                Terbit &middot; {fmt(post.published_at)}
                            </span>
                        ) : (
                            <span className="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">
                                Draf
                            </span>
                        )}
                        {post.is_published && (
                            <Link
                                href={`/articles/${post.slug}`}
                                className="ml-auto text-xs font-medium text-indigo-700 underline hover:text-indigo-800"
                            >
                                Buka di Halaman Publik &rarr;
                            </Link>
                        )}
                    </div>

                    <h1 className="mt-4 text-2xl font-extrabold text-slate-900 md:text-3xl">{post.title}</h1>

                    {post.excerpt && (
                        <p className="mt-3 rounded-lg bg-slate-50 p-3 text-sm italic text-slate-600">
                            {post.excerpt}
                        </p>
                    )}

                    <div className="mt-6 border-t pt-6">
                        <MarkdownViewer value={post.content} />
                    </div>
                </article>
            </div>

            {editing && (
                <PostFormModal
                    post={post}
                    categories={CATEGORIES}
                    onClose={() => setEditing(false)}
                />
            )}
        </AppLayout>
    );
}
