import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { getErrorMessage } from '../../api/client';
import { organizerAPI } from '../../api/organizer';
import EventSelect from './EventSelect';
import Spinner from '../../components/ui/Spinner';
import { buttonPrimary } from '../../components/ui/styles';
import { useEventChoice } from '../../hooks/useEventChoice';
import { rupiah } from '../../lib/format';

function Stat({ label, value, sub }) {
  return (
    <div className="rounded-xl border border-stone-200 bg-white p-4">
      <p className="text-xs uppercase tracking-wide text-stone-500">{label}</p>
      <p className="mt-1 text-2xl font-extrabold text-stone-900">{value}</p>
      {sub && <p className="text-xs text-stone-500">{sub}</p>}
    </div>
  );
}

export default function Dashboard() {
  const { events, eventId, setEventId, loading: loadingEvents } = useEventChoice();
  const [data, setData] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    if (!eventId) return undefined;
    let cancelled = false;
    const load = () =>
      organizerAPI
        .summary({ event_id: eventId })
        .then((res) => {
          if (cancelled) return;
          setData(res.data);
          setError('');
        })
        .catch((err) => !cancelled && setError(getErrorMessage(err)));
    load();
    const timer = setInterval(load, 30000);
    return () => {
      cancelled = true;
      clearInterval(timer);
    };
  }, [eventId]);

  if (loadingEvents) return <Spinner />;

  if (events.length === 0) {
    return (
      <div className="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
        <p className="text-stone-600">You have not created any event yet.</p>
        <Link to="/organizer/events/new" className={`${buttonPrimary} mt-4`}>
          Create your first event
        </Link>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="text-2xl font-extrabold text-stone-900">Dashboard</h1>
        <div className="w-full sm:w-80">
          <EventSelect events={events} value={eventId} onChange={setEventId} />
        </div>
      </div>

      {error && (
        <div role="alert" className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {error}
        </div>
      )}

      {!data ? (
        !error && <Spinner />
      ) : (
        <>
          <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <Stat label="Registrations" value={data.totalRegistrations} sub={`${data.paid} paid · ${data.pending} pending`} />
            <Stat label="Slots left" value={data.quotaRemaining} sub={`of ${data.quota} total`} />
            <Stat label="Revenue" value={rupiah(data.revenue)} />
            <Stat label="Kit collected" value={data.kitCollected} sub={`${data.checkedIn} checked in`} />
          </div>

          <section className="rounded-xl border border-stone-200 bg-white p-5">
            <h2 className="font-bold text-stone-900">By category</h2>
            <div className="mt-4 space-y-4">
              {data.categories.map((c) => {
                const pct = c.quota ? Math.min(100, Math.round((c.registered / c.quota) * 100)) : 0;
                return (
                  <div key={c.id}>
                    <div className="flex justify-between text-sm">
                      <span className="font-medium">{c.name}</span>
                      <span className="text-stone-600">
                        {c.registered}/{c.quota} registered · {c.paid} paid · {c.slotsAvailable} left
                      </span>
                    </div>
                    <div className="mt-1 h-2 overflow-hidden rounded-full bg-stone-200">
                      <div className="h-full bg-green-600" style={{ width: `${pct}%` }} />
                    </div>
                  </div>
                );
              })}
              {data.categories.length === 0 && <p className="text-sm text-stone-500">No categories yet.</p>}
            </div>
          </section>
          <p className="text-xs text-stone-500">Updates automatically every 30 seconds.</p>
        </>
      )}
    </div>
  );
}