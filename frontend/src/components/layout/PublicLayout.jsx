import { useState } from 'react';
import { Link, NavLink, Outlet, useNavigate } from 'react-router-dom';
import { useAuthStore } from '../../stores/authStore';
import { toast } from '../../stores/toastStore';

const navLink = ({ isActive }) =>
  `rounded-lg px-3 py-2 text-sm font-medium transition ${
    isActive ? 'bg-green-950 text-white' : 'text-stone-700 hover:bg-stone-100'
  }`;

export default function PublicLayout() {
  const [open, setOpen] = useState(false);
  const navigate = useNavigate();
  const user = useAuthStore((s) => s.user);
  const isAuthenticated = useAuthStore((s) => s.isAuthenticated);
  const logout = useAuthStore((s) => s.logout);

  const closeMenu = () => setOpen(false);
  const isAdmin = user?.role === 'admin';

  const onLogout = async () => {
    closeMenu();
    await logout();
    toast.success('You have been signed out.');
    navigate('/');
  };

  const items = (
    <>
      <NavLink to="/" end className={navLink} onClick={closeMenu}>
        Events
      </NavLink>
      {isAuthenticated ? (
        <>
          <NavLink
            to={isAdmin ? '/organizer/dashboard' : '/dashboard/my-race'}
            className={navLink}
            onClick={closeMenu}
          >
            {isAdmin ? 'Organizer panel' : 'My race'}
          </NavLink>
          <button
            type="button"
            onClick={onLogout}
            className="rounded-lg px-3 py-2 text-left text-sm font-medium text-stone-700 hover:bg-stone-100"
          >
            Sign out ({user?.name})
          </button>
        </>
      ) : (
        <>
          <NavLink to="/login" className={navLink} onClick={closeMenu}>
            Sign in
          </NavLink>
          <Link
            to="/register"
            onClick={closeMenu}
            className="rounded-lg bg-green-950 px-3 py-2 text-sm font-semibold text-white hover:bg-green-900"
          >
            Create account
          </Link>
        </>
      )}
    </>
  );

  return (
    <div className="flex min-h-screen flex-col bg-stone-50">
      <header className="sticky top-0 z-40 border-b border-stone-200 bg-white/90 backdrop-blur">
        <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
          <Link to="/" className="flex items-center gap-2" onClick={closeMenu}>
            <span className="grid h-8 w-8 place-items-center rounded-lg bg-lime-300 text-sm font-black text-green-950">
              NT
            </span>
            <span className="text-sm font-extrabold tracking-wide text-green-950">
              NUSANTARA TRAIL <span className="text-orange-500">SERIES</span>
            </span>
          </Link>
          <button
            type="button"
            className="rounded-lg border border-stone-300 px-3 py-1.5 text-sm md:hidden"
            onClick={() => setOpen((o) => !o)}
            aria-expanded={open}
            aria-label="Toggle menu"
          >
            Menu
          </button>
          <nav className="hidden items-center gap-1 md:flex">{items}</nav>
        </div>
        {open && <nav className="flex flex-col gap-1 border-t border-stone-200 px-4 py-3 md:hidden">{items}</nav>}
      </header>

      <main className="mx-auto w-full max-w-6xl flex-1 px-4 py-6">
        <Outlet />
      </main>

      <footer className="border-t border-stone-200 bg-white py-6 text-center text-xs text-stone-500">
        © {new Date().getFullYear()} Nusantara Trail Series
      </footer>
    </div>
  );
}