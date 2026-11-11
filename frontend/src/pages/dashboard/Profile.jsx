import { useEffect, useState } from 'react';
import { getErrorMessage } from '../../api/client';
import { profileAPI } from '../../api/profile';
import Field from '../../components/ui/Field';
import Spinner from '../../components/ui/Spinner';
import { buttonPrimary } from '../../components/ui/styles';
import { COUNTRIES } from '../../lib/countries';
import { getFieldErrors } from '../../lib/errors';
import { useAuthStore } from '../../stores/authStore';
import { toast } from '../../stores/toastStore';

const EMPTY = {
  fullName: '', bibName: '', gender: 'male', dateOfBirth: '', bloodType: '', nationality: 'ID',
  phone: '', address: '', city: '', country: 'Indonesia', postalCode: '',
  identity_type: 'KTP', identity_number: '', itraId: '', utmbId: '',
  emergency_name: '', emergency_phone: '', emergency_relationship: '',
};

const BLOOD = [['', 'Unknown'], ['A', 'A'], ['B', 'B'], ['AB', 'AB'], ['O', 'O']];

function Section({ title, children }) {
  return (
    <fieldset className="rounded-xl border border-stone-200 bg-white p-5">
      <legend className="px-2 text-sm font-bold text-stone-900">{title}</legend>
      <div className="grid gap-4 sm:grid-cols-2">{children}</div>
    </fieldset>
  );
}

export default function Profile() {
  const user = useAuthStore((s) => s.user);
  const [form, setForm] = useState({ ...EMPTY, fullName: user?.name ?? '' });
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    profileAPI
      .get()
      .then((res) => {
        const p = res.data;
        const loaded = Object.fromEntries(Object.keys(EMPTY).map((k) => [k, p[k] ?? EMPTY[k]]));
        loaded.dateOfBirth = String(p.dateOfBirth ?? '').slice(0, 10);
        setForm(loaded);
      })
      .catch((err) => {
        if (err.response?.status !== 404) toast.error(getErrorMessage(err));
      })
      .finally(() => setLoading(false));
  }, []);

  const onChange = (e) => {
    const { name, value } = e.target;
    setForm((f) => ({ ...f, [name]: value }));
    setErrors((er) => ({ ...er, [name]: undefined }));
  };

  const bind = (name) => ({ name, value: form[name] ?? '', onChange, error: errors[name] });

  const onSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    setErrors({});
    try {
      await profileAPI.update(form);
      toast.success('Profile saved.');
    } catch (err) {
      setErrors(getFieldErrors(err));
      toast.error(getErrorMessage(err, 'Could not save your profile.'));
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <Spinner />;

  return (
    <form onSubmit={onSubmit} className="mx-auto max-w-3xl space-y-5">
      <div>
        <h1 className="text-2xl font-extrabold text-stone-900">Profile</h1>
        <p className="text-sm text-stone-600">
          All fields except blood type, postal code, ITRA and UTMB IDs are required before you can register for a race.
        </p>
      </div>

      <Section title="Personal">
        <Field label="Full name" {...bind('fullName')} required />
        <Field label="Name on BIB" {...bind('bibName')} maxLength={30} required hint="Printed on your race bib." />
        <Field label="Gender" {...bind('gender')} options={[['male', 'Male'], ['female', 'Female']]} />
        <Field label="Date of birth" type="date" {...bind('dateOfBirth')} required />
        <Field label="Blood type" {...bind('bloodType')} options={BLOOD} />
        <Field
          label="Nationality"
          {...bind('nationality')}
          options={COUNTRIES}
          hint="Indonesia uses the local race fee; other countries pay the foreigner fee."
        />
      </Section>

      <Section title="Contact">
        <Field label="Phone" type="tel" {...bind('phone')} required />
        <Field label="Postal code" {...bind('postalCode')} />
        <Field label="Address" {...bind('address')} required />
        <Field label="City" {...bind('city')} required />
        <Field label="Country" {...bind('country')} required />
      </Section>

      <Section title="Identity">
        <Field label="ID type" {...bind('identity_type')} options={[['KTP', 'KTP'], ['Passport', 'Passport'], ['SIM', 'SIM']]} />
        <Field label="ID number" {...bind('identity_number')} required />
        <Field label="ITRA ID (optional)" {...bind('itraId')} />
        <Field label="UTMB ID (optional)" {...bind('utmbId')} />
      </Section>

      <Section title="Emergency contact">
        <Field label="Name" {...bind('emergency_name')} required />
        <Field label="Phone" type="tel" {...bind('emergency_phone')} required />
        <Field label="Relationship" {...bind('emergency_relationship')} required />
      </Section>

      <button type="submit" className={buttonPrimary} disabled={saving}>
        {saving ? 'Saving...' : 'Save profile'}
      </button>
    </form>
  );
}