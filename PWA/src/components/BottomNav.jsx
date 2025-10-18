import { NavLink } from 'react-router-dom'

const BottomNav = () => {
  const navItems = [
    { path: '/home', icon: 'home', label: 'Home' },
    { path: '/events', icon: 'event', label: 'Events' },
    { path: '/scan', icon: 'qr_code_scanner', label: 'Scanner' },
    { path: '/certificates', icon: 'workspace_premium', label: 'Certs' },
    { path: '/settings', icon: 'settings', label: 'Settings' },
  ]

  return (
    <nav className="bottom-nav">
      {navItems.map((item) => (
        <NavLink
          key={item.path}
          to={item.path}
          className={({ isActive }) => 
            `bottom-nav-item ${isActive ? 'active' : ''}`
          }
        >
          <span className="material-icons">{item.icon}</span>
          <span className="nav-label">{item.label}</span>
        </NavLink>
      ))}
    </nav>
  )
}

export default BottomNav

