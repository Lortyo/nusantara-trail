import { useEffect, useState } from 'react';
import { getErrorMessage } from '../../api/client';
import { eventsAPI } from '../../api/events';
import { organizerAPI } from '../../api/organizer';
import EventSelect from './EventSelect';
import Pagination from '../../components/ui/Pagination';
import Spinner from '../../components/ui/Spinner';
import StatusPill from '../../components/ui/StatusPill';
import { inputClass } from '../../components/ui/styles';
import { useEventChoice } from '../../hooks/useEventChoice';
import { getId } from '../../lib/event';
import { formatDateTime } from '../../lib/format';

export default function Participants() {
  const { events, eventId, setEventId, loading: loadingEvents } = useEventChoice();
  const [categories, setCategories] = useState([]);
  const [categoryId, setCategoryId] = useState('');
  const [status, setStatus] = useState('paid');
  const [search, setSearch] = useState('');
  const [q, setQ] = useState('');
  const [page, setPage] = useState(1);
  const [rows, setRows] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    const t = setTimeout(() => {
      setQ(search.trim());
      setPage(1);
    }, 400);
    return () => clearTimeout(t);
  }, [search]);

  useEffect(() => {
    setCategoryId('');
    setPage(1);
    if (!eventId) return;
    eventsAPI.categories(eventId).then((res) => setCategories(res.data)).catch(() => {});
  }, [eventId]);

  useEffect(() => {
    if (!eventId) return undefined;
    let cancelled = false;
    setLoading(true);
    setError('');
    organizerAPI
      .registrations({
        event_id: eventId,
        category_id: categoryId || undefined,
        status: status || undefined,
        q: q || undefined,
        page,
        limit: 20,
      })
      .then((res) => {
        if (cancelled) return;
        setRows(res.data);
        setMeta(res.meta);
      })
      .catch((err) => !cancelled && setError(getErrorMessage(err)))
      .finally(() => !cancelled && setLoading(false));
    return () => {
      cancelled = true;
    };
  }, [eventId, categoryId, status, q, page]);

  if (loadingEvents) return <Spinner />;

  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-extrabold text-stone-900">Participants</h1>

      <div className="grid gap-3 md:grid-cols-4">
        <EventSelect events={events} value={eventId} onChange={setEventId} />
        <select aria-label="Category" className={inputClass} value={categoryId} onChange={(e) => { setCategoryId(e.target.value); setPage(1); }}>
          <option value="">All categories</option>
          {categories.map((c) => (
            <option key={getId(c)} value={getId(c)}>
              {c.name}
            </option>
          ))}
        </select>
        <select aria-label="Status" className={inputClass} value={status} onChange={(e) => { setStatus(e.target.value); setPage(1); }}>
          <option value="">All statuses</option>
          <option value="paid">Paid</option>
          <option value="pending">Pending</option>
          <option value="expired">Expired</option>
          <option value="cancelled">Cancelled</option>
        </select>
        <input type="search" placeholder="Name, email, BIB or code" aria-label="Search participants" className={inputClass} value={search} onChange={(e) => setSearch(e.target.value)} />
      </div>

      {error && (
        <div role="alert" className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {error}
        </div>
      )}

      {loading ? (
        <Spinner />
      ) : (
        <div className="overflow-x-auto rounded-xl border border-stone-200 bg-white">
          <table className="w-full text-left text-sm">
            <thead className="bg-stone-100 text-xs uppercase text-stone-600">
              <tr>
                <th className="px-4 py-3">Participant</th>
                <th className="px-4 py-3">BIB</th>
                <th className="px-4 py-3">Category</th>
                <th className="px-4 py-3">Jersey</th>
                <th className="px-4 py-3">Status</th>
                <th className="px-4 py-3">Kit</th>
                <th className="px-4 py-3">Check-in</th>
              </tr>
            </thead>
            <tbody>
              {rows.map((r) => (
                <tr key={r.id} className="border-t border-stone-100">
                  <td className="px-4 py-3">
                    <p className="font-medium text-stone-900">{r.name ?? '-'}</p>
                    <p className="text-xs text-stone-500">{r.email}</p>
                    <p className="font-mono text-xs text-stone-400">{r.registrationCode}</p>
                  </td>
                  <td className="px-4 py-3 font-mono font-semibold">{r.bibNumber ?? '-'}</td>
                  <td className="px-4 py-3">{r.category ?? '-'}</td>
                  <td className="px-4 py-3">{r.jerseySize}</td>
                  <td className="px-4 py-3">
                    <StatusPill status={r.status} />
                  </td>
                  <td className="px-4 py-3 text-xs">{r.kitCollectedAt ? formatDateTime(r.kitCollectedAt) : '-'}</td>
                  <td className="px-4 py-3 text-xs">{r.checkedInAt ? formatDateTime(r.checkedInAt) : '-'}</td>
                </tr>
              ))}
              {rows.length === 0 && (
                <tr>
                  <td colSpan={7} className="px-4 py-8 text-center text-stone-500">
                    No participants match these filters.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      )}

      <p className="text-xs text-stone-500">{meta ? `${meta.total} registrations` : ''}</p>
      <Pagination page={meta?.page ?? 1} lastPage={meta?.last_page} onChange={setPage} />
    </div>
  );
}