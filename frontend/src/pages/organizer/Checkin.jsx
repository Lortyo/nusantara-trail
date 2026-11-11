import { useEffect, useRef, useState } from 'react';
import { getErrorMessage } from '../../api/client';
import { organizerAPI } from '../../api/organizer';
import EventSelect from './EventSelect';
import Spinner from '../../components/ui/Spinner';
import { buttonPrimary, buttonSecondary, inputClass } from '../../components/ui/styles';
import { useEventChoice } from '../../hooks/useEventChoice';
import { formatDateTime } from '../../lib/format';

function QrCamera({ onScan }) {
  const videoRef = useRef(null);
  const onScanRef = useRef(onScan);
  const [active, setActive] = useState(false);
  const [error, setError] = useState('');
  const supported = typeof window !== 'undefined' && 'BarcodeDetector' in window;

  useEffect(() => {
    onScanRef.current = onScan;
  }, [onScan]);

  useEffect(() => {
    if (!active) return undefined;
    let stream;
    let timer;
    let stopped = false;
    let last = { value: '', at: 0 };
    const detector = new window.BarcodeDetector({ formats: ['qr_code'] });

    (async () => {
      try {
        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
        if (stopped) {
          stream.getTracks().forEach((t) => t.stop());
          return;
        }
        videoRef.current.srcObject = stream;
        await videoRef.current.play();

        const tick = async () => {
          if (stopped) return;
          try {
            const codes = await detector.detect(videoRef.current);
            const value = codes[0]?.rawValue;
            if (value && (value !== last.value || Date.now() - last.at > 4000)) {
              last = { value, at: Date.now() };
              onScanRef.current(value);
            }
          } catch {
            // frame belum siap
          }
          timer = setTimeout(tick, 250);
        };
        tick();
      } catch (err) {
        setError(`Camera not available: ${err.message}`);
        setActive(false);
      }
    })();

    return () => {
      stopped = true;
      clearTimeout(timer);
      stream?.getTracks().forEach((t) => t.stop());
    };
  }, [active]);

  if (!supported) {
    return <p className="text-xs text-stone-500">Camera scanning is not supported in this browser. Use a USB scanner or type the BIB.</p>;
  }

  return (
    <div>
      <button type="button" className={buttonSecondary} onClick={() => setActive((a) => !a)}>
        {active ? 'Stop camera' : 'Scan with camera'}
      </button>
      {error && <p className="mt-2 text-xs text-red-600">{error}</p>}
      {active && <video ref={videoRef} muted playsInline className="mt-3 w-full max-w-sm rounded-lg bg-black" />}
    </div>
  );
}

export default function Checkin() {
  const { events, eventId, setEventId, loading } = useEventChoice();
  const [mode, setMode] = useState('kit');
  const [by, setBy] = useState('qr');
  const [value, setValue] = useState('');
  const [log, setLog] = useState([]);
  const busy = useRef(false);
  const latest = useRef({});

  useEffect(() => {
    latest.current = { eventId, mode, by };
  }, [eventId, mode, by]);

  const submit = async (raw, source) => {
    const code = String(raw).trim();
    const { eventId: eid, mode: m, by: kind } = latest.current;
    if (!code || !eid || busy.current) return;
    busy.current = true;

    const useQr = source === 'camera' || kind === 'qr';
    const payload = useQr ? { qrToken: code, event_id: eid, mode: m } : { bibNumber: code, event_id: eid, mode: m };

    let entry;
    try {
      const res = await organizerAPI.scan(payload);
      const d = res.data;
      entry = { ok: true, title: res.message, text: `BIB ${d.bibNumber} · ${d.name ?? '-'}`, at: d.recordedAt };
    } catch (err) {
      const d = err.response?.data?.data;
      entry = {
        ok: false,
        title: getErrorMessage(err, 'Scan failed.'),
        text: d ? `BIB ${d.bibNumber} · ${d.name ?? '-'} · recorded ${formatDateTime(d.recordedAt)} by ${d.recordedBy}` : code,
        at: new Date().toISOString(),
      };
    } finally {
      busy.current = false;
    }

    setLog((l) => [{ ...entry, id: `${Date.now()}-${Math.random()}` }, ...l].slice(0, 15));
    setValue('');
  };

  if (loading) return <Spinner />;

  const modeBtn = (m, label) => (
    <button
      type="button"
      onClick={() => setMode(m)}
      aria-pressed={mode === m}
      className={`rounded-lg px-4 py-2 text-sm font-semibold ${mode === m ? 'bg-green-950 text-white' : 'border border-stone-300 bg-white text-stone-700'}`}
    >
      {label}
    </button>
  );

  return (
    <div className="mx-auto max-w-2xl space-y-5">
      <h1 className="text-2xl font-extrabold text-stone-900">Check-in</h1>

      <EventSelect events={events} value={eventId} onChange={setEventId} />

      <div className="flex flex-wrap gap-2">
        {modeBtn('kit', 'Race kit pickup')}
        {modeBtn('checkin', 'Race day check-in')}
      </div>
      <p className="text-xs text-stone-500">
        {mode === 'kit' ? 'Records that the participant collected the race kit.' : 'Race day check-in. The race kit must have been collected first.'}
      </p>

      <div className="space-y-3 rounded-xl border border-stone-200 bg-white p-5">
        <div className="flex gap-4 text-sm">
          <label className="flex items-center gap-2">
            <input type="radio" name="by" checked={by === 'qr'} onChange={() => setBy('qr')} /> QR code
          </label>
          <label className="flex items-center gap-2">
            <input type="radio" name="by" checked={by === 'bib'} onChange={() => setBy('bib')} /> BIB number
          </label>
        </div>

        <form
          onSubmit={(e) => {
            e.preventDefault();
            submit(value, 'input');
          }}
          className="flex gap-2"
        >
          <input
            autoFocus
            className={inputClass}
            placeholder={by === 'qr' ? 'Scan or paste the QR code value' : 'Type the BIB number'}
            value={value}
            onChange={(e) => setValue(e.target.value)}
            aria-label="Code to scan"
          />
          <button type="submit" className={buttonPrimary}>
            Submit
          </button>
        </form>

        <QrCamera onScan={(v) => submit(v, 'camera')} />
      </div>

      <section>
        <h2 className="font-bold text-stone-900">Recent scans</h2>
        {log.length === 0 && <p className="mt-2 text-sm text-stone-500">Nothing scanned yet.</p>}
        <ul className="mt-2 space-y-2">
          {log.map((l) => (
            <li
              key={l.id}
              className={`rounded-lg border px-4 py-3 text-sm ${l.ok ? 'border-green-300 bg-green-50 text-green-900' : 'border-red-300 bg-red-50 text-red-900'}`}
            >
              <p className="font-semibold">{l.title}</p>
              <p>{l.text}</p>
            </li>
          ))}
        </ul>
      </section>
    </div>
  );
}