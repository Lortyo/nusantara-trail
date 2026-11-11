import { useCallback, useEffect, useState } from 'react';
import { getErrorMessage } from '../../api/client';
import { registrationsAPI } from '../../api/registrations';
import { transfersAPI } from '../../api/transfers';
import Field from '../../components/ui/Field';
import Spinner from '../../components/ui/Spinner';
import StatusPill from '../../components/ui/StatusPill';
import { buttonPrimary, buttonSecondary } from '../../components/ui/styles';
import { formatDateTime } from '../../lib/format';
import { toast } from '../../stores/toastStore';

export default function BibTransfer() {
  const [paid, setPaid] = useState([]);
  const [transfers, setTransfers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [regId, setRegId] = useState('');
  const [email, setEmail] = useState('');
  const [sending, setSending] = useState(false);
  const [error, setError] = useState('');

  const load = useCallback(async () => {
    try {
      const [regs, tr] = await Promise.all([registrationsAPI.my(), transfersAPI.list()]);
      const list = regs.data.filter((r) => r.status === 'paid');
      setPaid(list);
      setTransfers(tr.data);
      setRegId((current) => current || list[0]?.id || '');
    } catch (err) {
      setError(getErrorMessage(err));
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    load();
  }, [load]);

  const send = async (e) => {
    e.preventDefault();
    setError('');
    setSending(true);
    try {
      await registrationsAPI.transfer(regId, email.trim());
      toast.success('Transfer request sent. The recipient must accept it.');
      setEmail('');
      await load();
    } catch (err) {
      setError(getErrorMessage(err));
    } finally {
      setSending(false);
    }
  };

  const respond = async (id, action) => {
    try {
      await transfersAPI.respond(id, action);
      toast.success(action === 'accept' ? 'BIB transferred to you.' : 'Done.');
      await load();
    } catch (err) {
      toast.error(getErrorMessage(err));
    }
  };

  if (loading) return <Spinner />;

  return (
    <div className="mx-auto max-w-3xl space-y-6">
      <div>
        <h1 className="text-2xl font-extrabold text-stone-900">BIB transfer</h1>
        <p className="text-sm text-stone-600">
          Give your slot to another registered runner. They must accept, and their profile must be complete.
        </p>
      </div>

      <form onSubmit={send} className="space-y-4 rounded-xl border border-stone-200 bg-white p-5">
        <h2 className="font-bold text-stone-900">Send a transfer</h2>
        {paid.length === 0 ? (
          <p className="text-sm text-stone-500">You have no paid registration that can be transferred.</p>
        ) : (
          <>
            <Field
              label="Registration"
              name="registration"
              value={regId}
              onChange={(e) => setRegId(e.target.value)}
              options={paid.map((r) => [r.id, `${r.event} · ${r.category} · BIB ${r.bibNumber ?? '-'}`])}
            />
            <Field
              label="Recipient email"
              name="recipient_email"
              type="email"
              required
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              hint="The recipient must already have an account."
            />
            {error && (
              <div role="alert" className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {error}
              </div>
            )}
            <button type="submit" className={buttonPrimary} disabled={sending}>
              {sending ? 'Sending...' : 'Send request'}
            </button>
          </>
        )}
      </form>

      <section>
        <h2 className="font-bold text-stone-900">Requests</h2>
        {transfers.length === 0 && <p className="mt-2 text-sm text-stone-500">No transfer requests yet.</p>}
        <div className="mt-3 space-y-3">
          {transfers.map((t) => (
            <article key={t.id} className="rounded-xl border border-stone-200 bg-white p-4">
              <div className="flex flex-wrap items-start justify-between gap-2">
                <div className="text-sm">
                  <p className="font-semibold text-stone-900">
                    {t.event} · {t.category}
                  </p>
                  <p className="text-stone-600">
                    {t.direction === 'outgoing' ? 'To' : 'From'} {t.counterpart} · BIB {t.bibNumber ?? '-'}
                  </p>
                  <p className="text-xs text-stone-500">{formatDateTime(t.createdAt)}</p>
                </div>
                <StatusPill status={t.status} />
              </div>

              {t.status === 'requested' && (
                <div className="mt-3 flex gap-2">
                  {t.direction === 'incoming' ? (
                    <>
                      <button type="button" className={buttonPrimary} onClick={() => respond(t.id, 'accept')}>
                        Accept
                      </button>
                      <button type="button" className={buttonSecondary} onClick={() => respond(t.id, 'reject')}>
                        Reject
                      </button>
                    </>
                  ) : (
                    <button type="button" className={buttonSecondary} onClick={() => respond(t.id, 'cancel')}>
                      Cancel request
                    </button>
                  )}
                </div>
              )}
            </article>
          ))}
        </div>
      </section>
    </div>
  );
}