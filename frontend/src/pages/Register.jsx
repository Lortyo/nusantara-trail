import { useState } from 'react';
import { Link, Navigate } from 'react-router-dom';
import { getErrorMessage } from '../api/client';
import { COUNTRIES } from '../lib/countries';
import { useAuthStore } from '../stores/authStore';
import { toast } from '../stores/toastStore';
import backgroundImg from '../assets/bg-login.png'


export default function Register() {
  const register = useAuthStore((s) => s.register);
  const isLoading = useAuthStore((s) => s.isLoading);
  const isAuthenticated = useAuthStore((s) => s.isAuthenticated);
  const user = useAuthStore((s) => s.user);

  const [form, setForm] = useState({
    first_name: '',
    last_name: '',
    email: '',
    phone_code: '+62',
    phone: '',
    nationality: '',
    date_of_birth: '',
    gender: '',
    password: '',
    password_confirmation: '',
  });

  const [error, setError] = useState('');
  
  // State untuk toggle visibilitas password
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);

  if (isAuthenticated) {
    return <Navigate to={user?.role === 'admin' ? '/organizer/dashboard' : '/dashboard/profile'} replace />;
  }

  const onChange = (e) => setForm((f) => ({ ...f, [e.target.name]: e.target.value }));

  const onSubmit = async (e) => {
    e.preventDefault();
    setError('');

    if (form.password !== form.password_confirmation) {
      setError('Password confirmation does not match.');
      return;
    }

    try {
      // Menyesuaikan dengan format API sebelumnya jika diperlukan (misal: menggabungkan first_name & last_name menjadi name)
      const payload = {
        ...form,
        name: `${form.first_name} ${form.last_name}`.trim(),
      };
      
      await register(payload);
      toast.success('Account created. Please complete your profile before registering for a race.');
    } catch (err) {
      setError(getErrorMessage(err, 'Registration failed. Please try again.'));
    }
  };

  return (
    <div className="flex h-screen w-full overflow-hidden font-sans antialiased bg-[#f4f5f3]">
      
      {/* SISI KIRI */}
      <div className="hidden lg:block lg:w-[45%] xl:w-[50%] relative h-full">
        <img 
          src={backgroundImg}
          alt="Runners in forest"
          className="absolute inset-0 w-full h-full object-cover object-center"
        />
        <div className="absolute inset-0 bg-black/40"></div>

        <div className="absolute bottom-12 left-12 right-12 z-10 text-white">
          <p className="text-[10px] font-bold text-gray-300 uppercase tracking-widest mb-3">Participant Portal</p>
          <h1 className="text-4xl font-black mb-4 leading-tight tracking-tight text-white">Your first step to the starting line.</h1>
          <p className="text-sm text-gray-200 leading-relaxed max-w-md">Create an account to discover races, manage registrations, and get ready for your adventure.</p>
        </div>
      </div>

      {/* SISI KANAN */}
      <div className="w-full lg:w-[55%] xl:w-[50%] h-full p-8 lg:p-12 bg-[#f4f5f3] overflow-y-auto">

        <div className="max-w-[540px] mx-auto bg-white p-10 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 my-8">

          <h2 className="text-[28px] font-black text-gray-900 mb-2 tracking-tight">Create your account</h2>
          <p className="text-sm text-gray-500 mb-8 leading-relaxed">Your adventure starts here. Enter your details to join and access the participant portal.</p>

          <form onSubmit={onSubmit}>
            
            {/* Alert Error */}
            {error && (
              <div className="mb-5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-[12px] px-4 py-3">
                <ul className="list-disc pl-4 space-y-0.5">
                  <li>{error}</li>
                </ul>
              </div>
            )}

            {/* First name & Last name */}
            <div className="grid grid-cols-2 gap-4 mb-4">
              <div>
                <label className="block text-[11px] font-bold text-gray-900 mb-1.5">First name</label>
                <input 
                  type="text" 
                  name="first_name" 
                  value={form.first_name}
                  onChange={onChange}
                  required 
                  placeholder="First name" 
                  className="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016] transition-colors placeholder:text-gray-400" 
                />
              </div>
              <div>
                <label className="block text-[11px] font-bold text-gray-900 mb-1.5">Last name</label>
                <input 
                  type="text" 
                  name="last_name" 
                  value={form.last_name}
                  onChange={onChange}
                  required 
                  placeholder="Last name" 
                  className="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016] transition-colors placeholder:text-gray-400" 
                />
              </div>
            </div>

            {/* Email */}
            <div className="mb-4">
              <label className="block text-[11px] font-bold text-gray-900 mb-1.5">Email</label>
              <input 
                type="email" 
                name="email" 
                value={form.email}
                onChange={onChange}
                required 
                placeholder="name@email.com" 
                className="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016] transition-colors placeholder:text-gray-400" 
              />
            </div>

            {/* Phone number */}
            <div className="mb-4">
              <label className="block text-[11px] font-bold text-gray-900 mb-1.5">Phone number</label>
              <div className="flex">
                <select 
                  name="phone_code" 
                  value={form.phone_code}
                  onChange={onChange}
                  className="border border-gray-200 rounded-l-xl px-3 py-3 text-sm bg-gray-50 focus:outline-none focus:border-[#0c2016] border-r-0"
                >
                  <option value="+62">+62</option>
                  {/* Tambahkan opsi kode negara lain jika diperlukan */}
                </select>
                <input 
                  type="tel" 
                  name="phone" 
                  value={form.phone}
                  onChange={onChange}
                  required 
                  placeholder="812 3456 7890" 
                  className="w-full border border-gray-200 rounded-r-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016] transition-colors placeholder:text-gray-400" 
                />
              </div>
            </div>

            {/* Nationality & Date of birth */}
            <div className="grid grid-cols-2 gap-4 mb-4">
              <div>
                <label className="block text-[11px] font-bold text-gray-900 mb-1.5">Nationality</label>
                <div className="relative">
                  <select 
                    name="nationality" 
                    value={form.nationality}
                    onChange={onChange}
                    required 
                    className="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0c2016] appearance-none text-gray-900 bg-white"
                  >
                    <option value="" disabled>Select nationality</option>
                    {COUNTRIES.map(([code, name]) => (
                      <option key={code} value={code.toLowerCase()}>{name}</option>
                    ))}
                  </select>
                  <i className="ph ph-caret-down absolute right-4 top-3.5 text-gray-400 pointer-events-none"></i>
                </div>
              </div>
              <div>
                <label className="block text-[11px] font-bold text-gray-900 mb-1.5">Date of birth</label>
                <input 
                  type="date" 
                  name="date_of_birth" 
                  value={form.date_of_birth}
                  onChange={onChange}
                  max={new Date().toISOString().split('T')[0]} 
                  required 
                  className="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016] transition-colors text-gray-900" 
                />
              </div>
            </div>

            {/* Gender */}
            <div className="mb-4">
              <label className="block text-[11px] font-bold text-gray-900 mb-1.5">Gender</label>
              <div className="relative">
                <select 
                  name="gender" 
                  value={form.gender}
                  onChange={onChange}
                  required 
                  className="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0c2016] appearance-none text-gray-900 bg-white"
                >
                  <option value="" disabled>Select gender</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                </select>
                <i className="ph ph-caret-down absolute right-4 top-3.5 text-gray-400 pointer-events-none"></i>
              </div>
            </div>

            {/* Passwords */}
            <div className="grid grid-cols-2 gap-4 mb-2">
              <div>
                <label className="block text-[11px] font-bold text-gray-900 mb-1.5">Password</label>
                <div className="relative">
                  <input 
                    type={showPassword ? "text" : "password"} 
                    name="password" 
                    value={form.password}
                    onChange={onChange}
                    required 
                    minLength={8}
                    placeholder="Create a password" 
                    className="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016] transition-colors placeholder:text-gray-400 pr-12" 
                  />
                  <button 
                    type="button" 
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none flex items-center justify-center h-full"
                  >
                    {showPassword ? (
                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256"><path d="M247.31,124.76c-.35-.79-8.82-19.58-27.65-38.41C194.57,61.26,162.88,48,128,48S61.43,61.26,36.34,86.35C17.51,105.18,9,124,8.69,124.76a8,8,0,0,0,0,6.48c.35.79,8.82,19.58,27.65,38.41C61.43,194.74,93.12,208,128,208s66.57-13.26,91.66-38.35c18.83-18.83,27.3-37.62,27.65-38.41A8,8,0,0,0,247.31,124.76ZM128,192c-30.78,0-57.67-11.19-79.93-33.25A133.47,133.47,0,0,1,25,128,133.33,133.33,0,0,1,48.07,97.25C70.33,75.19,97.22,64,128,64s57.67,11.19,79.93,33.25A133.46,133.46,0,0,1,231.05,128c-7.21,13.46-38.62,64-103.05,64Zm0-112a48,48,0,1,0,48,48A48.05,48.05,0,0,0,128,80Zm0,80a32,32,0,1,1,32-32A32,32,0,0,1,128,160Z"></path></svg>
                    ) : (
                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256"><path d="M53.92,34.62A8,8,0,1,0,42.08,45.38l38.21,41.4C55.43,99.25,28.87,116.89,8.69,124.76a8,8,0,0,0,0,6.48c.35.79,8.82,19.58,27.65,38.41C61.43,194.74,93.12,208,128,208a127.13,127.13,0,0,0,48.8-9.68l37.28,40.38a8,8,0,1,0,11.84-10.76ZM128,192c-30.78,0-57.67-11.19-79.93-33.25A133.47,133.47,0,0,1,25,128c4.69-8.78,19.56-33.64,47.47-53.11l25.1,27.19A48,48,0,0,0,157.91,170l-12.72-13.78A32,32,0,0,1,96,112.59L82.17,97.6C80.74,107,89,122.25,128,122.25c.34,0,.68,0,1,0l-10.51-11.39A32.18,32.18,0,0,1,97.6,128a32,32,0,0,0,46.54,28.48l12.71,13.78A47.93,47.93,0,0,1,128,176Zm58.64-58.46a8,8,0,0,0,9.93-5.32c5.69-18.4,2.37-33-2.18-42-18.83-18.83-45.72-30-76.4-30a125.75,125.75,0,0,0-30.82,3.87,8,8,0,1,0,3.67,15.58A110.15,110.15,0,0,1,118,64c28.27,0,52.88,9.88,72.93,29.93C206.09,109.11,219.78,121,231.05,128a132.81,132.81,0,0,1-19.16,25.68A8,8,0,0,0,218.64,166h0a8,8,0,0,0,1.52-11.23Z"></path></svg>
                    )}
                  </button>
                </div>
              </div>
              <div>
                <label className="block text-[11px] font-bold text-gray-900 mb-1.5">Confirm password</label>
                <div className="relative">
                  <input 
                    type={showConfirmPassword ? "text" : "password"} 
                    name="password_confirmation" 
                    value={form.password_confirmation}
                    onChange={onChange}
                    required 
                    minLength={8}
                    placeholder="Re-enter password" 
                    className="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#0c2016] focus:ring-1 focus:ring-[#0c2016] transition-colors placeholder:text-gray-400 pr-12" 
                  />
                  <button 
                    type="button" 
                    onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                    className="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none flex items-center justify-center h-full"
                  >
                    {showConfirmPassword ? (
                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256"><path d="M247.31,124.76c-.35-.79-8.82-19.58-27.65-38.41C194.57,61.26,162.88,48,128,48S61.43,61.26,36.34,86.35C17.51,105.18,9,124,8.69,124.76a8,8,0,0,0,0,6.48c.35.79,8.82,19.58,27.65,38.41C61.43,194.74,93.12,208,128,208s66.57-13.26,91.66-38.35c18.83-18.83,27.3-37.62,27.65-38.41A8,8,0,0,0,247.31,124.76ZM128,192c-30.78,0-57.67-11.19-79.93-33.25A133.47,133.47,0,0,1,25,128,133.33,133.33,0,0,1,48.07,97.25C70.33,75.19,97.22,64,128,64s57.67,11.19,79.93,33.25A133.46,133.46,0,0,1,231.05,128c-7.21,13.46-38.62,64-103.05,64Zm0-112a48,48,0,1,0,48,48A48.05,48.05,0,0,0,128,80Zm0,80a32,32,0,1,1,32-32A32,32,0,0,1,128,160Z"></path></svg>
                    ) : (
                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256"><path d="M53.92,34.62A8,8,0,1,0,42.08,45.38l38.21,41.4C55.43,99.25,28.87,116.89,8.69,124.76a8,8,0,0,0,0,6.48c.35.79,8.82,19.58,27.65,38.41C61.43,194.74,93.12,208,128,208a127.13,127.13,0,0,0,48.8-9.68l37.28,40.38a8,8,0,1,0,11.84-10.76ZM128,192c-30.78,0-57.67-11.19-79.93-33.25A133.47,133.47,0,0,1,25,128c4.69-8.78,19.56-33.64,47.47-53.11l25.1,27.19A48,48,0,0,0,157.91,170l-12.72-13.78A32,32,0,0,1,96,112.59L82.17,97.6C80.74,107,89,122.25,128,122.25c.34,0,.68,0,1,0l-10.51-11.39A32.18,32.18,0,0,1,97.6,128a32,32,0,0,0,46.54,28.48l12.71,13.78A47.93,47.93,0,0,1,128,176Zm58.64-58.46a8,8,0,0,0,9.93-5.32c5.69-18.4,2.37-33-2.18-42-18.83-18.83-45.72-30-76.4-30a125.75,125.75,0,0,0-30.82,3.87,8,8,0,1,0,3.67,15.58A110.15,110.15,0,0,1,118,64c28.27,0,52.88,9.88,72.93,29.93C206.09,109.11,219.78,121,231.05,128a132.81,132.81,0,0,1-19.16,25.68A8,8,0,0,0,218.64,166h0a8,8,0,0,0,1.52-11.23Z"></path></svg>
                    )}
                  </button>
                </div>
              </div>
            </div>
            <p className="text-[10px] text-gray-500 mb-8">Use at least 8 characters, including letters and numbers.</p>

            <p className="text-[11px] text-gray-500 mb-4 text-center">
              By signing up, you agree to our Terms & Conditions and Privacy Policy.
            </p>

            <button 
              type="submit" 
              disabled={isLoading}
              className="w-full bg-[#0c2016] hover:bg-[#183626] disabled:bg-gray-400 disabled:cursor-not-allowed text-white py-3.5 rounded-xl font-bold text-sm transition-colors flex items-center justify-center gap-2 shadow-sm mb-6"
            >
              <span>{isLoading ? 'Creating account...' : 'Create account'}</span>
              {!isLoading && <i className="ph ph-arrow-right font-bold text-base"></i>}
            </button>

            <p className="text-[11px] text-center text-gray-500">
              Already have an account? <Link to="/login" className="text-gray-900 font-bold hover:underline">Sign in</Link>
            </p>

          </form>
        </div>
      </div>
    </div>
  );
}