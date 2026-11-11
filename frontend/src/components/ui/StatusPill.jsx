const styles = {
  paid: 'bg-green-100 text-green-800',
  accepted: 'bg-green-100 text-green-800',
  pending: 'bg-orange-100 text-orange-800',
  requested: 'bg-orange-100 text-orange-800',
  rejected: 'bg-red-100 text-red-800',
  expired: 'bg-stone-200 text-stone-700',
  cancelled: 'bg-stone-200 text-stone-700',
  open: 'bg-green-100 text-green-800',
};

export default function StatusPill({ status }) {
  return (
    <span className={`inline-block rounded-full px-2.5 py-1 text-xs font-semibold capitalize ${styles[status] ?? styles.expired}`}>
      {status}
    </span>
  );
}