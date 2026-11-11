import { useEffect, useState } from 'react';
import { organizerAPI } from '../api/organizer';
import { getId } from '../lib/event';

// Daftar event untuk pemilih event di halaman organizer (default: event yang sedang open)
export function useEventChoice() {
  const [events, setEvents] = useState([]);
  const [eventId, setEventId] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    organizerAPI
      .events({ limit: 100 })
      .then((res) => {
        setEvents(res.data);
        const pick = res.data.find((e) => e.status === 'open') ?? res.data[0];
        if (pick) setEventId(getId(pick));
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  }, []);

  return { events, eventId, setEventId, loading };
}