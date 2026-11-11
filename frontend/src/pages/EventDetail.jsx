import { useEffect, useState } from 'react';
import { Link, useLocation, useNavigate, useParams } from 'react-router-dom';
import { getErrorMessage } from '../api/client';
import { eventsAPI } from '../api/events';
import { registrationsAPI } from '../api/registrations';
import EventStateBadge from '../components/EventStateBadge';
import Spinner from '../components/ui/Spinner';
import { buttonPrimary, inputClass } from '../components/ui/styles';
import { canRegister, eventState, formatLocation, getId } from '../lib/event';
import { formatDate, formatDateTime, rupiah } from '../lib/format';
import { useAuthStore } from '../stores/authStore';
import { toast } from '../stores/toastStore';

const SIZES = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

export default function EventDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const location = useLocation();
  const isAuthenticated = useAuthStore((s) => s.isAuthenticated);
  const user = useAuthStore((s) => s.user);

  const [event, setEvent] = useState(null);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [categoryId, setCategoryId] = useState('');
  const [jersey, setJersey] = useState('M');
  const [submitting, setSubmitting] = useState(false);
  const [formError, setFormError] = useState('');

  useEffect(() => {
    let cancelled = false;
    setLoading(true);
    eventsAPI
      .get(id)
      .then((res) => {
        if (cancelled) return;
        setEvent(res.data.event);
        setCategories(res.data.categories);
      })
      .catch((err) => !cancelled && setError(getErrorMessage(err, 'Event not found.')))
      .finally(() => !cancelled && setLoading(false));
    return () => {
      cancelled = true;
    };
  }, [id]);

  // Perbarui kuota setiap 10 detik selama tab terlihat
  useEffect(() => {
    if (!event) return undefined;
    const timer = setInterval(() => {
      if (document.visibilityState !== 'visible') return;
      eventsAPI
        .categories(id)
        .then((res) => setCategories(res.data))
        .catch(() => {});
    }, 10000);
    return () => clearInterval(timer);
  }, [event, id]);

  if (loading) return <Spinner />;
  if (error || !event) {
    return (
      <div className="py-16 text-center">
        <p className="text-stone-600">{error || 'Event not found.'}</p>
        <Link to="/" className="mt-3 inline-block text-sm font-medium text-green-800 hover:underline">
          Back to events
        </Link>
      </div>
    );
  }

  const state = eventState(event, categories);
  const open = canRegister(state);
  const selected = categories.find((c) => getId(c) === categoryId);

  const reserve = async () => {
    if (!isAuthenticated) {
      navigate('/login', { state: { from: location } });
      return;
    }
    setFormError('');
    setSubmitting(true);
    try {
      const res = await registrationsAPI.create({ event_id: id, category_id: categoryId, jerseySize: jersey });
      toast.success('Slot reserved. Complete your payment within 15 minutes.');
      navigate(`/payment/${res.data.id}`);
    } catch (err) {
      setFormError(getErrorMessage(err, 'Could not reserve the slot.'));
      // Kuota mungkin berubah, muat ulang
      eventsAPI.categories(id).then((r) => setCategories(r.data)).catch(() => {});
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div>
      {event.bannerUrl ? (
        <img src={event.bannerUrl} alt="" className="h-56 w-full rounded-2xl object-cover sm:h-72" />
      ) : (
        <div className="h-40 w-full rounded-2xl bg-gradient-to-br from-green-900 to-green-700 sm:h-56" />
      )}

      <div className="mt-5 flex flex-wrap items-start justify-between gap-3">
        <div>
          <EventStateBadge state={state} />
          <h1 className="mt-2 text-3xl font-extrabold text-stone-900">{event.name}</h1>
          <p className="mt-1 text-stone-600">
            {formatDate(event.eventDate)}
            {event.startTime ? ` · ${event.startTime}` : ''} · {formatLocation(event.location)}
          </p>
          {event.organizer && <p className="text-sm text-stone-500">Organized by {event.organizer}</p>}
        </div>
        <div className="flex gap-2 text-sm">
          <Link to={`/events/${id}/entry-list`} className="rounded-lg border border-stone-300 bg-white px-3 py-2 font-medium hover:bg-stone-50">
            Entry list
          </Link>
          <Link to={`/events/${id}/results`} className="rounded-lg border border-stone-300 bg-white px-3 py-2 font-medium hover:bg-stone-50">
            Results
          </Link>
        </div>
      </div>

      {event.description && <p className="mt-4 max-w-3xl whitespace-pre-line text-stone-700">{event.description}</p>}

      <p className="mt-4 text-sm text-stone-500">
        Registration: {formatDateTime(event.registrationOpenAt)} to {formatDateTime(event.registrationCloseAt)}
      </p>

      <h2 className="mt-8 text-xl font-bold text-stone-900">Race categories</h2>
      <p className="text-sm text-stone-500">Slots update automatically every 10 seconds.</p>

      <div className="mt-4 grid gap-4 md:grid-cols-2">
        {categories.map((c) => {
          const cid = getId(c);
          const left = c.slotsAvailable ?? 0;
          const pct = c.quota ? Math.round(((c.quota - left) / c.quota) * 100) : 0;
          const soldOut = left <= 0;
          const active = categoryId === cid;
          return (
            <button
              type="button"
              key={cid}
              disabled={!open || soldOut}
              onClick={() => setCategoryId(cid)}
              aria-pressed={active}
              className={`rounded-xl border bg-white p-4 text-left transition disabled:cursor-not-allowed disabled:opacity-60 ${
                active ? 'border-green-700 ring-2 ring-green-700/30' : 'border-stone-200 hover:border-stone-400'
              }`}
            >
              <div className="flex items-start justify-between gap-2">
                <h3 className="text-lg font-bold text-stone-900">{c.name}</h3>
                <span className="text-sm font-semibold text-green-800">{c.distanceKm} km</span>
              </div>
              <p className="mt-1 text-sm text-stone-600">
                Elevation gain {c.elevationGain} m · Cut-off {c.cutOffHours} h
              </p>
              <p className="mt-2 text-sm text-stone-800">
                Local {rupiah(c.price?.local)} · Foreigner {rupiah(c.price?.foreigner)}
              </p>

              <div className="mt-3 h-2 overflow-hidden rounded-full bg-stone-200">
                <div className={`h-full ${soldOut ? 'bg-red-500' : 'bg-green-600'}`} style={{ width: `${pct}%` }} />
              </div>
              <p className={`mt-1 text-xs ${soldOut ? 'font-semibold text-red-600' : 'text-stone-500'}`}>
                {soldOut ? 'Sold out' : `${left} of ${c.quota} slots left`}
              </p>

              {c.qualification?.required && (
                <p className="mt-2 text-xs text-orange-700">
                  Requires a finish of at least {c.qualification.minDistanceKm} km within the last{' '}
                  {c.qualification.withinYears} years.
                </p>
              )}
              {c.benefits?.length > 0 && (
                <ul className="mt-2 list-inside list-disc text-xs text-stone-600">
                  {c.benefits.map((b) => (
                    <li key={b}>{b}</li>
                  ))}
                </ul>
              )}
            </button>
          );
        })}
      </div>

      {categories.length === 0 && <p className="mt-4 text-sm text-stone-500">No categories yet.</p>}

      <div className="mt-8 rounded-xl border border-stone-200 bg-white p-5">
        <h2 className="text-lg font-bold text-stone-900">Reserve your slot</h2>

        {!open ? (
          <p className="mt-2 text-sm text-stone-600">Registration is not available for this event right now.</p>
        ) : user?.role === 'admin' ? (
          <p className="mt-2 text-sm text-stone-600">Organizer accounts cannot register for races.</p>
        ) : (
          <>
            <p className="mt-1 text-sm text-stone-600">
              Your slot is held for 15 minutes while you pay. Complete your{' '}
              <Link to="/dashboard/profile" className="font-medium text-green-800 hover:underline">
                profile
              </Link>{' '}
              first if you have not yet.
            </p>

            {formError && (
              <div role="alert" className="mt-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {formError}
              </div>
            )}

            <div className="mt-4 grid gap-3 sm:grid-cols-[1fr_auto]">
              <div>
                <label htmlFor="jersey" className="mb-1 block text-sm font-medium text-stone-700">
                  Jersey size
                </label>
                <select id="jersey" className={inputClass} value={jersey} onChange={(e) => setJersey(e.target.value)}>
                  {SIZES.map((s) => (
                    <option key={s} value={s}>
                      {s}
                    </option>
                  ))}
                </select>
              </div>
              <div className="flex items-end">
                <button type="button" className={`${buttonPrimary} w-full sm:w-auto`} disabled={!categoryId || submitting} onClick={reserve}>
                  {submitting
                    ? 'Reserving...'
                    : !isAuthenticated
                      ? 'Sign in to register'
                      : selected
                        ? `Reserve ${selected.name}`
                        : 'Select a category'}
                </button>
              </div>
            </div>
          </>
        )}
      </div>
    </div>
  );
}