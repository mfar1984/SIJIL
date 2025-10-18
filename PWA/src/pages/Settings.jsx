import { useNavigate } from 'react-router-dom'
import { authAPI } from '../services/api'

const Settings = ({ user, onLogout }) => {
  const navigate = useNavigate()

  const handleLogout = async () => {
    if (!window.confirm('Are you sure you want to logout?')) return

    try {
      // Call backend API to delete token from database
      await authAPI.logout()
    } catch (error) {
      // Silent error handling
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
    <div className="page-settings">
      {/* Profile Card */}
      <div className="settings-section">
        <div className="profile-card-ios">
          <div className="profile-avatar-large">
            <div className="user-avatar-minimal" style={{ width: '80px', height: '80px' }}>
              <span className="material-icons avatar-icon-large" style={{ fontSize: '80px' }}>
                account_circle
              </span>
            </div>
          </div>
          <h2 className="profile-name">{user?.name || 'Participant'}</h2>
          <p className="profile-email">{user?.email || 'participant@email.com'}</p>
          <button className="edit-profile-btn-ios" onClick={() => navigate('/settings/profile')}>
            <span className="material-icons">edit</span>
            <span>Edit Profile</span>
          </button>
        </div>
      </div>

      {/* Account Settings */}
      <div className="settings-section">
        <h3 className="section-heading-ios">Account</h3>
        <div className="settings-list-ios">
          <button className="settings-item-ios" onClick={() => navigate('/settings/profile')}>
            <div className="item-icon-wrapper blue">
              <span className="material-icons">person</span>
            </div>
            <span className="item-text-ios">Personal Information</span>
            <span className="material-icons item-arrow-ios">chevron_right</span>
          </button>
          
          <div className="item-divider" />
          
          <button className="settings-item-ios" onClick={() => navigate('/change-password')}>
            <div className="item-icon-wrapper purple">
              <span className="material-icons">lock</span>
            </div>
            <span className="item-text-ios">Change Password</span>
            <span className="material-icons item-arrow-ios">chevron_right</span>
          </button>
          
          <div className="item-divider" />
          
          <button className="settings-item-ios" onClick={() => navigate('/attendance-history')}>
            <div className="item-icon-wrapper green">
              <span className="material-icons">history</span>
            </div>
            <span className="item-text-ios">Attendance History</span>
            <span className="material-icons item-arrow-ios">chevron_right</span>
          </button>
        </div>
      </div>

      {/* App Settings */}
      <div className="settings-section">
        <h3 className="section-heading-ios">App</h3>
        <div className="settings-list-ios">
          <button className="settings-item-ios" onClick={() => alert('About SIJIL - Version 1.0.0')}>
            <div className="item-icon-wrapper gray">
              <span className="material-icons">info</span>
            </div>
            <span className="item-text-ios">About SIJIL</span>
            <span className="material-icons item-arrow-ios">chevron_right</span>
          </button>
          
          <div className="item-divider" />
          
          <button className="settings-item-ios" onClick={() => alert('Help & Support - Coming soon')}>
            <div className="item-icon-wrapper orange">
              <span className="material-icons">help</span>
            </div>
            <span className="item-text-ios">Help & Support</span>
            <span className="material-icons item-arrow-ios">chevron_right</span>
          </button>
          
          <div className="item-divider" />
          
          <button className="settings-item-ios" onClick={() => alert('Privacy Policy - Coming soon')}>
            <div className="item-icon-wrapper teal">
              <span className="material-icons">shield</span>
            </div>
            <span className="item-text-ios">Privacy Policy</span>
            <span className="material-icons item-arrow-ios">chevron_right</span>
          </button>
        </div>
      </div>

      {/* Logout */}
      <div className="settings-section">
        <button className="logout-btn-ios" onClick={handleLogout}>
          <span className="material-icons">logout</span>
          <span>Logout</span>
        </button>
      </div>

      {/* App Version */}
      <div className="app-version">
        <p>SIJIL PWA v1.0.0</p>
        <p>© 2025 E-Certificate System</p>
      </div>
    </div>
  )
}

export default Settings

