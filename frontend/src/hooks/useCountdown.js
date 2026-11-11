import { useEffect, useState } from 'react';

// Mengembalikan sisa waktu (ms) sampai `iso`, diperbarui tiap detik
export function useCountdown(iso) {
  const target = iso ? new Date(iso).getTime() : null;
  const [left, setLeft] = useState(() => (target ? Math.max(0, target - Date.now()) : 0));

  useEffect(() => {
    if (!target) return undefined;
    const tick = () => setLeft(Math.max(0, target - Date.now()));
    tick();
    const timer = setInterval(tick, 1000);
    return () => clearInterval(timer);
  }, [target]);

  return left;
}

export function formatCountdown(ms) {
  const total = Math.floor(ms / 1000);
  const m = String(Math.floor(total / 60)).padStart(2, '0');
  const s = String(total % 60).padStart(2, '0');
  return `${m}:${s}`;
}