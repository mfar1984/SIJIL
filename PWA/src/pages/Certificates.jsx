import { useState, useEffect } from 'react'
import { participantAPI } from '../services/api'
import LoadingScreen from '../components/LoadingScreen'

const Certificates = () => {
  const [loading, setLoading] = useState(true)
  const [certificates, setCertificates] = useState([])
  const [downloading, setDownloading] = useState({})

  useEffect(() => {
    fetchCertificates()
  }, [])

  const fetchCertificates = async () => {
    try {
      const response = await participantAPI.getCertificates()
      // Backend returns { success, data: { certificates: [...] } }
      const list = response?.data?.data?.certificates || []
      setCertificates(list)
    } catch (error) {
      // Silent error handling
    } finally {
      setLoading(false)
    }
  }

  const handleDownload = async (certificate) => {
    setDownloading(prev => ({ ...prev, [certificate.id]: true }))
    try {
      const response = await participantAPI.downloadCertificate(certificate.id)
      const url = window.URL.createObjectURL(new Blob([response.data]))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', `${certificate.certificate_number}.pdf`)
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(url)
    } catch (error) {
      alert('Failed to download certificate. Please try again.')
    } finally {
      setDownloading(prev => ({ ...prev, [certificate.id]: false }))
    }
  }

  const handleShare = async (certificate) => {
    if (navigator.share) {
      try {
        await navigator.share({
          title: certificate.event_name,
          text: `My certificate for ${certificate.event_name}`,
          url: window.location.href
        })
      } catch (error) {
        // Share cancelled or failed
      }
    } else {
      alert('Sharing not supported on this device')
    }
  }

  if (loading) {
    return <LoadingScreen message="Loading certificates..." />
  }

  return (
    <div className="page-certificates">
      {/* Header */}
      <div className="page-header-certs">
        <div className="header-title-group">
          <h1 className="page-title">My Certificates</h1>
          {certificates.length > 0 && (
            <div className="cert-count-badge">
              <span className="material-icons cert-count-icon">workspace_premium</span>
              <span>{certificates.length}</span>
            </div>
          )}
        </div>
        <p className="page-subtitle">Your achievements and completed events</p>
      </div>

      {/* Certificates List */}
      <div className="certificates-list">
        {certificates.length === 0 ? (
          <div className="empty-state">
            <div className="empty-icon-wrapper">
              <span className="material-icons empty-icon-large">workspace_premium</span>
            </div>
            <p className="empty-text">No certificates yet</p>
            <p className="empty-subtext">Complete events to earn certificates</p>
          </div>
        ) : (
          certificates.map(certificate => (
            <div key={certificate.id} className="certificate-card-enhanced">
              {/* Badge Corner */}
              <div className="cert-badge-corner">
                <span className="material-icons">workspace_premium</span>
              </div>

              {/* Header */}
              <div className="cert-main-header">
                <h3 className="cert-main-title">{certificate.event_name}</h3>
              </div>

              {/* Event Info */}
              <div className="cert-event-info">
                <div className="cert-info-row">
                  <span className="material-icons cert-info-icon">event</span>
                  <div className="cert-info-content">
                    <div className="cert-info-label">Event</div>
                    <div className="cert-info-value">{certificate.event_name}</div>
                  </div>
                </div>
                <div className="cert-info-row">
                  <span className="material-icons cert-info-icon">calendar_today</span>
                  <div className="cert-info-content">
                    <div className="cert-info-label">Issued</div>
                    <div className="cert-info-value">
                      {new Date(certificate.issued_date).toLocaleDateString('en-MY', { 
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                      })}
                    </div>
                  </div>
                </div>
                <div className="cert-info-row">
                  <span className="material-icons cert-info-icon">badge</span>
                  <div className="cert-info-content">
                    <div className="cert-info-label">Certificate No.</div>
                    <div className="cert-number-mono">{certificate.certificate_number}</div>
                  </div>
                </div>
              </div>

              {/* Description */}
              {certificate.description && (
                <div className="cert-description-box">
                  <p>{certificate.description}</p>
                </div>
              )}

              {/* Actions */}
              <div className="cert-actions-enhanced">
                <button 
                  className="cert-btn-main" 
                  onClick={() => handleDownload(certificate)}
                  disabled={downloading[certificate.id]}
                >
                  {downloading[certificate.id] ? (
                    <>
                      <div className="loading-spinner" />
                      <span>Downloading...</span>
                    </>
                  ) : (
                    <>
                      <span className="material-icons">download</span>
                      <span>Download PDF</span>
                    </>
                  )}
                </button>
                <button 
                  className="cert-btn-secondary" 
                  onClick={() => handleShare(certificate)}
                >
                  <span className="material-icons">share</span>
                </button>
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  )
}

export default Certificates

