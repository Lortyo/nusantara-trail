import { useCallback, useEffect, useState } from 'react';
import { getErrorMessage } from '../../api/client';
import { eventsAPI } from '../../api/events';
import Field from '../../components/ui/Field';
import Spinner from '../../components/ui/Spinner';
import { buttonPrimary, buttonSecondary } from '../../components/ui/styles';
import { getFieldErrors } from '../../lib/errors';
import { getId } from '../../lib/event';
import { rupiah } from '../../lib/format';
import { toast } from '../../stores/toastStore';

const EMPTY = {
  name: '', distanceKm: '', elevationGain: '0', cutOffHours: '', quota: '',
  priceLocal: '', priceForeigner: '', benefits: '', qualRequired: false, minDistanceKm: '', withinYears: '',
};

const ALIAS = {
  priceLocal: 'price.local',
  priceForeigner: 'price.foreigner',
  minDistanceKm: 'qualification.minDistanceKm',
  withinYears: 'qualification.withinYears',
};

const toForm = (c) => ({
  name: c.name ?? '',
  distanceKm: String(c.distanceKm ?? ''),
  elevationGain: String(c.elevationGain ?? 0),
  cutOffHours: String(c.cutOffHours ?? ''),
  quota: String(c.quota ?? ''),
  priceLocal: String(c.price?.local ?? ''),
  priceForeigner: String(c.price?.foreigner ?? ''),
  benefits: (c.benefits ?? []).join('\n'),
  qualRequired: Boolean(c.qualification?.required),
  minDistanceKm: c.qualification?.minDistanceKm != null ? String(c.qualification.minDistanceKm) : '',
  withinYears: c.qualification?.withinYears != null ? String(c.qualification.withinYears) : '',
});

const toPayload = (f) => ({
  name: f.name,
  distanceKm: Number(f.distanceKm),
  elevationGain: Number(f.elevationGain || 0),
  cutOffHours: Number(f.cutOffHours),
  quota: Number(f.quota),
  price: { local: Number(f.priceLocal), foreigner: Number(f.priceForeigner) },
  benefits: f.benefits.split('\n').map((b) => b.trim()).filter(Boolean),
  qualification: {
    required: f.qualRequired,
    minDistanceKm: f.qualRequired ? Number(f.minDistanceKm) : null,
    withinYears: f.qualRequired ? Number(f.withinYears) : null,
  },
});

