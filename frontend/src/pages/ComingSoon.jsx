export default function ComingSoon({ title }) {
  return (
    <div className="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
      <h1 className="text-xl font-bold text-stone-900">{title}</h1>
      <p className="mt-2 text-sm text-stone-500">This page is under construction.</p>
    </div>
  );
}