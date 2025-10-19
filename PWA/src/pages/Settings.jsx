import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { authAPI } from '../services/api'
import api from '../services/api'

const Settings = ({ user, onLogout }) => {
  const navigate = useNavigate()
  const [showLegalModal, setShowLegalModal] = useState(false)
  const [legalContent, setLegalContent] = useState({ title: '', html: '' })
  const [loadingLegal, setLoadingLegal] = useState(false)
  const [showAboutModal, setShowAboutModal] = useState(false)

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

  const openLegalModal = async (type) => {
    setShowLegalModal(true)
    setLoadingLegal(true)
    setLegalContent({ title: 'Loading...', html: '' })

    try {
      const response = await api.get(`/api/legal/${type}`)
      if (response.data.success) {
        setLegalContent({
          title: response.data.title,
          html: response.data.html
        })
      }
    } catch (error) {
      setLegalContent({
        title: 'Error',
        html: '<p class="text-red-600">Failed to load content. Please try again.</p>'
      })
    } finally {
      setLoadingLegal(false)
    }
  }

  const closeLegalModal = () => {
    setShowLegalModal(false)
    setLegalContent({ title: '', html: '' })
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
          <button className="settings-item-ios" onClick={() => setShowAboutModal(true)}>
            <div className="item-icon-wrapper gray">
              <span className="material-icons">info</span>
            </div>
            <span className="item-text-ios">About</span>
            <span className="material-icons item-arrow-ios">chevron_right</span>
          </button>
          
          <div className="item-divider" />
          
          <button className="settings-item-ios" onClick={() => alert('Help & Support\n\nFor assistance, please contact your event organizer or email support@e-certificate.com.my')}>
            <div className="item-icon-wrapper orange">
              <span className="material-icons">help</span>
            </div>
            <span className="item-text-ios">Help & Support</span>
            <span className="material-icons item-arrow-ios">chevron_right</span>
          </button>
        </div>
      </div>

      {/* Legal */}
      <div className="settings-section">
        <h3 className="section-heading-ios">Legal</h3>
        <div className="settings-list-ios">
          <button className="settings-item-ios" onClick={() => openLegalModal('disclaimer')}>
            <div className="item-icon-wrapper blue">
              <span className="material-icons">description</span>
            </div>
            <span className="item-text-ios">Disclaimer</span>
            <span className="material-icons item-arrow-ios">chevron_right</span>
          </button>
          
          <div className="item-divider" />
          
          <button className="settings-item-ios" onClick={() => openLegalModal('privacy')}>
            <div className="item-icon-wrapper teal">
              <span className="material-icons">shield</span>
            </div>
            <span className="item-text-ios">Privacy Policy</span>
            <span className="material-icons item-arrow-ios">chevron_right</span>
          </button>
          
          <div className="item-divider" />
          
          <button className="settings-item-ios" onClick={() => openLegalModal('terms')}>
            <div className="item-icon-wrapper purple">
              <span className="material-icons">gavel</span>
            </div>
            <span className="item-text-ios">Terms & Conditions</span>
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
        <p>E-Certificate PWA v1.0.2</p>
        <p>© {new Date().getFullYear()} E-Certificate System</p>
      </div>

      {/* About Modal */}
      {showAboutModal && (
        <div className="modal-overlay" onClick={() => setShowAboutModal(false)}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()} style={{ maxWidth: '500px' }}>
            <div className="modal-header">
              <h3 className="modal-title">About E-Certificate</h3>
              <button onClick={() => setShowAboutModal(false)} style={{ background: 'none', border: 'none', cursor: 'pointer', padding: '8px' }}>
                <span className="material-icons" style={{ color: 'var(--gray-600)' }}>close</span>
              </button>
            </div>
            <div className="modal-body" style={{ padding: '24px', textAlign: 'center' }}>
              <div style={{ marginBottom: '20px' }}>
                <img src="/logo.png" alt="E-Certificate" style={{ width: '100px', height: '100px', margin: '0 auto 16px', borderRadius: '12px', boxShadow: '0 4px 6px rgba(0,0,0,0.1)' }} />
              </div>
              <h4 style={{ fontSize: '20px', fontWeight: '700', color: 'var(--gray-900)', marginBottom: '8px' }}>E-Certificate</h4>
              <p style={{ fontSize: '14px', color: 'var(--gray-600)', marginBottom: '24px' }}>Progressive Web Application</p>
              
              <div style={{ background: 'var(--gray-50)', padding: '16px', borderRadius: '8px', marginBottom: '20px', textAlign: 'left' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '8px', fontSize: '12px' }}>
                  <span style={{ color: 'var(--gray-600)' }}>Version</span>
                  <span style={{ fontWeight: '600', color: 'var(--gray-900)' }}>1.0.2</span>
                </div>
                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '8px', fontSize: '12px' }}>
                  <span style={{ color: 'var(--gray-600)' }}>Build Date</span>
                  <span style={{ fontWeight: '600', color: 'var(--gray-900)' }}>October 2025</span>
                </div>
                <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '12px' }}>
                  <span style={{ color: 'var(--gray-600)' }}>Platform</span>
                  <span style={{ fontWeight: '600', color: 'var(--gray-900)' }}>Mobile Web (PWA)</span>
                </div>
              </div>
              
              <div style={{ fontSize: '12px', color: 'var(--gray-700)', lineHeight: '1.6', marginBottom: '20px', textAlign: 'left' }}>
                <p style={{ marginBottom: '12px' }}>E-Certificate is a comprehensive digital platform for event management, attendance tracking, and certificate issuance.</p>
                
                <p style={{ marginBottom: '8px', fontWeight: '600', color: 'var(--gray-900)' }}>Features:</p>
                <ul style={{ listStyle: 'disc', paddingLeft: '20px', marginBottom: '12px' }}>
                  <li>Event registration and management</li>
                  <li>QR code attendance scanning with GPS verification</li>
                  <li>Digital certificate generation and download</li>
                  <li>Attendance history tracking</li>
                  <li>Offline-capable Progressive Web App</li>
                  <li>Push notifications for event updates</li>
                </ul>
              </div>
              
              <div style={{ fontSize: '11px', color: 'var(--gray-500)', paddingTop: '16px', borderTop: '1px solid var(--gray-200)' }}>
                <p style={{ marginBottom: '4px' }}>© {new Date().getFullYear()} E-Certificate System</p>
                <p>All rights reserved.</p>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Legal Modal */}
      {showLegalModal && (
        <div className="modal-overlay" onClick={closeLegalModal}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()} style={{ maxWidth: '800px', maxHeight: '85vh' }}>
            <div className="modal-header" style={{ position: 'sticky', top: 0, background: 'white', zIndex: 10, borderBottom: '1px solid var(--gray-200)' }}>
              <h3 className="modal-title">{legalContent.title}</h3>
              <button onClick={closeLegalModal} style={{ background: 'none', border: 'none', cursor: 'pointer', padding: '8px' }}>
                <span className="material-icons" style={{ color: 'var(--gray-600)' }}>close</span>
              </button>
            </div>
            <div className="modal-body" style={{ padding: '24px', overflowY: 'auto', maxHeight: 'calc(85vh - 70px)' }}>
              {loadingLegal ? (
                <div style={{ display: 'flex', justifyContent: 'center', padding: '40px' }}>
                  <div className="loading-spinner"></div>
                </div>
              ) : (
                <div dangerouslySetInnerHTML={{ __html: legalContent.html }} style={{ fontSize: '12px', lineHeight: '1.7' }} />
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default Settings

