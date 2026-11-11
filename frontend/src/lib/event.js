// API bisa mengembalikan `id` atau `_id` tergantung versi serializer
export const getId = (obj) => obj?.id ?? obj?._id;

export const STATE_META = {
  open: { label: 'Registration open', cls: 'bg-green-100 text-green-800' },
  spots_left: { label: 'Few spots left', cls: 'bg-orange-100 text-orange-800' },
  sold_out: { label: 'Sold out', cls: 'bg-red-100 text-red-800' },
  opening_soon: { label: 'Opening soon', cls: 'bg-sky-100 text-sky-800' },
  closed: { label: 'Registration closed', cls: 'bg-stone-200 text-stone-700' },
  finished: { label: 'Finished', cls: 'bg-stone-200 text-stone-700' },
};

export function eventState(event, categories = []) {
  if (!event) return 'closed';
  if (event.status === 'finished') return 'finished';
  if (event.status === 'closed') return 'closed';

  const now = Date.now();
  const opens = event.registrationOpenAt ? new Date(event.registrationOpenAt).getTime() : null;
  const closes = event.registrationCloseAt ? new Date(event.registrationCloseAt).getTime() : null;
  if (opens && now < opens) return 'opening_soon';
  if (closes && now > closes) return 'closed';

  const quota = categories.reduce((n, c) => n + (c.quota ?? 0), 0);
  const left = categories.reduce((n, c) => n + (c.slotsAvailable ?? 0), 0);
  if (quota > 0 && left <= 0) return 'sold_out';
  if (quota > 0 && left <= quota * 0.25) return 'spots_left';
  return 'open';
}

export const canRegister = (state) => state === 'open' || state === 'spots_left';

export function formatLocation(location) {
  if (!location) return '-';
  return [location.venue, location.city, location.province].filter(Boolean).join(', ');
}