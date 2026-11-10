import SidebarLayout from './SidebarLayout';

const links = [
  { to: '/organizer/dashboard', label: 'Dashboard' },
  { to: '/organizer/events', label: 'Events' },
  { to: '/organizer/participants', label: 'Participants' },
  { to: '/organizer/checkin', label: 'Check-in' },
  { to: '/organizer/results', label: 'Results' },
];

export default function OrganizerLayout() {
  return <SidebarLayout title="Race organizer" links={links} />;
}