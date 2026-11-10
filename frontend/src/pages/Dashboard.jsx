import { useNavigate } from 'react-router-dom';
import { useAuthStore } from '../stores/authStore';

export default function Dashboard() {
  const navigate = useNavigate();
  const { user, logout } = useAuthStore((state) => ({
    user: state.user,
    logout: state.logout,
  }));

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-lg w-full bg-white p-8 rounded-lg shadow-lg">
        <h2 className="text-2xl font-bold mb-4 text-gray-800">Dashboard</h2>
        {user ? (
          <div className="space-y-2 mb-6">
            <p className="text-gray-700">
              <span className="font-medium">Name:</span> {user.name ?? user.full_name ?? 'N/A'}
            </p>
            <p className="text-gray-700">
              <span className="font-medium">Email:</span> {user.email}
            </p>
          </div>
        ) : (
          <p className="text-gray-600 mb-4">Loading user data...</p>
        )}
        <button
          onClick={handleLogout}
          className="w-full py-2 px-4 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-md"
        >
          Logout
        </button>
      </div>
    </div>
  );
}