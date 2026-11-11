import { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams, useSearchParams } from 'react-router-dom';
import { getErrorMessage } from '../api/client';
import { paymentsAPI } from '../api/payments';
import { registrationsAPI } from '../api/registrations';
import Spinner from '../components/ui/Spinner';
import { buttonPrimary, buttonSecondary } from '../components/ui/styles';
import { formatCountdown, useCountdown } from '../hooks/useCountdown';
import { rupiah } from '../lib/format';
import { loadSnap } from '../lib/snap';
import { toast } from '../stores/toastStore';

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

export default function Payment() {
  const { registrationId } = useParams();
  const [params] = useSearchParams();
  const navigate = useNavigate();
  const changeId = params.get('change');

  const [reg, setReg] = useState(null);
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');
  const [paid, setPaid] = useState(false);
  const [processing, setProcessing] = useState(false);

  useEffect(() => {
    let cancelled = false;
    registrationsAPI
      .get(registrationId)
      .then((res) => {
        if (cancelled) return;
        setReg(res.data);
        if (!changeId && res.data.status === 'paid') setPaid(true);
      })
      .catch((err) => !cancelled && setError(getErrorMessage(err, 'Registration not found.')))
      .finally(() => !cancelled && setLoading(false));
    return () => {
      cancelled = true;
    };
  }, [registrationId, changeId]);

  const expiresAt = changeId ? params.get('expires') : reg?.expiresAt;
  const amount = changeId ? Number(params.get('total')) : reg?.totalAmount;
  const left = useCountdown(expiresAt);
  const expired = Boolean(expiresAt) && left <= 0;

  // Tanya status ke Midtrans sampai lunas (webhook tidak sampai ke localhost)
  const confirm = useCallback(async (orderId) => {
    for (let i = 0; i < 6; i += 1) {
      try {
        const res = await paymentsAPI.status(orderId, true);
        const status = res.data.status;
        if (status === 'settlement') {
          setPaid(true);
          toast.success('Payment received.');
          return;
        }
        if (['expire', 'cancel', 'deny'].includes(status)) {
          setError('The payment was not completed. Please try again or start over.');
          return;
        }
      } catch {
        // coba lagi
      }
      await sleep(2000);
    }
    setProcessing(true);
  }, []);

  const pay = async () => {
    setBusy(true);
    setError('');
    try {
      const res = await paymentsAPI.checkout(changeId ? { change_id: changeId } : { registration_id: registrationId });
      const { snapToken, orderId, clientKey } = res.data;
      const snap = await loadSnap(clientKey);
      snap.pay(snapToken, {
        onSuccess: () => confirm(orderId).finally(() => setBusy(false)),
        onPending: () => confirm(orderId).finally(() => setBusy(false)),
        onError: () => {
          setError('Payment failed. Please try again.');
          setBusy(false);
        },
        onClose: () => setBusy(false),
      });
    } catch (err) {
      setError(getErrorMessage(err, 'Could not start the payment.'));
      setBusy(false);
    }
  };

  const cancelReservation = async () => {
    if (!window.confirm('Cancel this reservation? Your slot will be released.')) return;
    try {
      await registrationsAPI.cancel(registrationId);
      toast.success('Reservation cancelled.');
      navigate('/dashboard/my-race');
    } catch (err) {
      setError(getErrorMessage(err));
    }
  };

  if (loading) return <Spinner />;

  return (
    <div className="mx-auto max-w-xl">
      <h1 className="text-2xl font-extrabold text-stone-900">{changeId ? 'Category change payment' : 'Payment'}</h1>

      {!reg ? (
        <div role="alert" className="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {error || 'Registration not found.'}
        </div>
      ) : paid ? (
        <div className="mt-4 rounded-xl border border-green-200 bg-green-50 p-6 text-center">
          <p className="text-lg font-bold text-green-900">Payment successful</p>
          <p className="mt-1 text-sm text-green-800">Your BIB and QR code are ready.</p>
          <div className="mt-4 flex justify-center gap-2">
            <Link to="/dashboard/race-kits" className={buttonPrimary}>
              View race kit
            </Link>
            <Link to="/dashboard/my-race" className={buttonSecondary}>
              My race
            </Link>
          </div>
        </div>
      ) : (
        <div className="mt-4 rounded-xl border border-stone-200 bg-white p-6">
          <dl className="space-y-2 text-sm">
            <div className="flex justify-between gap-4">
              <dt className="text-stone-500">Event</dt>
              <dd className="text-right font-medium">{reg.event}</dd>
            </div>
            <div className="flex justify-between gap-4">
              <dt className="text-stone-500">Category</dt>
              <dd className="text-right font-medium">{reg.category}</dd>
            </div>
            <div className="flex justify-between gap-4">
              <dt className="text-stone-500">Registration code</dt>
              <dd className="font-mono">{reg.registrationCode}</dd>
            </div>
            {!changeId && (
              <div className="flex justify-between gap-4">
                <dt className="text-stone-500">Jersey size</dt>
                <dd>{reg.jerseySize}</dd>
              </div>
            )}
            <div className="flex justify-between gap-4 border-t border-stone-200 pt-2 text-base">
              <dt className="font-semibold">Total</dt>
              <dd className="font-bold text-green-900">{rupiah(amount)}</dd>
            </div>
          </dl>

          <div
            className={`mt-4 rounded-lg px-4 py-3 text-center text-sm ${
              expired ? 'bg-red-50 text-red-700' : 'bg-orange-50 text-orange-800'
            }`}
          >
            {expired ? (
              'Your reservation has expired and the slot was released.'
            ) : (
              <>
                Your slot is held for <span className="font-mono text-lg font-bold">{formatCountdown(left)}</span>
              </>
            )}
          </div>

          {processing && (
            <p className="mt-3 rounded-lg bg-sky-50 px-4 py-3 text-sm text-sky-800">
              We are still waiting for confirmation from the payment provider. Check{' '}
              <Link to="/dashboard/my-race" className="font-semibold underline">
                My race
              </Link>{' '}
              in a minute.
            </p>
          )}

          {error && (
            <div role="alert" className="mt-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
              {error}
            </div>
          )}

          <div className="mt-5 flex flex-wrap gap-2">
            {expired ? (
              <Link to="/" className={buttonPrimary}>
                Back to events
              </Link>
            ) : (
              <>
                <button type="button" className={buttonPrimary} onClick={pay} disabled={busy}>
                  {busy ? 'Processing...' : `Pay ${rupiah(amount)}`}
                </button>
                {!changeId && (
                  <button type="button" className={buttonSecondary} onClick={cancelReservation} disabled={busy}>
                    Cancel reservation
                  </button>
                )}
              </>
            )}
          </div>
        </div>
      )}
    </div>
  );
}