export default function CategoryManager({ eventId }) {
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [editing, setEditing] = useState(null); // null | 'new' | id kategori
  const [form, setForm] = useState(EMPTY);
  const [errors, setErrors] = useState({});
  const [saving, setSaving] = useState(false);

  const load = useCallback(() => {
    eventsAPI
      .categories(eventId)
      .then((res) => setCategories(res.data))
      .catch((err) => toast.error(getErrorMessage(err)))
      .finally(() => setLoading(false));
  }, [eventId]);

  useEffect(() => {
    load();
  }, [load]);

  const open = (c) => {
    setErrors({});
    setForm(c ? toForm(c) : EMPTY);
    setEditing(c ? getId(c) : 'new');
  };

  const onChange = (e) => {
    const { name, value } = e.target;
    setForm((f) => ({ ...f, [name]: value }));
    setErrors((er) => ({ ...er, [ALIAS[name] ?? name]: undefined }));
  };
  const bind = (name) => ({ name, value: form[name], onChange, error: errors[ALIAS[name] ?? name] });

  const save = async (e) => {
    e.preventDefault();
    setSaving(true);
    setErrors({});
    try {
      if (editing === 'new') {
        await eventsAPI.createCategory(eventId, toPayload(form));
        toast.success('Category added.');
      } else {
        await eventsAPI.updateCategory(editing, toPayload(form));
        toast.success('Category saved.');
      }
      setEditing(null);
      load();
    } catch (err) {
      setErrors(getFieldErrors(err));
      toast.error(getErrorMessage(err, 'Could not save the category.'));
    } finally {
      setSaving(false);
    }
  };

  const remove = async (c) => {
    if (!window.confirm(`Delete category "${c.name}"?`)) return;
    try {
      await eventsAPI.removeCategory(getId(c));
      toast.success('Category deleted.');
      load();
    } catch (err) {
      toast.error(getErrorMessage(err));
    }
  };

  if (loading) return <Spinner />;

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <p className="text-sm text-stone-600">Add at least one category before opening registration.</p>
        {editing === null && (
          <button type="button" className={buttonPrimary} onClick={() => open(null)}>
            Add category
          </button>
        )}
      </div>

      {editing !== null && (
        <form onSubmit={save} className="space-y-4 rounded-xl border border-green-700/30 bg-white p-5">
          <h2 className="font-bold text-stone-900">{editing === 'new' ? 'New category' : 'Edit category'}</h2>
          <div className="grid gap-4 sm:grid-cols-2">
            <Field label="Name" {...bind('name')} required placeholder="e.g. 21K Trail" />
            <Field label="Distance (km)" type="number" step="0.1" min="1" {...bind('distanceKm')} required />
            <Field label="Elevation gain (m)" type="number" min="0" {...bind('elevationGain')} required />
            <Field label="Cut-off (hours)" type="number" step="0.5" min="1" {...bind('cutOffHours')} required />
            <Field label="Quota" type="number" min="1" {...bind('quota')} required />
            <div />
            <Field label="Price, local (IDR)" type="number" min="0" {...bind('priceLocal')} required />
            <Field label="Price, foreigner (IDR)" type="number" min="0" {...bind('priceForeigner')} required />
          </div>

          <div>
            <label htmlFor="benefits" className="mb-1 block text-sm font-medium text-stone-700">
              Benefits (one per line)
            </label>
            <textarea
              id="benefits"
              name="benefits"
              rows={3}
              className="block w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700/20"
              value={form.benefits}
              onChange={onChange}
            />
          </div>

          <label className="flex items-center gap-2 text-sm">
            <input type="checkbox" checked={form.qualRequired} onChange={(e) => setForm((f) => ({ ...f, qualRequired: e.target.checked }))} />
            Requires a qualifying finish
          </label>
          {form.qualRequired && (
            <div className="grid gap-4 sm:grid-cols-2">
              <Field label="Minimum distance (km)" type="number" min="1" {...bind('minDistanceKm')} required />
              <Field label="Within the last (years)" type="number" min="1" max="10" {...bind('withinYears')} required />
            </div>
          )}

          <div className="flex gap-2">
            <button type="submit" className={buttonPrimary} disabled={saving}>
              {saving ? 'Saving...' : 'Save category'}
            </button>
            <button type="button" className={buttonSecondary} onClick={() => setEditing(null)}>
              Cancel
            </button>
          </div>
        </form>
      )}

      <div className="overflow-x-auto rounded-xl border border-stone-200 bg-white">
        <table className="w-full text-left text-sm">
          <thead className="bg-stone-100 text-xs uppercase text-stone-600">
            <tr>
              <th className="px-4 py-3">Category</th>
              <th className="px-4 py-3">Distance</th>
              <th className="px-4 py-3">Slots</th>
              <th className="px-4 py-3">Price (local / foreigner)</th>
              <th className="px-4 py-3" />
            </tr>
          </thead>
          <tbody>
            {categories.map((c) => (
              <tr key={getId(c)} className="border-t border-stone-100">
                <td className="px-4 py-3 font-medium">{c.name}</td>
                <td className="px-4 py-3">{c.distanceKm} km</td>
                <td className="px-4 py-3">
                  {c.quota - c.slotsAvailable}/{c.quota} taken
                </td>
                <td className="px-4 py-3">
                  {rupiah(c.price?.local)} / {rupiah(c.price?.foreigner)}
                </td>
                <td className="whitespace-nowrap px-4 py-3 text-right">
                  <button type="button" className="mr-2 font-medium text-green-800 hover:underline" onClick={() => open(c)}>
                    Edit
                  </button>
                  <button type="button" className="font-medium text-red-700 hover:underline" onClick={() => remove(c)}>
                    Delete
                  </button>
                </td>
              </tr>
            ))}
            {categories.length === 0 && (
              <tr>
                <td colSpan={5} className="px-4 py-8 text-center text-stone-500">
                  No categories yet.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}