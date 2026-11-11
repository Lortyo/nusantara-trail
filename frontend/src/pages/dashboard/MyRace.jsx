import { useCallback, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { getErrorMessage } from '../../api/client';
import { registrationsAPI } from '../../api/registrations';
import Spinner from '../../components/ui/Spinner';
import StatusPill from '../../components/ui/StatusPill';
import { buttonPrimary, buttonSecondary } from '../../components/ui/styles';
import { formatCountdown, useCountdown } from '../../hooks/useCountdown';
import { rupiah } from '../../lib/format';
import { toast } from '../../stores/toastStore';

function Timer({ iso }) {
  const left = useCountdown(iso);
  return left > 0 ? (
    <span className="font-mono text-orange-700">{formatCountdown(left)} left to pay</span>
  ) : (
    <span className="text-stone-500">Reservation expired</span>
  );
}

export default function MyRace() {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const load = useCallback(() => {
    setLoading(true);
    registrationsAPI
      .my()
      .then((res) => setItems(res.data))
      .catch((err) => setError(getErrorMessage(err)))
      .finally(() => setLoading(false));
  }, []);

  useEffect(load, [load]);

  const cancel = async (id) => {
    if (!window.confirm('Cancel this reservation? Your slot will be released.')) return;
    try {
      await registrationsAPI.cancel(id);
      toast.success('Reservation cancelled.');
      load();
    } catch (err) {
      toast.error(getErrorMessage(err));
    }
  };

  if (loading) return <Spinner />;

  return (
    <div className="mx-auto max-w-3xl">
      <h1 className="text-2xl font-extrabold text-stone-900">My race</h1>

      {error && (
        <div role="alert" className="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {error}
        </div>
      )}

      {items.length === 0 && !error && (
        <div className="mt-6 rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
          <p className="text-stone-600">You have not registered for any race yet.</p>
          <Link to="/" className={`${buttonPrimary} mt-4`}>
            Browse events
          </Link>
        </div>
      )}

      <div className="mt-4 space-y-4">
        {items.map((r) => {
          const overdue = r.status === 'pending' && r.expiresAt && new Date(r.expiresAt) < Date.now();
          const status = overdue ? 'expired' : r.status;
          return (
            <article key={r.id} className="rounded-xl border border-stone-200 bg-white p-5">
              <div className="flex flex-wrap items-start justify-between gap-2">
                <div>
                  <h2 className="text-lg font-bold text-stone-900">{r.event}</h2>
                  <p className="text-sm text-stone-600">
                    {r.category} · {rupiah(r.totalAmount)}
                  </p>
                  <p className="font-mono text-xs text-stone-500">{r.registrationCode}</p>
                </div>
                <StatusPill status={status} />
              </div>

              {r.status === 'paid' && (
                <>
                  <p className="mt-3 text-sm">
                    BIB <span className="font-mono text-lg font-bold">{r.bibNumber ?? '-'}</span>
                  </p>
                  <div className="mt-3 flex flex-wrap gap-2">
                    <Link to="/dashboard/race-kits" className={buttonPrimary}>
                      Race kit and QR
                    </Link>
                    <Link to="/dashboard/upgrade-downgrade" className={buttonSecondary}>
                      Change category
                    </Link>
                    <Link to="/dashboard/bib-transfer" className={buttonSecondary}>
                      Transfer BIB
                    </Link>
                  </div>
                </>
              )}

              {r.status === 'pending' && !overdue && (
                <div className="mt-3 flex flex-wrap items-center gap-3">
                  <Timer iso={r.expiresAt} />
                  <Link to={`/payment/${r.id}`} className={buttonPrimary}>
                    Pay now
                  </Link>
                  <button type="button" className={buttonSecondary} onClick={() => cancel(r.id)}>
                    Cancel
                  </button>
                </div>
              )}
            </article>
          );
        })}
      </div>
    </div>
  );
}