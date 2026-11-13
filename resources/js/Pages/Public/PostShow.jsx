import { Head, Link, usePage } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import MarkdownViewer from '../../Components/MarkdownViewer';
import { LogIn, UserPlus } from '../../Components/Icons';

const fmt = (d) =>
    d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '';

export default function PostShow({ post }) {
    const { auth } = usePage().props;

    return (
        <PublicLayout>
            <Head title={post.title} />
            <article className="mx-auto max-w-3xl">
                <Link href="/articles" className="text-sm text-indigo-600 hover:underline">
                    &larr; Semua artikel
                </Link>
                <span className="ml-3 rounded-full bg-indigo-50 px-2 py-0.5 text-xs text-indigo-700">
                    {post.category}
                </span>
                <h1 className="mt-3 text-3xl font-extrabold leading-tight md:text-4xl">{post.title}</h1>
                <p className="mt-2 text-sm text-slate-500">
                    oleh {post.user?.name} &middot; {fmt(post.published_at)}
                </p>

                {post.cover_image_path && (
                    <img
                        src={`/storage/${post.cover_image_path}`}
                        alt=""
                        className="mt-6 max-h-96 w-full rounded-xl object-cover"
                    />
                )}

                <div className="mt-8">
                    <MarkdownViewer value={post.content} />
                </div>

                {!auth.user && (
                    <section className="mt-12 rounded-xl border bg-indigo-50 p-6 text-center">
                        <h2 className="text-lg font-bold">Suka tulisan ini?</h2>
                        <p className="mt-1 text-sm text-slate-600">
                            Bergabunglah dengan komunitas penulis dan terbitkan artikelmu sendiri.
                        </p>
                        <div className="mt-4 flex flex-wrap justify-center gap-3">
                            <Link
                                href="/login"
                                className="flex cursor-pointer items-center gap-2 rounded border bg-white px-4 py-2 text-sm hover:bg-slate-50"
                            >
                                <LogIn /> Masuk
                            </Link>
                            <Link
                                href="/register"
                                className="flex cursor-pointer items-center gap-2 rounded bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700"
                            >
                                <UserPlus /> Daftar Penulis
                            </Link>
                        </div>
                    </section>
                )}
            </article>
        </PublicLayout>
    );
}