import { useEffect, useRef } from 'react';
import Editor from '@toast-ui/editor';
import '@toast-ui/editor/dist/toastui-editor.css';

export default function MarkdownEditor({ value, onChange }) {
    const el = useRef(null);

    useEffect(() => {
        const editor = new Editor({
            el: el.current,
            height: '320px',
            initialEditType: 'markdown',
            previewStyle: 'vertical',
            initialValue: value ?? '',
            usageStatistics: false,
            events: { change: () => onChange(editor.getMarkdown()) },
        });
        return () => editor.destroy();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    return <div ref={el} />;
}