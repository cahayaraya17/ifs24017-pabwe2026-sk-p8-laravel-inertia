import { router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import Modal from './Modal';

export default function GenerateModal({ categories, onClose }) {
    const drafts = usePage().props.flash?.drafts ?? null;
    const [saving, setSaving] = useState(false);
    const { data, setData, post, processing, errors } = useForm({ topic: '', count: 3, category: '' });

    const submit = () => {
        if (drafts) {
            router.post(
                '/dashboard/posts/ai-save',
                { items: drafts },
                {
                    onStart: () => setSaving(true),
                    onFinish: () => setSaving(false),
                    onSuccess: onClose,
                }
            );
        } else {
            post('/dashboard/posts/ai-generate', { preserveState: true, preserveScroll: true });
        }
    };

    const field = 'w-full rounded border px-3 py-2';
    const err = (k) => errors[k] && <p className="mt-1 text-sm text-red-600">{errors[k]}</p>;

    return (
        <Modal
            title="AI Generator Artikel"
            headerClass="bg-purple-600 text-white"
            footerClass="border-t border-purple-200 bg-purple-50"
            submitClass="bg-purple-600 hover:bg-purple-700"
            submitLabel={drafts ? 'Simpan sebagai Draf' : 'Generate'}
            processing={processing || saving}
            onClose={onClose}
            onSubmit={submit}
        >
            {drafts ? (
                <div>
                    <div className="mb-3 flex items-center justify-between">
                        <h3 className="font-semibold">{drafts.length} draf dihasilkan</h3>
                        <button
                            type="button"
                            className="cursor-pointer text-sm text-purple-700 hover:underline"
                            onClick={() => router.reload({ only: ['flash'] })}
                        >
                            Ulangi
                        </button>
                    </div>
                    {drafts.length === 0 ? (
                        <p className="text-sm text-slate-500">AI tidak menghasilkan artikel yang valid. Coba ulangi.</p>
                    ) : (
                        <ul className="divide-y rounded border bg-white">
                            {drafts.map((d, i) => (
                                <li key={i} className="px-4 py-3">
                                    <div className="font-medium">{d.title}</div>
                                    <div className="text-xs text-purple-700">{d.category}</div>
                                    {d.excerpt && <p className="mt-1 text-sm text-slate-600">{d.excerpt}</p>}
                                </li>
                            ))}
                        </ul>
                    )}
                    <p className="mt-3 text-xs text-slate-500">
                        Artikel disimpan sebagai draf. Kamu bisa menyuntingnya dan menerbitkannya nanti.
                    </p>
                </div>
            ) : (
                <div className="space-y-4">
                    <div>
                        <label className="mb-1 block text-sm font-medium">Topik</label>
                        <input
                            type="text"
                            placeholder="Contoh: Manfaat belajar pemrograman sejak dini"
                            className={field}
                            value={data.topic}
                            onChange={(e) => setData('topic', e.target.value)}
                        />
                        {err('topic')}
                    </div>
                    <div className="grid gap-4 md:grid-cols-2">
                        <div>
                            <label className="mb-1 block text-sm font-medium">Jumlah artikel (1-5)</label>
                            <input
                                type="number"
                                min={1}
                                max={5}
                                className={field}
                                value={data.count}
                                onChange={(e) => setData('count', e.target.value)}
                            />
                            {err('count')}
                        </div>
                        <div>
                            <label className="mb-1 block text-sm font-medium">Kategori (opsional)</label>
                            <select
                                className={field}
                                value={data.category}
                                onChange={(e) => setData('category', e.target.value)}
                            >
                                <option value="">Biarkan AI memilih</option>
                                {categories.map((c) => (
                                    <option key={c} value={c}>
                                        {c}
                                    </option>
                                ))}
                            </select>
                            {err('category')}
                        </div>
                    </div>
                </div>
            )}
        </Modal>
    );
}