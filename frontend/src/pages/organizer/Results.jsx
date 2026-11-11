import { useCallback, useEffect, useState } from 'react';
import { getErrorMessage } from '../../api/client';
import { eventsAPI } from '../../api/events';
import { organizerAPI } from '../../api/organizer';
import EventSelect from './EventSelect';
import Pagination from '../../components/ui/Pagination';
import Spinner from '../../components/ui/Spinner';
import { buttonPrimary, inputClass } from '../../components/ui/styles';
import { useEventChoice } from '../../hooks/useEventChoice';
import { toast } from '../../stores/toastStore';

const TIME = /^\d{1,2}:[0-5]\d:[0-5]\d$/;

async function fetchAllResults(eventId) {
  const all = [];
  let page = 1;
  for (;;) {
    const res = await eventsAPI.results(eventId, { page, limit: 200 });
    all.push(...res.data);
    if (page >= res.meta.last_page) break;
    page += 1;
  }
  return all;
}

export default function Results() {
  const { events, eventId, setEventId, loading: loadingEvents } = useEventChoice();
  const [search, setSearch] = useState('');
  const [q, setQ] = useState('');
  const [page, setPage] = useState(1);
  const [rows, setRows] = useState([]);
  const [meta, setMeta] = useState(null);
  const [existing, setExisting] = useState({}); // bibNumber -> hasil tersimpan
  const [edits, setEdits] = useState({}); // registration_id -> {finishTime, status}
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    const t = setTimeout(() => {
      setQ(search.trim());
      setPage(1);
    }, 400);
    return () => clearTimeout(t);
  }, [search]);

  const loadExisting = useCallback(async () => {
    if (!eventId) return;
    try {
      const all = await fetchAllResults(eventId);
      setExisting(Object.fromEntries(all.filter((r) => r.bibNumber).map((r) => [r.bibNumber, r])));
    } catch {
      setExisting({});
    }
  }, [eventId]);

  useEffect(() => {
    setEdits({});
    setPage(1);
    loadExisting();
  }, [eventId, loadExisting]);

  useEffect(() => {
    if (!eventId) return undefined;
    let cancelled = false;
    setLoading(true);
    organizerAPI
      .registrations({ event_id: eventId, status: 'paid', q: q || undefined, page, limit: 50, sort: 'created_at' })
      .then((res) => {
        if (cancelled) return;
        setRows(res.data);
        setMeta(res.meta);
      })
      .catch((err) => !cancelled && toast.error(getErrorMessage(err)))
      .finally(() => !cancelled && setLoading(false));
    return () => {
      cancelled = true;
    };
  }, [eventId, q, page]);

  const current = (r) => edits[r.id] ?? {
    finishTime: existing[r.bibNumber]?.finishTime ?? '',
    status: existing[r.bibNumber]?.status ?? 'finished',
  };

  const setField = (r, field, value) =>
    setEdits((e) => ({ ...e, [r.id]: { ...current(r), [field]: value } }));

  const changed = Object.keys(edits).length;

  const save = async () => {
    const payload = [];
    const bad = [];
    Object.entries(edits).forEach(([registration_id, v]) => {
      if (v.status === 'finished' && !TIME.test(v.finishTime)) {
        bad.push(registration_id);
        return;
      }
      payload.push({
        registration_id,
        status: v.status,
        ...(v.status === 'finished' ? { finishTime: v.finishTime } : {}),
      });
    });

    if (bad.length) {
      toast.error(`${bad.length} row(s) need a finish time in HH:MM:SS format.`);
      return;
    }
    if (payload.length === 0) return;

    setSaving(true);
    try {
      const res = await organizerAPI.inputResults(eventId, payload);
      toast.success(`Saved: ${res.data.inserted} new, ${res.data.updated} updated.`);
      setEdits({});
      await loadExisting();
    } catch (err) {
      const errs = err.response?.data?.errors;
      const first = errs ? Object.values(errs).flat()[0] : null;
      toast.error(first || getErrorMessage(err, 'Could not save the results.'));
    } finally {
      setSaving(false);
    }
  };

  if (loadingEvents) return <Spinner />;

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="text-2xl font-extrabold text-stone-900">Results</h1>
        <button type="button" className={buttonPrimary} disabled={saving || changed === 0} onClick={save}>
          {saving ? 'Saving...' : `Save ${changed || ''} change${changed === 1 ? '' : 's'}`}
        </button>
      </div>

      <div className="grid gap-3 md:grid-cols-2">
        <EventSelect events={events} value={eventId} onChange={setEventId} />
        <input type="search" placeholder="Search name, BIB or code" aria-label="Search participants" className={inputClass} value={search} onChange={(e) => setSearch(e.target.value)} />
      </div>
      <p className="text-xs text-stone-500">
        Enter finish times as HH:MM:SS. Ranks are calculated automatically. Changes on other pages are kept until you save.
      </p>

      {loading ? (
        <Spinner />
      ) : (
        <div className="overflow-x-auto rounded-xl border border-stone-200 bg-white">
          <table className="w-full text-left text-sm">
            <thead className="bg-stone-100 text-xs uppercase text-stone-600">
              <tr>
                <th className="px-4 py-3">BIB</th>
                <th className="px-4 py-3">Participant</th>
                <th className="px-4 py-3">Category</th>
                <th className="px-4 py-3">Finish time</th>
                <th className="px-4 py-3">Status</th>
                <th className="px-4 py-3">Rank</th>
              </tr>
            </thead>
            <tbody>
              {rows.map((r) => {
                const v = current(r);
                const saved = existing[r.bibNumber];
                return (
                  <tr key={r.id} className={`border-t border-stone-100 ${edits[r.id] ? 'bg-yellow-50' : ''}`}>
                    <td className="px-4 py-2 font-mono font-semibold">{r.bibNumber ?? '-'}</td>
                    <td className="px-4 py-2">{r.name ?? '-'}</td>
                    <td className="px-4 py-2">{r.category ?? '-'}</td>
                    <td className="px-4 py-2">
                      <input
                        className={`${inputClass} w-32 font-mono`}
                        placeholder="HH:MM:SS"
                        aria-label={`Finish time for BIB ${r.bibNumber}`}
                        disabled={v.status !== 'finished'}
                        value={v.finishTime}
                        onChange={(e) => setField(r, 'finishTime', e.target.value)}
                      />
                    </td>
                    <td className="px-4 py-2">
                      <select
                        className={`${inputClass} w-28`}
                        aria-label={`Status for BIB ${r.bibNumber}`}
                        value={v.status}
                        onChange={(e) => setField(r, 'status', e.target.value)}
                      >
                        <option value="finished">Finished</option>
                        <option value="dnf">DNF</option>
                        <option value="dns">DNS</option>
                        <option value="dsq">DSQ</option>
                      </select>
                    </td>
                    <td className="px-4 py-2 text-xs text-stone-600">
                      {saved?.overallRank ? `#${saved.overallRank} overall, #${saved.categoryRank} in category` : '-'}
                    </td>
                  </tr>
                );
              })}
              {rows.length === 0 && (
                <tr>
                  <td colSpan={6} className="px-4 py-8 text-center text-stone-500">
                    No paid participants.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      )}

      <Pagination page={meta?.page ?? 1} lastPage={meta?.last_page} onChange={setPage} />
    </div>
  );
}