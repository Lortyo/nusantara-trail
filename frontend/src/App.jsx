import { useEffect } from 'react';
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import DashboardLayout from './components/layout/DashboardLayout';
import OrganizerLayout from './components/layout/OrganizerLayout';
import PublicLayout from './components/layout/PublicLayout';
import RequireAuth from './components/RequireAuth';
import Toaster from './components/ui/Toaster';
import ComingSoon from './pages/ComingSoon';
import Login from './pages/Login';
import Register from './pages/Register';
import { useAuthStore } from './stores/authStore';
import EntryList from './pages/EntryList';
import EventDetail from './pages/EventDetail';
import EventsList from './pages/EventsList';
import Results from './pages/Results';
import BibTransfer from './pages/dashboard/BibTransfer';
import MyRace from './pages/dashboard/MyRace';
import Profile from './pages/dashboard/Profile';
import RaceKits from './pages/dashboard/RaceKits';
import UpgradeDowngrade from './pages/dashboard/UpgradeDowngrade';
import Payment from './pages/Payment';

export default function App() {
  const fetchUser = useAuthStore((s) => s.fetchUser);

  useEffect(() => {
    fetchUser();
  }, [fetchUser]);

  return (
    <BrowserRouter>
      <Toaster />
      <Routes>
        <Route path="/login" element={<Login />} />
        <Route path="/register" element={<Register />} />

        {/* Halaman publik */}
        <Route element={<PublicLayout />}>
          <Route path="/" element={<EventsList />} />
          <Route path="/events/:id" element={<EventDetail />} />`
          <Route path="/events/:id/entry-list" element={<EntryList />} />
          <Route path="/events/:id/results" element={<Results />} />`

          <Route element={<RequireAuth roles={['user']} />}>
            <Route path="/payment/:registrationId" element={<Payment />} />
          </Route>
        </Route>

        {/* Portal peserta */}
        <Route element={<RequireAuth roles={['user']} />}>
          <Route path="/dashboard" element={<DashboardLayout />}>
            <Route index element={<Navigate to="my-race" replace />} />
            <Route path="profile" element={<Profile />} />
            <Route path="my-race" element={<MyRace />} />
            <Route path="race-kits" element={<RaceKits />} />
            <Route path="bib-transfer" element={<BibTransfer />} />
            <Route path="upgrade-downgrade" element={<UpgradeDowngrade />} />
          </Route>
        </Route>

        {/* Panel race organizer */}
        <Route element={<RequireAuth roles={['admin']} />}>
          <Route path="/organizer" element={<OrganizerLayout />}>
            <Route index element={<Navigate to="dashboard" replace />} />
            <Route path="dashboard" element={<ComingSoon title="Dashboard" />} />
            <Route path="events" element={<ComingSoon title="Events" />} />
            <Route path="participants" element={<ComingSoon title="Participants" />} />
            <Route path="checkin" element={<ComingSoon title="Check-in" />} />
            <Route path="results" element={<ComingSoon title="Results" />} />
          </Route>
        </Route>

        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  );
}