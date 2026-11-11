import { useCallback, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { getErrorMessage } from '../../api/client';
import { eventsAPI } from '../../api/events';
import { organizerAPI } from '../../api/organizer';
import Pagination from '../../components/ui/Pagination';
import Spinner from '../../components/ui/Spinner';
import StatusPill from '../../components/ui/StatusPill';
import { buttonPrimary, buttonSecondary, inputClass } from '../../components/ui/styles';
import { getId } from '../../lib/event';
import { formatDate } from '../../lib/format';
import { toast } from '../../stores/toastStore';

const ACTIONS = {
  draft: [['publish', 'Open registration']],
  open: [['close', 'Close registration'], ['finish', 'Mark finished']],
  closed: [['reopen', 'Reopen'], ['finish', 'Mark finished']],
  finished: [],
};

export default function Events() {
  const [search, setSearch] = useState('');
  const [q, setQ] = useState('');
  const [status, setStatus] = useState('');
  const [page, setPage] = useState(1);
  const [events, setEvents] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const t = setTimeout(() => {
      setQ(search.trim());
      setPage(1);
    }, 400);
    return () => clearTimeout(t);
  }, [search]);

  const load = useCallback(() => {
    setLoading(true);
    organizerAPI
      .events({ q: q || undefined, status: status || undefined, page, limit: 10 })
      .then((res) => {
        setEvents(res.data);
        setMeta(res.meta);
      })
      .catch((err) => toast.error(getErrorMessage(err)))
      .finally(() => setLoading(false));
  }, [q, status, page]);

  useEffect(() => {
    load();
  }, [load]);

  const changeStatus = async (id, action) => {
    try {
      await eventsAPI.setStatus(id, action);
      toast.success('Event status updated.');
      load();
    } catch (err) {
      toast.error(getErrorMessage(err));
    }
  };

  const remove = async (e) => {
    if (!window.confirm(`Delete "${e.name}" and its categories? This cannot be undone.`)) return;
    try {
      await eventsAPI.remove(getId(e));
      toast.success('Event deleted.');
      load();
    } catch (err) {
      toast.error(getErrorMessage(err));
    }
  };

  return (
    <div>
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="text-2xl font-extrabold text-stone-900">Events</h1>
        <Link to="/organizer/events/new" className={buttonPrimary}>
          New event
        </Link>
      </div>

      <div className="mt-4 grid gap-3 sm:grid-cols-[1fr_200px]">
        <input type="search" placeholder="Search by name" aria-label="Search events" className={inputClass} value={search} onChange={(e) => setSearch(e.target.value)} />
        <select aria-label="Filter by status" className={inputClass} value={status} onChange={(e) => { setStatus(e.target.value); setPage(1); }}>
          <option value="">All statuses</option>
          <option value="draft">Draft</option>
          <option value="open">Open</option>
          <option value="closed">Closed</option>
          <option value="finished">Finished</option>
        </select>
      </div>

      {loading ? (
        <Spinner />
      ) : (
        <div className="mt-4 space-y-3">
          {events.length === 0 && <p className="py-10 text-center text-sm text-stone-500">No events found.</p>}
          {events.map((e) => {
            const id = getId(e);
            return (
              <article key={id} className="rounded-xl border border-stone-200 bg-white p-4">
                <div className="flex flex-wrap items-start justify-between gap-2">
                  <div>
                    <h2 className="font-bold text-stone-900">{e.name}</h2>
                    <p className="text-sm text-stone-600">
                      {formatDate(e.eventDate)} · {e.location?.city ?? '-'} · {e.stats?.label}
                    </p>
                    <p className="text-xs text-stone-500">
                      {e.stats?.taken ?? 0}/{e.stats?.total ?? 0} slots taken
                    </p>
                  </div>
                  <StatusPill status={e.status} />
                </div>
                <div className="mt-3 flex flex-wrap gap-2">
                  <Link to={`/organizer/events/${id}`} className={buttonSecondary}>
                    Edit
                  </Link>
                  {(ACTIONS[e.status] ?? []).map(([action, label]) => (
                    <button key={action} type="button" className={buttonSecondary} onClick={() => changeStatus(id, action)}>
                      {label}
                    </button>
                  ))}
                  <button type="button" className="rounded-lg px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50" onClick={() => remove(e)}>
                    Delete
                  </button>
                </div>
              </article>
            );
          })}
        </div>
      )}

      <Pagination page={meta?.page ?? 1} lastPage={meta?.last_page} onChange={setPage} />
    </div>
  );
}