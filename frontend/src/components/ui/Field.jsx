import { inputClass } from './styles';

// Input atau select (jika `options` diberikan) dengan label dan pesan error
export default function Field({ label, name, error, hint, options, ...props }) {
  const common = { id: name, name, className: inputClass, 'aria-invalid': Boolean(error), ...props };
  return (
    <div>
      <label htmlFor={name} className="mb-1 block text-sm font-medium text-stone-700">
        {label}
      </label>
      {options ? (
        <select {...common}>
          {options.map(([value, text]) => (
            <option key={value} value={value}>
              {text}
            </option>
          ))}
        </select>
      ) : (
        <input {...common} />
      )}
      {hint && !error && <p className="mt-1 text-xs text-stone-500">{hint}</p>}
      {error && <p className="mt-1 text-xs text-red-600">{error}</p>}
    </div>
  );
}