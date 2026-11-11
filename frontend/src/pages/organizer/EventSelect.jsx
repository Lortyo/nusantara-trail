import { inputClass } from '../../components/ui/styles';
import { getId } from '../../lib/event';

export default function EventSelect({ events, value, onChange }) {
  return (
    <select aria-label="Event" className={inputClass} value={value} onChange={(e) => onChange(e.target.value)}>
      {events.map((e) => (
        <option key={getId(e)} value={getId(e)}>
          {e.name} ({e.status})
        </option>
      ))}
    </select>
  );
}