import { useForm } from '@inertiajs/react';
import Modal from './Modal';

export default function ImportModal({ onClose }) {
    const { setData, post, processing, errors } = useForm({ file: null });

    const submit = () => post('/dashboard/posts/import', { forceFormData: true, onSuccess: onClose });

    return (
        <Modal
            title="Impor Artikel dari Excel"
            headerClass="bg-green-600 text-white"
            footerClass="border-t border-green-200 bg-green-50"
            submitClass="bg-green-600 hover:bg-green-700"
            submitLabel="Impor"
            processing={processing}
            onClose={onClose}
            onSubmit={submit}
        >
            <div className="space-y-3">
                <p className="text-sm text-slate-600">
                    Unggah file <b>.xlsx</b>, <b>.xls</b>, atau <b>.csv</b> dengan kolom berurutan:{' '}
                    <b>Judul, Kategori, Ringkasan, Isi, Status</b> (Terbit/Draf). Baris pertama adalah
                    judul kolom. Cara termudah: ekspor artikelmu dulu, lalu pakai file itu sebagai templat.
                </p>
                <input
                    type="file"
                    accept=".xlsx,.xls,.csv"
                    className="block w-full text-sm"
                    onChange={(e) => setData('file', e.target.files[0] ?? null)}
                />
                {errors.file && <p className="text-sm text-red-600">{errors.file}</p>}
            </div>
        </Modal>
    );
}