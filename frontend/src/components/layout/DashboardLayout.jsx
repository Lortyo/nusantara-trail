import SidebarLayout from './SidebarLayout';

const links = [
  { to: '/', label: 'Events', end: true },
  { to: '/dashboard/profile', label: 'Profile' },
  { to: '/dashboard/my-race', label: 'My race' },
  { to: '/dashboard/race-kits', label: 'Race kits' },
  { to: '/dashboard/bib-transfer', label: 'BIB transfer' },
  { to: '/dashboard/upgrade-downgrade', label: 'Upgrade / downgrade' },
];

export default function DashboardLayout() {
  return <SidebarLayout title="Participant portal" links={links} />;
}