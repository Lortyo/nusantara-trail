import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { getErrorMessage } from '../api/client';
import { eventsAPI } from '../api/events';
import EventStateBadge from '../components/EventStateBadge';
import Pagination from '../components/ui/Pagination';
import Spinner from '../components/ui/Spinner';
import { inputClass } from '../components/ui/styles';
import { eventState, formatLocation, getId } from '../lib/event';
import { formatDate } from '../lib/format';

export default function EventsList() {
  const [search, setSearch] = useState('');
  const [q, setQ] = useState('');
  const [status, setStatus] = useState('');
  const [sort, setSort] = useState('eventDate');
  const [page, setPage] = useState(1);
  const [events, setEvents] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  // Tunggu 400ms setelah berhenti mengetik sebelum memanggil API
  useEffect(() => {
    const t = setTimeout(() => {
      setQ(search.trim());
      setPage(1);
    }, 400);
    return () => clearTimeout(t);
  }, [search]);

  useEffect(() => {
    let cancelled = false;
    setLoading(true);
    setError('');
    eventsAPI
      .list({ q: q || undefined, status: status || undefined, sort, page, limit: 9 })
      .then((res) => {
        if (cancelled) return;
        setEvents(res.data);
        setMeta(res.meta);
      })
      .catch((err) => !cancelled && setError(getErrorMessage(err)))
      .finally(() => !cancelled && setLoading(false));
    return () => {
      cancelled = true;
    };
  }, [q, status, sort, page]);

  return (
    <div>
      <section className="rounded-2xl bg-green-950 px-6 py-10 text-white">
        <p className="text-xs font-semibold uppercase tracking-widest text-lime-300">Nusantara Trail Series</p>
        <h1 className="mt-2 text-3xl font-extrabold sm:text-4xl">Find your next trail race</h1>
        <p className="mt-2 max-w-xl text-sm text-green-100">
          Browse upcoming trail running events across Indonesia and reserve your slot.
        </p>
      </section>

      <div className="mt-6 grid gap-3 sm:grid-cols-[1fr_auto_auto]">
        <input
          type="search"
          placeholder="Search events by name"
          aria-label="Search events"
          className={inputClass}
          value={search}
          onChange={(e) => setSearch(e.target.value)}
        />
        <select
          aria-label="Filter by status"
          className={inputClass}
          value={status}
          onChange={(e) => {
            setStatus(e.target.value);
            setPage(1);
          }}
        >
          <option value="">All statuses</option>
          <option value="open">Open</option>
          <option value="closed">Closed</option>
          <option value="finished">Finished</option>
        </select>
        <select
          aria-label="Sort events"
          className={inputClass}
          value={sort}
          onChange={(e) => {
            setSort(e.target.value);
            setPage(1);
          }}
        >
          <option value="eventDate">Date: earliest first</option>
          <option value="-eventDate">Date: latest first</option>
          <option value="name">Name: A to Z</option>
        </select>
      </div>

      {error && (
        <div role="alert" className="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {error}
        </div>
      )}

      {loading ? (
        <Spinner />
      ) : events.length === 0 && !error ? (
        <p className="mt-10 text-center text-sm text-stone-500">No events match your search.</p>
      ) : (
        <div className="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          {events.map((e) => (
            <Link
              key={getId(e)}
              to={`/events/${getId(e)}`}
              className="group overflow-hidden rounded-xl border border-stone-200 bg-white transition hover:shadow-lg"
            >
              {e.bannerUrl ? (
                <img src={e.bannerUrl} alt="" className="h-40 w-full object-cover" loading="lazy" />
              ) : (
                <div className="h-40 w-full bg-gradient-to-br from-green-900 to-green-700" />
              )}
              <div className="p-4">
                <EventStateBadge state={eventState(e)} />
                <h2 className="mt-2 text-lg font-bold text-stone-900 group-hover:text-green-800">{e.name}</h2>
                <p className="mt-1 text-sm text-stone-600">{formatDate(e.eventDate)}</p>
                <p className="text-sm text-stone-500">{formatLocation(e.location)}</p>
              </div>
            </Link>
          ))}
        </div>
      )}

      <Pagination page={meta?.page ?? 1} lastPage={meta?.last_page} onChange={setPage} />
    </div>
  );
}