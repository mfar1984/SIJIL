import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { authAPI } from '../services/api'

const Header = ({ user, onLogout }) => {
  const navigate = useNavigate()
  const [showDropdown, setShowDropdown] = useState(false)
  const notificationCount = 0 // Can be dynamic from API

  const handleLogout = async () => {
    if (!window.confirm('Are you sure you want to logout?')) return

    try {
      // Call backend API to delete token from database
      await authAPI.logout()
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      // Always clear local storage and redirect
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      onLogout()
      navigate('/login')
    }
  }

  const getUserInitials = () => {
    if (!user?.name) return 'U'
    return user.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
  }

  return (
    <header className="app-header">
      <div className="header-content">
        {/* Left - Logo */}
        <div className="header-left" onClick={() => navigate('/home')}>
          <img src="/logo.png" alt="SIJIL" className="app-logo" />
          <h1 className="app-title">SIJIL</h1>
        </div>

        {/* Right - Notification & User */}
        {user && (
          <div className="header-right">
            {/* Notification Bell */}
            <button className="notification-btn-minimal" onClick={() => alert('Notifications coming soon')}>
              <span className="material-icons">notifications</span>
              {notificationCount > 0 && (
                <span className="notification-badge-minimal">{notificationCount}</span>
              )}
            </button>

            {/* User Menu */}
            <div className="user-menu-wrapper">
              <button 
                className="user-avatar-btn"
                onClick={() => setShowDropdown(!showDropdown)}
              >
                <div className="user-avatar-minimal">
                  <span className="material-icons">account_circle</span>
                </div>
              </button>

              {/* Dropdown Menu */}
              {showDropdown && (
                <>
                  <div className="menu-backdrop" onClick={() => setShowDropdown(false)} />
                  <div className="user-dropdown-minimal">
                    {/* Dropdown Header */}
                    <div className="dropdown-header-minimal">
                      <div className="dropdown-avatar-large">
                        <span className="material-icons">account_circle</span>
                      </div>
                      <div className="dropdown-user-info">
                        <div className="dropdown-name">{user.name}</div>
                        <div className="dropdown-email">{user.email}</div>
                      </div>
                    </div>

                    <div className="dropdown-divider" />

                    {/* Menu Items */}
                    <button 
                      className="dropdown-item-minimal"
                      onClick={() => {
                        setShowDropdown(false)
                        navigate('/settings')
                      }}
                    >
                      <span className="material-icons dropdown-icon-minimal">person</span>
                      <span>My Profile</span>
                    </button>

                    <button 
                      className="dropdown-item-minimal"
                      onClick={() => {
                        setShowDropdown(false)
                        navigate('/change-password')
                      }}
                    >
                      <span className="material-icons dropdown-icon-minimal">lock</span>
                      <span>Change Password</span>
                    </button>

                    <button 
                      className="dropdown-item-minimal"
                      onClick={() => {
                        setShowDropdown(false)
                        navigate('/attendance-history')
                      }}
                    >
                      <span className="material-icons dropdown-icon-minimal">history</span>
                      <span>Attendance History</span>
                    </button>

                    <div className="dropdown-divider" />

                    <button 
                      className="dropdown-item-minimal logout"
                      onClick={() => {
                        setShowDropdown(false)
                        handleLogout()
                      }}
                    >
                      <span className="material-icons dropdown-icon-minimal">logout</span>
                      <span>Logout</span>
                    </button>
                  </div>
                </>
              )}
            </div>
          </div>
        )}
      </div>
    </header>
  )
}

export default Header 