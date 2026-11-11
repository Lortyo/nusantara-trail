import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { getErrorMessage } from '../api/client';
import { eventsAPI } from '../api/events';
import Pagination from '../components/ui/Pagination';
import Spinner from '../components/ui/Spinner';
import { inputClass } from '../components/ui/styles';
import { getId } from '../lib/event';

export default function Results() {
  const { id } = useParams();
  const [eventName, setEventName] = useState('');
  const [categories, setCategories] = useState([]);
  const [categoryId, setCategoryId] = useState('');
  const [page, setPage] = useState(1);
  const [rows, setRows] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    eventsAPI
      .get(id)
      .then((res) => {
        setEventName(res.data.event.name);
        setCategories(res.data.categories);
      })
      .catch((err) => setError(getErrorMessage(err, 'Event not found.')));
  }, [id]);

  useEffect(() => {
    let cancelled = false;
    setLoading(true);
    eventsAPI
      .results(id, { page, limit: 50, category_id: categoryId || undefined })
      .then((res) => {
        if (cancelled) return;
        setRows(res.data);
        setMeta(res.meta);
      })
      .catch((err) => !cancelled && setError(getErrorMessage(err)))
      .finally(() => !cancelled && setLoading(false));
    return () => {
      cancelled = true;
    };
  }, [id, page, categoryId]);

  return (
    <div>
      <Link to={`/events/${id}`} className="text-sm font-medium text-green-800 hover:underline">
        Back to event
      </Link>
      <h1 className="mt-2 text-2xl font-extrabold text-stone-900">Race results</h1>
      <p className="text-sm text-stone-600">{eventName}</p>

      <div className="mt-4 max-w-xs">
        <select
          aria-label="Filter by category"
          className={inputClass}
          value={categoryId}
          onChange={(e) => {
            setCategoryId(e.target.value);
            setPage(1);
          }}
        >
          <option value="">All categories</option>
          {categories.map((c) => (
            <option key={getId(c)} value={getId(c)}>
              {c.name}
            </option>
          ))}
        </select>
      </div>

      {error && (
        <div role="alert" className="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {error}
        </div>
      )}

      {loading ? (
        <Spinner />
      ) : (
        <div className="mt-4 overflow-x-auto rounded-xl border border-stone-200 bg-white">
          <table className="w-full text-left text-sm">
            <thead className="bg-stone-100 text-xs uppercase text-stone-600">
              <tr>
                <th className="px-4 py-3">Rank</th>
                <th className="px-4 py-3">Cat. rank</th>
                <th className="px-4 py-3">BIB</th>
                <th className="px-4 py-3">Name</th>
                <th className="px-4 py-3">Category</th>
                <th className="px-4 py-3">Finish time</th>
                <th className="px-4 py-3">Status</th>
              </tr>
            </thead>
            <tbody>
              {rows.map((r) => (
                <tr key={r.id} className="border-t border-stone-100">
                  <td className="px-4 py-2 font-semibold">{r.overallRank ?? '-'}</td>
                  <td className="px-4 py-2">{r.categoryRank ?? '-'}</td>
                  <td className="px-4 py-2 font-mono">{r.bibNumber ?? '-'}</td>
                  <td className="px-4 py-2">{r.bibName || '-'}</td>
                  <td className="px-4 py-2">{r.category || '-'}</td>
                  <td className="px-4 py-2 font-mono">{r.finishTime ?? '-'}</td>
                  <td className="px-4 py-2 uppercase">{r.status}</td>
                </tr>
              ))}
              {rows.length === 0 && (
                <tr>
                  <td colSpan={7} className="px-4 py-8 text-center text-stone-500">
                    Results have not been published yet.
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