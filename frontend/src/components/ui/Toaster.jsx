import { useToastStore } from '../../stores/toastStore';

const styles = {
  success: 'border-green-300 bg-green-50 text-green-900',
  error: 'border-red-300 bg-red-50 text-red-900',
  info: 'border-stone-300 bg-white text-stone-900',
};

export default function Toaster() {
  const toasts = useToastStore((s) => s.toasts);
  const remove = useToastStore((s) => s.remove);

  return (
    <div className="pointer-events-none fixed inset-x-0 top-4 z-50 flex flex-col items-center gap-2 px-4 sm:items-end sm:pr-6">
      {toasts.map((t) => (
        <div
          key={t.id}
          role="status"
          className={`pointer-events-auto w-full max-w-sm rounded-lg border px-4 py-3 text-sm shadow-lg ${styles[t.type] ?? styles.info}`}
        >
          <div className="flex items-start justify-between gap-3">
            <span>{t.message}</span>
            <button type="button" onClick={() => remove(t.id)} aria-label="Dismiss" className="opacity-60 hover:opacity-100">
              ×
            </button>
          </div>
        </div>
      ))}
    </div>
  );
}