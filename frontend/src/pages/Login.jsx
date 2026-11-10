import { useState } from 'react';
import { Link, Navigate, useLocation } from 'react-router-dom';
import { getErrorMessage } from '../api/client';
import { useAuthStore } from '../stores/authStore';
import { toast } from '../stores/toastStore';
import backgroundImg from '../assets/bg-login.png'

export default function Login() {
  const location = useLocation();
  const login = useAuthStore((s) => s.login);
  const isLoading = useAuthStore((s) => s.isLoading);
  const isAuthenticated = useAuthStore((s) => s.isAuthenticated);
  const user = useAuthStore((s) => s.user);
  
  // Menambahkan state 'remember' untuk checkbox
  const [form, setForm] = useState({ email: '', password: '', remember: false });
  const [error, setError] = useState('');

  if (isAuthenticated) {
    const from = location.state?.from?.pathname;
    return <Navigate to={from || (user?.role === 'admin' ? '/organizer/dashboard' : '/')} replace />;
  }

  const onChange = (e) => {
    const { name, value, type, checked } = e.target;
    setForm((f) => ({ ...f, [name]: type === 'checkbox' ? checked : value }));
  };

  const onSubmit = async (e) => {
    e.preventDefault();
    setError('');
    try {
      const res = await login(form);
      toast.success(`Welcome back, ${res.data.user.name}!`);
    } catch (err) {
      setError(getErrorMessage(err, 'Login failed. Please check your email and password.'));
    }
  };

  return (
    <div className="flex h-screen w-full overflow-hidden font-sans antialiased bg-[#f4f5f3]">
      
      {/* SISI KIRI */}
      <div className="hidden lg:block lg:w-[55%] relative h-full">
        <img 
          src={backgroundImg} 
          alt="Runners in forest" 
          className="absolute inset-0 w-full h-full object-cover object-center"
        />
        <div className="absolute inset-0 bg-black/10"></div>
      </div>

      {/* SISI KANAN */}
      <div className="w-full lg:w-[45%] h-full flex items-center justify-center p-6 bg-[#f4f5f3] relative">
        
        {/* Kotak Form Putih */}
        <div className="bg-white w-full max-w-[400px] p-7 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 z-10">
          
          <h2 className="text-[26px] font-black text-gray-900 mb-1.5 tracking-tight">Welcome back</h2>
          <p className="text-[13px] text-gray-500 mb-6 leading-relaxed">
            Sign in to access the participant portal and prepare for your race.
          </p>

          <form onSubmit={onSubmit}>

            {/* Tampilkan Error jika Login Gagal */}
            {error && (
              <div className="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 text-xs rounded-xl">
                {error}
              </div>
            )}
            
            <div className="mb-4">
              <label htmlFor="email" className="block text-[11px] font-bold text-gray-900 mb-1.5">
                Participant email
              </label>
              <input 
                type="email" 
                id="email" 
                name="email" 
                value={form.email} 
                onChange={onChange} 
                required
                autoComplete="email"
                className="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016] transition-colors" 
              />
            </div>

            <div className="mb-4">
              <label htmlFor="password" className="block text-[11px] font-bold text-gray-900 mb-1.5">
                Password
              </label>
              <input 
                type="password" 
                id="password" 
                name="password" 
                value={form.password} 
                onChange={onChange} 
                required
                autoComplete="current-password"
                className="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-[13px] focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016] transition-colors" 
              />
            </div>

            <div className="flex items-center justify-between mb-6">
              <label className="flex items-center gap-2 cursor-pointer group">
                <input 
                  type="checkbox" 
                  name="remember" 
                  checked={form.remember}
                  onChange={onChange}
                  className="w-4 h-4 rounded border-gray-300 text-[#0c2016] focus:ring-[#0c2016] cursor-pointer" 
                />
                <span className="text-[11px] text-gray-500 group-hover:text-gray-900 transition-colors">
                  Remember me
                </span>
              </label>
              <Link to="/forgot-password" className="text-[11px] font-bold text-gray-900 hover:underline">
                Forgot password?
              </Link>
            </div>

            <button 
              type="submit" 
              disabled={isLoading}
              className="w-full bg-[#0c2016] hover:bg-[#183626] disabled:bg-gray-400 disabled:cursor-not-allowed text-white py-3 rounded-xl font-bold text-[13px] transition-colors flex items-center justify-center gap-2 shadow-sm"
            >
              <span>{isLoading ? 'Signing in...' : 'Sign in to portal'}</span>
              {!isLoading && <i className="ph ph-arrow-right font-bold text-base"></i>}
            </button>

          </form>

          <p className="text-[11px] text-center text-gray-500 mt-5">
            <Link to="/register" className="text-gray-900 font-bold hover:underline">
              No account yet? 
            </Link>{' '}
            Activate via your registration email.
          </p>

        </div>
      </div>
    </div>
  );
}