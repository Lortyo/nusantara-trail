const SNAP_URL =
  import.meta.env.VITE_MIDTRANS_PRODUCTION === 'true'
    ? 'https://app.midtrans.com/snap/snap.js'
    : 'https://app.sandbox.midtrans.com/snap/snap.js';

let loading = null;

export function loadSnap(clientKey) {
  if (window.snap) return Promise.resolve(window.snap);
  if (!loading) {
    loading = new Promise((resolve, reject) => {
      const script = document.createElement('script');
      script.src = SNAP_URL;
      script.setAttribute('data-client-key', clientKey);
      script.onload = () => resolve(window.snap);
      script.onerror = () => {
        loading = null;
        reject(new Error('Could not load the Midtrans payment window.'));
      };
      document.body.appendChild(script);
    });
  }
  return loading;
}