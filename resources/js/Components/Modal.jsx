import Spinner from './Spinner';

export default function Modal({
    title,
    headerClass,
    footerClass,
    onClose,
    onSubmit,
    submitLabel = 'Simpan',
    submitClass = 'bg-indigo-600 hover:bg-indigo-700',
    closeLabel = 'Batal',
    processing = false,
    children,
}) {
    const handleSubmit = (e) => {
        e.preventDefault();
        onSubmit?.();
    };

    return (
        <div className="fixed inset-0 z-50 flex flex-col bg-white">
            <div className={`flex items-center justify-between px-6 py-4 ${headerClass}`}>
                <h2 className="text-lg font-semibold">{title}</h2>
                <button
                    type="button"
                    onClick={onClose}
                    className="cursor-pointer text-2xl leading-none"
                    aria-label="Tutup"
                >
                    &times;
                </button>
            </div>
            <form onSubmit={handleSubmit} className="flex min-h-0 flex-1 flex-col">
                <div className="flex-1 overflow-y-auto p-6">
                    <div className="mx-auto max-w-3xl">{children}</div>
                </div>
                <div className={`flex justify-end gap-3 px-6 py-4 ${footerClass}`}>
                    <button
                        type="button"
                        onClick={onClose}
                        className="cursor-pointer rounded border bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50"
                    >
                        {closeLabel}
                    </button>
                    {onSubmit && (
                        <button
                            type="submit"
                            disabled={processing}
                            className={`flex cursor-pointer items-center gap-2 rounded px-4 py-2 text-sm text-white disabled:opacity-60 ${submitClass}`}
                        >
                            {processing && <Spinner />}
                            {submitLabel}
                        </button>
                    )}
                </div>
            </form>
        </div>
    );
}