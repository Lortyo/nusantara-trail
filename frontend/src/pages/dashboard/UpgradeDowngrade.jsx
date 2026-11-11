import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { getErrorMessage } from '../../api/client';
import { eventsAPI } from '../../api/events';
import { registrationsAPI } from '../../api/registrations';
import Field from '../../components/ui/Field';
import Spinner from '../../components/ui/Spinner';
import { buttonPrimary } from '../../components/ui/styles';
import { getId } from '../../lib/event';
import { rupiah } from '../../lib/format';
import { toast } from '../../stores/toastStore';

export default function UpgradeDowngrade() {
  const navigate = useNavigate();
  const [paid, setPaid] = useState([]);
  const [loading, setLoading] = useState(true);
  const [regId, setRegId] = useState('');
  const [categories, setCategories] = useState([]);
  const [target, setTarget] = useState('');
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    registrationsAPI
      .my()
      .then((res) => {
        const list = res.data.filter((r) => r.status === 'paid');
        setPaid(list);
        setRegId(list[0]?.id ?? '');
      })
      .catch((err) => setError(getErrorMessage(err)))
      .finally(() => setLoading(false));
  }, []);

  const reg = paid.find((r) => r.id === regId);

  useEffect(() => {
    setTarget('');
    setCategories([]);
    if (!reg) return;
    eventsAPI
      .categories(reg.event_id)
      .then((res) => setCategories(res.data))
      .catch((err) => setError(getErrorMessage(err)));
  }, [reg?.event_id]); // eslint-disable-line react-hooks/exhaustive-deps

  const submit = async () => {
    setError('');
    setBusy(true);
    try {
      await registrationsAPI.changeCategory(regId, { new_category_id: target });
      toast.success('Category changed.');
      navigate('/dashboard/my-race');
    } catch (err) {
      const status = err.response?.status;
      const data = err.response?.data?.data;
      if (status === 402 && data?.change_id) {
        toast.info('Please pay the price difference to complete the change.');
        navigate(
          `/payment/${regId}?change=${data.change_id}&total=${data.total}&expires=${encodeURIComponent(data.expiresAt)}`
        );
        return;
      }
      setError(getErrorMessage(err));
    } finally {
      setBusy(false);
    }
  };

  if (loading) return <Spinner />;

  return (
    <div className="mx-auto max-w-3xl space-y-5">
      <div>
        <h1 className="text-2xl font-extrabold text-stone-900">Upgrade / downgrade</h1>
        <p className="text-sm text-stone-600">
          Moving to a more expensive category requires paying the difference. Moving to a cheaper one is free, but the
          difference is not refunded.
        </p>
      </div>

      {paid.length === 0 ? (
        <p className="rounded-xl border border-dashed border-stone-300 bg-white p-8 text-center text-sm text-stone-600">
          You need a paid registration to change category.
        </p>
      ) : (
        <>
          <Field
            label="Registration"
            name="registration"
            value={regId}
            onChange={(e) => setRegId(e.target.value)}
            options={paid.map((r) => [r.id, `${r.event} · currently ${r.category}`])}
          />

          <div className="grid gap-3 sm:grid-cols-2">
            {categories.map((c) => {
              const cid = getId(c);
              const full = (c.slotsAvailable ?? 0) <= 0;
              return (
                <button
                  type="button"
                  key={cid}
                  disabled={full}
                  onClick={() => setTarget(cid)}
                  aria-pressed={target === cid}
                  className={`rounded-xl border bg-white p-4 text-left disabled:cursor-not-allowed disabled:opacity-50 ${
                    target === cid ? 'border-green-700 ring-2 ring-green-700/30' : 'border-stone-200 hover:border-stone-400'
                  }`}
                >
                  <p className="font-bold text-stone-900">
                    {c.name} · {c.distanceKm} km
                  </p>
                  <p className="text-sm text-stone-600">
                    Local {rupiah(c.price?.local)} · Foreigner {rupiah(c.price?.foreigner)}
                  </p>
                  <p className={`text-xs ${full ? 'text-red-600' : 'text-stone-500'}`}>
                    {full ? 'Sold out' : `${c.slotsAvailable} slots left`}
                  </p>
                </button>
              );
            })}
          </div>

          {error && (
            <div role="alert" className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
              {error}
            </div>
          )}

          <button type="button" className={buttonPrimary} disabled={!target || busy} onClick={submit}>
            {busy ? 'Processing...' : 'Change category'}
          </button>
        </>
      )}
    </div>
  );
}