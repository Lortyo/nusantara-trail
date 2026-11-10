import { Link, NavLink, Outlet, useNavigate } from 'react-router-dom';
import { useAuthStore } from '../../stores/authStore';
import { toast } from '../../stores/toastStore';
import { 
  Mountains, 
  User, 
  Flag, 
  Package, 
  ArrowsLeftRight, 
  CalendarBlank, 
  Circle, 
  SignOut 
} from '@phosphor-icons/react';

export default function SidebarLayout({ title, links }) {
  const navigate = useNavigate();
  const user = useAuthStore((s) => s.user);
  const logout = useAuthStore((s) => s.logout);

  const onLogout = async (e) => {
    e.preventDefault();
    await logout();
    toast.success('You have been signed out.');
    navigate('/');
  };

  // Sekarang me-return langsung komponen React dari Phosphor Icons
  const getIconForLabel = (label) => {
    const l = label.toLowerCase();
    if (l.includes('profile')) return User;
    if (l.includes('event')) return Flag;
    if (l.includes('race')) return Package;
    if (l.includes('category')) return ArrowsLeftRight;
    if (l.includes('calendar') || l.includes('events')) return CalendarBlank;
    return Circle; // Fallback icon
  };

  return (
    <div className="bg-gray-50 flex h-screen font-sans overflow-hidden">
      
      {/* SIDEBAR (COMPACT MODE) */}
      <div className="w-[260px] bg-[#0c2016] text-white p-4 flex flex-col justify-between border-r border-[#153123] h-screen overflow-hidden">
        
        {/* Bagian Atas */}
        <div>
          {/* Logo */}
          <div className="flex items-center gap-2 mb-6 mt-2">
            <div className="bg-[#d2f371] w-10 h-10 rounded-xl flex items-center justify-center text-[#0c2016]">
              <Mountains size={24} weight="bold" />
            </div>
            <Link to="/" className="font-bold text-[13px] tracking-widest leading-tight block">
              NUSANTARA TRAIL<br />
              <span className="text-[10px] text-[#d2f371]">{title || 'SERIES'}</span>
            </Link>
          </div>

          {/* Daftar Menu */}
          <p className="text-[10px] text-gray-400 font-bold mb-2 uppercase tracking-wider px-1">
            Participant Portal
          </p>
          <ul className="space-y-0.5 text-[13px] font-medium">
            {links.map((l) => {
              const IconComponent = getIconForLabel(l.label);
              
              return (
                <li key={l.to}>
                  <NavLink
                    to={l.to}
                    end={l.end}
                    className={({ isActive }) =>
                      `${isActive ? 'bg-[#183626] text-white' : 'text-gray-300 hover:text-white hover:bg-[#183626]'} flex items-center justify-between py-2 px-3 rounded-lg transition-all`
                    }
                  >
                    {({ isActive }) => (
                      <>
                        <div className="flex items-center gap-2.5">
                          <IconComponent 
                            size={18} 
                            weight={isActive ? "fill" : "regular"} 
                            className={isActive ? 'text-[#d2f371]' : ''} 
                          />
                          <span>{l.label}</span>
                        </div>
                        {isActive && <div className="w-1 h-4 bg-orange-500 rounded-full"></div>}
                      </>
                    )}
                  </NavLink>
                </li>
              );
            })}
          </ul>
        </div>

        {/* Bagian Bawah */}
        <div className="mb-2">
          {/* Box Bantuan */}
          <div className="bg-[#183626] p-3 rounded-xl mb-3">
            <h3 className="font-bold text-[13px] mb-1 text-white">Need help?</h3>
            <p className="text-[11px] text-gray-300 mb-2 leading-relaxed">
              Participant support: Mon–Fri,<br />09:00–17:00 WIB.
            </p>
            <a href="mailto:peserta@nusantaratrail.id" className="text-[#d2f371] text-[11px] font-bold hover:underline">
              peserta@nusantaratrail.id
            </a>
          </div>

          {/* User Mini Profile */}
          <div className="flex items-center justify-between px-1">
            <div className="flex items-center gap-2.5">
              <div className="w-8 h-8 bg-[#fde9de] rounded-full flex items-center justify-center text-[#0c2016] font-bold text-xs">
                 {/* Fallback Inisial jika gambar tidak ada */}
                 {user?.name?.charAt(0).toUpperCase() || 'U'}
              </div>
              <div>
                <p className="font-bold text-[13px] text-white leading-tight truncate w-[100px]">
                  {user?.name || 'Guest User'}
                </p>
                <p className="text-[10px] text-gray-400 truncate w-[100px]">
                  {user?.email || 'No Email'}
                </p>
              </div>
            </div>
            <button 
              type="button" 
              onClick={onLogout}
              title="Logout" 
              className="text-gray-400 hover:text-white transition-colors"
            >
              <SignOut size={20} />
            </button>
          </div>
        </div>
      </div>

      {/* MAIN CONTENT */}
      <div className="flex-1 p-8 overflow-y-auto">
        <Outlet />
      </div>
    </div>
  );
}