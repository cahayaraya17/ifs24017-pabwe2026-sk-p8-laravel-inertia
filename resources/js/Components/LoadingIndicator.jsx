import { router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import Spinner from './Spinner';

export default function LoadingIndicator() {
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        const offStart = router.on('start', () => setLoading(true));
        const offFinish = router.on('finish', () => setLoading(false));
        return () => {
            offStart();
            offFinish();
        };
    }, []);

    if (!loading) return null;

    return (
        <>
            <div className="fixed left-0 top-0 z-[60] h-1 w-full animate-pulse bg-indigo-600" />
            <div className="fixed bottom-4 right-4 z-[60] flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-sm text-white shadow-lg">
                <Spinner />
                Memproses tindakan...
            </div>
        </>
    );
}