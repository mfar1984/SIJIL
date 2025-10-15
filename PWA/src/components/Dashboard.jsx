import { useState, useEffect } from 'react'
import { participantAPI } from '../services/api'

const Dashboard = ({ user }) => {
  const [events, setEvents] = useState([])
  const [certificates, setCertificates] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchUserData()
  }, [])

  const fetchUserData = async () => {
    try {
      const eventsResponse = await participantAPI.getEvents()
      setEvents(eventsResponse.data.events || [])

      const certificatesResponse = await participantAPI.getCertificates()
      setCertificates(certificatesResponse.data.certificates || [])

    } catch (error) {
      console.error('Error fetching user data:', error)
    } finally {
      setLoading(false)
    }
  }

  const handleQRScan = () => {
    alert('QR Scanner will be implemented soon!')
  }

  const handleDownloadCertificate = async (certificateId) => {
    try {
      const response = await participantAPI.downloadCertificate(certificateId)
      const url = window.URL.createObjectURL(new Blob([response.data]))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', `certificate-${certificateId}.pdf`)
      document.body.appendChild(link)
      link.click()
      link.remove()
    } catch (error) {
      console.error('Error downloading certificate:', error)
      alert('Failed to download certificate. Please try again.')
    }
  }

  if (loading) {
    return (
      <div className="dashboard">
        <div className="loading">
          <div style={{ 
            width: '48px', 
            height: '48px', 
            border: '4px solid var(--gray-200)', 
            borderTop: '4px solid var(--primary-500)',
            borderRadius: '50%',
            animation: 'spin 1s linear infinite',
            margin: '0 auto var(--space-4)'
          }}></div>
          Loading your dashboard...
        </div>
      </div>
    )
  }

  return (
    <div className="dashboard">
      <div className="dashboard-header">
        <div style={{ 
          width: '80px', 
          height: '80px', 
          background: 'linear-gradient(135deg, var(--primary-500), var(--secondary-500))',
          borderRadius: 'var(--radius-3xl)',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          margin: '0 auto var(--space-4)',
          boxShadow: 'var(--shadow-xl)'
        }}>
          <span style={{ fontSize: '40px' }}>👋</span>
        </div>
        <h1>Welcome, {user?.name || 'Participant'}!</h1>
        <p>Manage your events and access your certificates</p>
      </div>

      <div className="dashboard-grid">
        {/* Quick Actions Card */}
        <div className="dashboard-card">
          <h3>
            <span style={{ marginRight: '8px' }}>⚡</span>
            Quick Actions
          </h3>
          <div className="action-buttons">
            <button className="action-btn primary" onClick={handleQRScan}>
              <span style={{ fontSize: '18px' }}>📱</span>
              Scan QR Code
            </button>
            <button className="action-btn secondary">
              <span style={{ fontSize: '18px' }}>📋</span>
              View Profile
            </button>
          </div>
        </div>

        {/* Events Card */}
        <div className="dashboard-card">
          <h3>
            <span style={{ marginRight: '8px' }}>📅</span>
            Your Events
          </h3>
          {events.length === 0 ? (
            <div style={{ 
              textAlign: 'center', 
              padding: 'var(--space-8)', 
              color: 'var(--gray-500)',
              fontSize: 'var(--font-size-sm)'
            }}>
              <span style={{ fontSize: '48px', display: 'block', marginBottom: 'var(--space-4)' }}>📭</span>
              No events found
              <br />
              <small>You haven't registered for any events yet.</small>
            </div>
          ) : (
            <div className="events-list">
              {events.map((event) => (
                <div key={event.id} className="event-item">
                  <h4>{event.title}</h4>
                  <p>{event.description}</p>
                  <p>
                    <strong>Date:</strong> {new Date(event.date).toLocaleDateString()}
                  </p>
                  <p>
                    <strong>Location:</strong> {event.location}
                  </p>
                  <span className={`status ${event.status}`}>
                    {event.status === 'attended' ? '✅ Attended' : '📝 Registered'}
                  </span>
                </div>
              ))}
            </div>
          )}
        </div>

        {/* Certificates Card */}
        <div className="dashboard-card">
          <h3>
            <span style={{ marginRight: '8px' }}>🏆</span>
            Your Certificates
          </h3>
          {certificates.length === 0 ? (
            <div style={{ 
              textAlign: 'center', 
              padding: 'var(--space-8)', 
              color: 'var(--gray-500)',
              fontSize: 'var(--font-size-sm)'
            }}>
              <span style={{ fontSize: '48px', display: 'block', marginBottom: 'var(--space-4)' }}>📜</span>
              No certificates yet
              <br />
              <small>Complete events to earn certificates.</small>
            </div>
          ) : (
            <div className="certificates-list">
              {certificates.map((certificate) => (
                <div key={certificate.id} className="certificate-item">
                  <h4>{certificate.title}</h4>
                  <p>{certificate.description}</p>
                  <p>
                    <strong>Event:</strong> {certificate.event_name}
                  </p>
                  <p>
                    <strong>Issued:</strong> {new Date(certificate.issued_date).toLocaleDateString()}
                  </p>
                  <button
                    className="download-btn"
                    onClick={() => handleDownloadCertificate(certificate.id)}
                  >
                    <span style={{ fontSize: '14px' }}>⬇️</span>
                    Download Certificate
                  </button>
                </div>
              ))}
            </div>
          )}
        </div>

        {/* Statistics Card */}
        <div className="dashboard-card">
          <h3>
            <span style={{ marginRight: '8px' }}>📊</span>
            Statistics
          </h3>
          <div style={{ 
            display: 'grid', 
            gridTemplateColumns: 'repeat(auto-fit, minmax(120px, 1fr))', 
            gap: 'var(--space-4)',
            marginTop: 'var(--space-4)'
          }}>
            <div style={{ 
              textAlign: 'center', 
              padding: 'var(--space-4)', 
              background: 'var(--primary-50)', 
              borderRadius: 'var(--radius-xl)',
              border: '1px solid var(--primary-200)'
            }}>
              <div style={{ fontSize: '24px', marginBottom: 'var(--space-2)' }}>📅</div>
              <div style={{ fontSize: 'var(--font-size-2xl)', fontWeight: '700', color: 'var(--primary-700)' }}>
                {events.length}
              </div>
              <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--gray-600)', textTransform: 'uppercase', letterSpacing: '0.05em' }}>
                Events
              </div>
            </div>
            <div style={{ 
              textAlign: 'center', 
              padding: 'var(--space-4)', 
              background: 'var(--success-50)', 
              borderRadius: 'var(--radius-xl)',
              border: '1px solid var(--success-200)'
            }}>
              <div style={{ fontSize: '24px', marginBottom: 'var(--space-2)' }}>🏆</div>
              <div style={{ fontSize: 'var(--font-size-2xl)', fontWeight: '700', color: 'var(--success-700)' }}>
                {certificates.length}
              </div>
              <div style={{ fontSize: 'var(--font-size-xs)', color: 'var(--gray-600)', textTransform: 'uppercase', letterSpacing: '0.05em' }}>
                Certificates
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default Dashboard 