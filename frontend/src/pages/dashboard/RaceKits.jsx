import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { eventsAPI } from '../../api/events';
import { registrationsAPI } from '../../api/registrations';
import Spinner from '../../components/ui/Spinner';
import { buttonSecondary } from '../../components/ui/styles';
import { formatDate, formatDateTime } from '../../lib/format';

export default function RaceKits() {
  const [kits, setKits] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;
    (async () => {
      try {
        const res = await registrationsAPI.my();
        const paid = res.data.filter((r) => r.status === 'paid');
        const loaded = await Promise.all(
          paid.map(async (r) => {
            const [cred, ev] = await Promise.allSettled([registrationsAPI.credential(r.id), eventsAPI.get(r.event_id)]);
            return {
              reg: r,
              cred: cred.status === 'fulfilled' ? cred.value.data : null,
              event: ev.status === 'fulfilled' ? ev.value.data.event : null,
            };
          })
        );
        if (!cancelled) setKits(loaded);
      } finally {
        if (!cancelled) setLoading(false);
      }
    })();
    return () => {
      cancelled = true;
    };
  }, []);

  if (loading) return <Spinner />;

  return (
    <div className="mx-auto max-w-3xl">
      <h1 className="text-2xl font-extrabold text-stone-900">Race kits</h1>
      <p className="text-sm text-stone-600">Show your QR code to the organizer when you collect your kit and at check-in.</p>

      {kits.length === 0 && (
        <div className="mt-6 rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center text-stone-600">
          No paid registrations yet.{' '}
          <Link to="/" className="font-medium text-green-800 hover:underline">
            Browse events
          </Link>
        </div>
      )}

      <div className="mt-4 space-y-5">
        {kits.map(({ reg, cred, event }) => (
          <article key={reg.id} className="rounded-xl border border-stone-200 bg-white p-5">
            <h2 className="text-lg font-bold text-stone-900">{reg.event}</h2>

            {!cred ? (
              <p className="mt-2 text-sm text-red-600">Your ticket could not be loaded. Please refresh the page.</p>
            ) : (
              <div className="mt-3 grid gap-5 sm:grid-cols-[auto_1fr]">
                <div className="text-center">
                  <img src={cred.qrImage} alt={`QR code for BIB ${cred.bibNumber}`} className="h-48 w-48 rounded-lg border border-stone-200 p-2" />
                  <a href={cred.qrImage} download={`bib-${cred.bibNumber}.svg`} className={`${buttonSecondary} mt-2 w-full`}>
                    Download QR
                  </a>
                </div>

                <dl className="space-y-1.5 text-sm">
                  <div>
                    <dt className="text-xs uppercase text-stone-500">BIB number</dt>
                    <dd className="font-mono text-3xl font-black text-green-950">{cred.bibNumber}</dd>
                  </div>
                  <div>
                    <dt className="inline text-stone-500">Name on BIB: </dt>
                    <dd className="inline font-medium">{cred.bibName}</dd>
                  </div>
                  <div>
                    <dt className="inline text-stone-500">Category: </dt>
                    <dd className="inline font-medium">{cred.category}</dd>
                  </div>
                  <div>
                    <dt className="inline text-stone-500">Race kit: </dt>
                    <dd className="inline font-medium">
                      {cred.kitCollectedAt ? `Collected ${formatDateTime(cred.kitCollectedAt)}` : 'Not collected yet'}
                    </dd>
                  </div>
                  <div>
                    <dt className="inline text-stone-500">Check-in: </dt>
                    <dd className="inline font-medium">
                      {cred.checkedInAt ? formatDateTime(cred.checkedInAt) : 'Not checked in'}
                    </dd>
                  </div>
                </dl>
              </div>
            )}

            {event?.kitVenue && (
              <div className="mt-4 rounded-lg bg-stone-50 p-4 text-sm">
                <p className="font-semibold text-stone-900">Race kit pickup</p>
                <p className="text-stone-700">
                  {formatDate(event.kitDate)} · {event.kitStartTime} to {event.kitEndTime}
                </p>
                <p className="text-stone-700">
                  {event.kitVenue}
                  {event.kitAddress ? `, ${event.kitAddress}` : ''}
                </p>
                {event.kitMapUrl && (
                  <a href={event.kitMapUrl} target="_blank" rel="noreferrer" className="font-medium text-green-800 hover:underline">
                    Open map
                  </a>
                )}
              </div>
            )}
          </article>
        ))}
      </div>
    </div>
  );
}