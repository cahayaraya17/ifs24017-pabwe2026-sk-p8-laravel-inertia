import { useForm } from '@inertiajs/react';
import Modal from './Modal';
import MarkdownEditor from './MarkdownEditor';

export default function PostFormModal({ post, categories, onClose }) {
    const isEdit = Boolean(post);

    const { data, setData, post: send, processing, errors } = useForm({
        _method: isEdit ? 'put' : 'post',
        title: post?.title ?? '',
        category: post?.category ?? 'Umum',
        excerpt: post?.excerpt ?? '',
        content: post?.content ?? '',
        is_published: post?.is_published ?? false,
        cover_image: null,
    });

    const submit = () => {
        send(isEdit ? `/dashboard/posts/${post.id}` : '/dashboard/posts', {
            forceFormData: true,
            onSuccess: onClose,
        });
    };

    const field = 'w-full rounded border px-3 py-2';
    const err = (k) => errors[k] && <p className="mt-1 text-sm text-red-600">{errors[k]}</p>;

    return (
        <Modal
            title={isEdit ? 'Ubah Artikel' : 'Tulis Artikel Baru'}
            headerClass={isEdit ? 'bg-amber-500 text-white' : 'bg-indigo-600 text-white'}
            footerClass={isEdit ? 'border-t border-amber-200 bg-amber-50' : 'border-t border-indigo-200 bg-indigo-50'}
            submitClass={isEdit ? 'bg-amber-600 hover:bg-amber-700' : 'bg-indigo-600 hover:bg-indigo-700'}
            processing={processing}
            onClose={onClose}
            onSubmit={submit}
        >
            <div className="space-y-4">
                <div>
                    <label className="mb-1 block text-sm font-medium">Judul</label>
                    <input
                        type="text"
                        className={field}
                        value={data.title}
                        onChange={(e) => setData('title', e.target.value)}
                    />
                    {err('title')}
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <div>
                        <label className="mb-1 block text-sm font-medium">Kategori</label>
                        <select
                            className={field}
                            value={data.category}
                            onChange={(e) => setData('category', e.target.value)}
                        >
                            {categories.map((c) => (
                                <option key={c} value={c}>
                                    {c}
                                </option>
                            ))}
                        </select>
                        {err('category')}
                    </div>
                    <div>
                        <label className="mb-1 block text-sm font-medium">Foto sampul (maks. 2 MB)</label>
                        <input
                            type="file"
                            accept="image/*"
                            className="block w-full text-sm"
                            onChange={(e) => setData('cover_image', e.target.files[0] ?? null)}
                        />
                        {err('cover_image')}
                    </div>
                </div>

                <div>
                    <label className="mb-1 block text-sm font-medium">Ringkasan (1-2 kalimat)</label>
                    <textarea
                        rows={2}
                        className={field}
                        value={data.excerpt}
                        onChange={(e) => setData('excerpt', e.target.value)}
                    />
                    {err('excerpt')}
                </div>

                <div>
                    <label className="mb-1 block text-sm font-medium">Isi artikel (Markdown)</label>
                    <MarkdownEditor value={data.content} onChange={(v) => setData('content', v)} />
                    {err('content')}
                </div>

                <label className="flex cursor-pointer items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        checked={data.is_published}
                        onChange={(e) => setData('is_published', e.target.checked)}
                    />
                    Terbitkan sekarang (tampil di halaman publik)
                </label>
            </div>
        </Modal>
    );
}