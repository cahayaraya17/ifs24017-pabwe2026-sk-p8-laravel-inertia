import { useEffect, useRef, useState } from 'react';
import { Eye, Pencil, Trash } from './Icons';

export default function KebabMenu({ onDetail, onEdit, onDelete }) {
    const [open, setOpen] = useState(false);
    const ref = useRef(null);

    useEffect(() => {
        const close = (e) => {
            if (ref.current && !ref.current.contains(e.target)) setOpen(false);
        };
        document.addEventListener('mousedown', close);
        return () => document.removeEventListener('mousedown', close);
    }, []);

    const item = 'flex w-full cursor-pointer items-center gap-2 px-4 py-2 text-left text-sm hover:bg-slate-50';
    const run = (fn) => () => {
        setOpen(false);
        fn();
    };

    return (
        <div className="relative" ref={ref}>
            <button
                type="button"
                onClick={() => setOpen(!open)}
                className="cursor-pointer rounded px-2 py-1 text-lg leading-none hover:bg-slate-100"
                aria-label="Menu tindakan"
            >
                &#8942;
            </button>
            {open && (
                <div className="absolute right-0 z-20 mt-1 w-36 rounded border bg-white py-1 shadow-lg">
                    <button type="button" className={`${item} text-indigo-600`} onClick={run(onDetail)}>
                        <Eye /> Detail
                    </button>
                    <button type="button" className={`${item} text-indigo-600`} onClick={run(onEdit)}>
                        <Pencil /> Ubah
                    </button>
                    <button type="button" className={`${item} text-red-600`} onClick={run(onDelete)}>
                        <Trash /> Hapus
                    </button>
                </div>
            )}
        </div>
    );
}