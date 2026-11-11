import { useEffect, useState } from 'react';
import { Link, useNavigate, useParams, useSearchParams } from 'react-router-dom';
import { getErrorMessage } from '../../api/client';
import { eventsAPI } from '../../api/events';
import { organizerAPI } from '../../api/organizer';
import Field from '../../components/ui/Field';
import Spinner from '../../components/ui/Spinner';
import StatusPill from '../../components/ui/StatusPill';
import { buttonPrimary } from '../../components/ui/styles';
import { getFieldErrors } from '../../lib/errors';
import { getId } from '../../lib/event';
import { toast } from '../../stores/toastStore';
import CategoryManager from './CategoryManager';

const EMPTY = {
  name: '', eventDate: '', startTime: '06:00', venue: '', city: '', province: '',
  description: '', organizer: '', contactEmail: '', timezone: 'Asia/Makassar',
  registrationOpenAt: '', registrationCloseAt: '', transferDeadline: '', categoryChangeDeadline: '',
  bannerUrl: '', kitDate: '', kitStartTime: '', kitEndTime: '', kitVenue: '', kitAddress: '', kitMapUrl: '',
};

const ALIAS = { venue: 'location.venue', city: 'location.city', province: 'location.province' };
const pad = (n) => String(n).padStart(2, '0');

// ISO (UTC) -> nilai input datetime-local di zona waktu browser
function toInputDateTime(iso) {
  if (!iso) return '';
  const d = new Date(iso);
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}
const fromInputDateTime = (v) => (v ? new Date(v).toISOString() : null);
const dateOnly = (iso) => (iso ? String(iso).slice(0, 10) : '');
const nul = (v) => (v === '' || v == null ? null : v);

function toForm(e) {
  return {
    ...EMPTY,
    name: e.name ?? '',
    eventDate: dateOnly(e.eventDate),
    startTime: e.startTime ?? '',
    venue: e.location?.venue ?? '',
    city: e.location?.city ?? '',
    province: e.location?.province ?? '',
    description: e.description ?? '',
    organizer: e.organizer ?? '',
    contactEmail: e.contactEmail ?? '',
    timezone: e.timezone ?? 'Asia/Makassar',
    registrationOpenAt: toInputDateTime(e.registrationOpenAt),
    registrationCloseAt: toInputDateTime(e.registrationCloseAt),
    transferDeadline: toInputDateTime(e.transferDeadline),
    categoryChangeDeadline: toInputDateTime(e.categoryChangeDeadline),
    bannerUrl: e.bannerUrl ?? '',
    kitDate: dateOnly(e.kitDate),
    kitStartTime: e.kitStartTime ?? '',
    kitEndTime: e.kitEndTime ?? '',
    kitVenue: e.kitVenue ?? '',
    kitAddress: e.kitAddress ?? '',
    kitMapUrl: e.kitMapUrl ?? '',
  };
}

function toPayload(f) {
  return {
    name: f.name,
    eventDate: f.eventDate,
    startTime: f.startTime,
    location: { venue: f.venue, city: f.city, province: f.province },
    description: nul(f.description),
    organizer: nul(f.organizer),
    contactEmail: nul(f.contactEmail),
    timezone: f.timezone,
    currency: 'IDR',
    registrationOpenAt: fromInputDateTime(f.registrationOpenAt),
    registrationCloseAt: fromInputDateTime(f.registrationCloseAt),
    transferDeadline: fromInputDateTime(f.transferDeadline),
    categoryChangeDeadline: fromInputDateTime(f.categoryChangeDeadline),
    bannerUrl: nul(f.bannerUrl),
    kitDate: nul(f.kitDate),
    kitStartTime: nul(f.kitStartTime),
    kitEndTime: nul(f.kitEndTime),
    kitVenue: nul(f.kitVenue),
    kitAddress: nul(f.kitAddress),
    kitMapUrl: nul(f.kitMapUrl),
  };
}

function Section({ title, children }) {
  return (
    <fieldset className="rounded-xl border border-stone-200 bg-white p-5">
      <legend className="px-2 text-sm font-bold text-stone-900">{title}</legend>
      <div className="grid gap-4 sm:grid-cols-2">{children}</div>
    </fieldset>
  );
}

