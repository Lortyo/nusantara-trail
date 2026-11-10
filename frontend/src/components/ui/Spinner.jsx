export default function Spinner({ label = 'Loading...' }) {
  return (
    <div className="flex items-center justify-center gap-3 py-12 text-stone-500" role="status">
      <span className="h-5 w-5 animate-spin rounded-full border-2 border-stone-300 border-t-green-700" />
      <span className="text-sm">{label}</span>
    </div>
  );
}