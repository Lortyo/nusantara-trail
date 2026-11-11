import { STATE_META } from '../lib/event';

export default function EventStateBadge({ state }) {
  const meta = STATE_META[state] ?? STATE_META.closed;
  return (
    <span className={`inline-block rounded-full px-2.5 py-1 text-xs font-semibold ${meta.cls}`}>
      {meta.label}
    </span>
  );
}