export const rupiah = (n) => 'Rp ' + Number(n ?? 0).toLocaleString('id-ID');

export function formatDate(iso, options = { day: 'numeric', month: 'long', year: 'numeric' }) {
  return iso ? new Date(iso).toLocaleDateString('en-GB', options) : '-';
}

export function formatDateTime(iso) {
  return iso
    ? new Date(iso).toLocaleString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      })
    : '-';
}