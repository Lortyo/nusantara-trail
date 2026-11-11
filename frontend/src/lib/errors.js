// Ubah {field: ["pesan", ...]} dari API menjadi {field: "pesan"}
export function getFieldErrors(error) {
  const errs = error?.response?.data?.errors;
  if (!errs || typeof errs !== 'object') return {};
  return Object.fromEntries(
    Object.entries(errs).map(([key, value]) => [key, Array.isArray(value) ? value[0] : String(value)])
  );
}