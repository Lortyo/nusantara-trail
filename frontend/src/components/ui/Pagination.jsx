import { buttonSecondary } from './styles';

export default function Pagination({ page, lastPage, onChange }) {
  if (!lastPage || lastPage <= 1) return null;
  return (
    <div className="mt-6 flex items-center justify-center gap-3 text-sm">
      <button type="button" className={buttonSecondary} disabled={page <= 1} onClick={() => onChange(page - 1)}>
        Previous
      </button>
      <span className="text-stone-600">
        Page {page} of {lastPage}
      </span>
      <button type="button" className={buttonSecondary} disabled={page >= lastPage} onClick={() => onChange(page + 1)}>
        Next
      </button>
    </div>
  );
}