export default function EventForm() {
  const { id } = useParams();
  const isNew = !id;
  const navigate = useNavigate();
  const [params, setParams] = useSearchParams();
  const tab = params.get('tab') === 'categories' && !isNew ? 'categories' : 'details';

  const [form, setForm] = useState(EMPTY);
  const [status, setStatus] = useState('draft');
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(!isNew);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (isNew) return;
    organizerAPI
      .event(id)
      .then((res) => {
        setForm(toForm(res.data.event));
        setStatus(res.data.event.status);
      })
      .catch((err) => {
        toast.error(getErrorMessage(err, 'Event not found.'));
        navigate('/organizer/events');
      })
      .finally(() => setLoading(false));
  }, [id, isNew, navigate]);

  const onChange = (e) => {
    const { name, value } = e.target;
    setForm((f) => ({ ...f, [name]: value }));
    setErrors((er) => ({ ...er, [ALIAS[name] ?? name]: undefined }));
  };
  const bind = (name) => ({ name, value: form[name] ?? '', onChange, error: errors[ALIAS[name] ?? name] });

  const onSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    setErrors({});
    try {
      if (isNew) {
        const res = await eventsAPI.create(toPayload(form));
        toast.success('Event created. Now add the race categories.');
        navigate(`/organizer/events/${getId(res.data)}?tab=categories`, { replace: true });
      } else {
        await eventsAPI.update(id, toPayload(form));
        toast.success('Event saved.');
      }
    } catch (err) {
      setErrors(getFieldErrors(err));
      toast.error(getErrorMessage(err, 'Could not save the event.'));
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <Spinner />;

  const tabClass = (active) =>
    `rounded-lg px-4 py-2 text-sm font-semibold ${active ? 'bg-green-950 text-white' : 'bg-white text-stone-700 hover:bg-stone-50 border border-stone-300'}`;

  return (
    <div className="mx-auto max-w-4xl space-y-5">
      <div>
        <Link to="/organizer/events" className="text-sm font-medium text-green-800 hover:underline">
          Back to events
        </Link>
        <div className="mt-1 flex items-center gap-3">
          <h1 className="text-2xl font-extrabold text-stone-900">{isNew ? 'New event' : form.name || 'Edit event'}</h1>
          {!isNew && <StatusPill status={status} />}
        </div>
      </div>

      {!isNew && (
        <div className="flex gap-2">
          <button type="button" className={tabClass(tab === 'details')} onClick={() => setParams({})}>
            Details
          </button>
          <button type="button" className={tabClass(tab === 'categories')} onClick={() => setParams({ tab: 'categories' })}>
            Categories
          </button>
        </div>
      )}

      {tab === 'categories' ? (
        <CategoryManager eventId={id} />
      ) : (
        <form onSubmit={onSubmit} className="space-y-5">
          <Section title="Basics">
            <Field label="Event name" {...bind('name')} required />
            <Field label="Organizer" {...bind('organizer')} />
            <Field label="Event date" type="date" {...bind('eventDate')} required />
            <Field label="Start time" type="time" {...bind('startTime')} required />
            <Field label="Time zone" {...bind('timezone')} options={[['Asia/Jakarta', 'WIB (Asia/Jakarta)'], ['Asia/Makassar', 'WITA (Asia/Makassar)'], ['Asia/Jayapura', 'WIT (Asia/Jayapura)']]} />
            <Field label="Contact email" type="email" {...bind('contactEmail')} />
            <Field label="Banner image URL" type="url" {...bind('bannerUrl')} />
          </Section>

          <Section title="Location">
            <Field label="Venue" {...bind('venue')} required />
            <Field label="City" {...bind('city')} required />
            <Field label="Province" {...bind('province')} required />
          </Section>

          <fieldset className="rounded-xl border border-stone-200 bg-white p-5">
            <legend className="px-2 text-sm font-bold text-stone-900">Description</legend>
            <textarea
              name="description"
              rows={5}
              maxLength={3000}
              className="block w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20"
              value={form.description}
              onChange={onChange}
            />
            {errors.description && <p className="mt-1 text-xs text-red-600">{errors.description}</p>}
          </fieldset>

          <Section title="Registration schedule">
            <Field label="Registration opens" type="datetime-local" {...bind('registrationOpenAt')} />
            <Field label="Registration closes" type="datetime-local" {...bind('registrationCloseAt')} hint="Must be before the event date." />
            <Field label="BIB transfer deadline" type="datetime-local" {...bind('transferDeadline')} hint="Defaults to the event date." />
            <Field label="Category change deadline" type="datetime-local" {...bind('categoryChangeDeadline')} hint="Defaults to registration close." />
          </Section>

          <Section title="Race kit pickup (optional)">
            <Field label="Date" type="date" {...bind('kitDate')} />
            <Field label="Venue" {...bind('kitVenue')} />
            <Field label="From" type="time" {...bind('kitStartTime')} />
            <Field label="Until" type="time" {...bind('kitEndTime')} />
            <Field label="Address" {...bind('kitAddress')} />
            <Field label="Map link" type="url" {...bind('kitMapUrl')} />
          </Section>

          <button type="submit" className={buttonPrimary} disabled={saving}>
            {saving ? 'Saving...' : isNew ? 'Create event' : 'Save changes'}
          </button>
        </form>
      )}
    </div>
  );
}