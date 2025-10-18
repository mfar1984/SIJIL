import { useState, useEffect, useRef } from 'react'
import { participantAPI } from '../services/api'

const Scan = () => {
  const [manualCode, setManualCode] = useState('')
  const [scanning, setScanning] = useState(false)
  const [result, setResult] = useState(null)
  const [location, setLocation] = useState({ lat: null, lng: null, error: null })
  const [gettingLocation, setGettingLocation] = useState(true)
  const [scannerActive, setScannerActive] = useState(false)
  const scannerRef = useRef(null)

  useEffect(() => {
    getLocation()
    initScanner()
    
    return () => {
      // Cleanup scanner on unmount
      if (scannerRef.current) {
        scannerRef.current.clear().catch(console.error)
      }
    }
  }, [])

  const getLocation = () => {
    setGettingLocation(true)
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          setLocation({
            lat: position.coords.latitude,
            lng: position.coords.longitude,
            error: null
          })
          setGettingLocation(false)
        },
        (error) => {
          setLocation({
            lat: null,
            lng: null,
            error: error.message
          })
          setGettingLocation(false)
        }
      )
    } else {
      setLocation({
        lat: null,
        lng: null,
        error: 'Geolocation not supported'
      })
      setGettingLocation(false)
    }
  }

  const initScanner = async () => {
    const { Html5QrcodeScanner } = await import('html5-qrcode')
    const scanner = new Html5QrcodeScanner(
      "qr-reader",
      { 
        fps: 10,
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0,
        rememberLastUsedCamera: true
      },
      false
    )

    scanner.render(onScanSuccess, onScanFailure)
    scannerRef.current = scanner
    setScannerActive(true)
  }

  const onScanSuccess = (decodedText, decodedResult) => {
    // Stop scanner
    if (scannerRef.current) {
      scannerRef.current.clear().catch(console.error)
      setScannerActive(false)
    }
    // Process the scanned code
    processAttendance(decodedText)
  }

  const onScanFailure = (error) => {
    // Silently ignore scan failures (common during scanning)
  }

  const handleManualSubmit = async (e) => {
    e.preventDefault()
    if (!manualCode.trim()) {
      alert('Please enter an attendance code')
      return
    }
    await processAttendance(manualCode.trim())
  }

  const processAttendance = async (code) => {
    setScanning(true)
    try {
      const response = await participantAPI.checkIn(
        null,
        code,
        location.lat,
        location.lng
      )
      
      setResult({
        success: true,
        data: response.data
      })
      setManualCode('')
    } catch (error) {
      setResult({
        success: false,
        message: error.response?.data?.message || 'Failed to process attendance. Please try again.'
      })
    } finally {
      setScanning(false)
    }
  }

  const handleScanAnother = () => {
    setResult(null)
    setManualCode('')
    // Restart scanner
    if (!scannerActive) {
      initScanner()
    }
  }

  if (result) {
    return (
      <div className="page-scan">
        <div className="scan-result">
          {result.success ? (
            <div className="result-success">
              <div className="success-icon-wrapper">
                <span className="material-icons success-icon">check_circle</span>
              </div>
              <h2 className="result-title">Attendance Recorded!</h2>
              <p className="result-subtitle">You have successfully checked in</p>
              
              <div className="result-details">
                <div className="detail-item">
                  <span className="detail-label">Event</span>
                  <span className="detail-value">{result.data.event_name || 'N/A'}</span>
                </div>
                <div className="detail-item">
                  <span className="detail-label">Time</span>
                  <span className="detail-value">
                    {result.data.time ? new Date(result.data.time).toLocaleTimeString('en-MY', { 
                      hour: '2-digit', 
                      minute: '2-digit' 
                    }) : 'N/A'}
                  </span>
                </div>
                <div className="detail-item">
                  <span className="detail-label">Status</span>
                  <span className="detail-value" style={{
                    color: result.data.status === 'Late' ? '#ef4444' : '#22c55e',
                    fontWeight: '600'
                  }}>{result.data.status || 'N/A'}</span>
                </div>
                {result.data.location && (result.data.location.latitude || result.data.location.longitude) && (
                  <div className="detail-item">
                    <span className="detail-label">Location (GPS)</span>
                    <span className="detail-value" style={{ fontSize: '11px' }}>
                      {result.data.location.latitude?.toFixed(6)}, {result.data.location.longitude?.toFixed(6)}
                    </span>
                  </div>
                )}
              </div>

              <button className="action-btn primary" onClick={handleScanAnother}>
                <span className="material-icons">qr_code_scanner</span>
                <span>Scan Another</span>
              </button>
            </div>
          ) : (
            <div className="result-error">
              <div className="error-icon-wrapper">
                <span className="material-icons error-icon">error</span>
              </div>
              <h2 className="result-title">Attendance Failed</h2>
              <p className="result-subtitle">{result.message}</p>
              
              <button className="action-btn primary" onClick={handleScanAnother}>
                <span className="material-icons">refresh</span>
                <span>Try Again</span>
              </button>
            </div>
          )}
        </div>
      </div>
    )
  }

  return (
    <div className="page-scan">
      {/* Header */}
      <div className="scanner-header">
        <h1 className="scanner-title">Scan Attendance</h1>
        <p className="scanner-subtitle">Scan QR code or enter code manually</p>
      </div>

      {/* Location Status */}
      <div className="location-status">
        {gettingLocation ? (
          <div className="status-item warning">
            <span className="material-icons">location_searching</span>
            <span>Getting location...</span>
          </div>
        ) : location.error ? (
          <div className="location-error-box">
            <div className="status-item error">
              <span className="material-icons">location_off</span>
              <span>Location unavailable</span>
            </div>
            <p className="error-message">{location.error}</p>
            <button className="retry-location-btn" onClick={getLocation}>
              <span className="material-icons">refresh</span>
              <span>Retry</span>
            </button>
          </div>
        ) : (
          <div className="status-item success">
            <span className="material-icons">location_on</span>
            <span>Location detected</span>
          </div>
        )}
      </div>

      {/* Scanner Area */}
      <div className="scanner-wrapper">
        <div className="qr-scanner-container">
          <div id="qr-reader" style={{
            borderRadius: '12px',
            overflow: 'hidden',
            border: '2px solid var(--gray-200)'
          }}></div>
        </div>
      </div>

      {/* Manual Entry */}
      <div className="scan-result">
        <div style={{
          background: 'white',
          padding: 'var(--space-4)',
          borderRadius: 'var(--radius-lg)',
          border: '1px solid var(--gray-200)'
        }}>
          <div style={{ textAlign: 'center', marginBottom: 'var(--space-3)' }}>
            <div style={{
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              gap: 'var(--space-3)',
              marginBottom: 'var(--space-2)'
            }}>
              <div style={{ flex: 1, height: '1px', background: 'var(--gray-300)' }} />
              <span style={{ fontSize: '12px', color: 'var(--gray-500)', fontWeight: '600' }}>OR</span>
              <div style={{ flex: 1, height: '1px', background: 'var(--gray-300)' }} />
            </div>
          </div>

          <form onSubmit={handleManualSubmit}>
            <div className="form-field">
              <label className="field-label">Enter Attendance Code</label>
              <input
                type="text"
                className="input-modern"
                placeholder="e.g., UZSt6fkwyqTwAQP40x..."
                value={manualCode}
                onChange={(e) => setManualCode(e.target.value)}
                disabled={scanning}
                style={{ paddingLeft: 'var(--space-3)', paddingRight: 'var(--space-3)' }}
              />
            </div>

            <button 
              type="submit" 
              className="login-btn-modern"
              disabled={scanning || !manualCode.trim()}
            >
              {scanning ? (
                <>
                  <div className="loading-spinner" />
                  <span>Processing...</span>
                </>
              ) : (
                <>
                  <span className="material-icons">check_circle</span>
                  <span>Submit Code</span>
                </>
              )}
            </button>
          </form>

          <div style={{
            background: '#eff6ff',
            border: '1px solid #bfdbfe',
            padding: 'var(--space-3)',
            borderRadius: 'var(--radius-md)',
            marginTop: 'var(--space-3)',
            display: 'flex',
            gap: 'var(--space-2)',
            fontSize: '12px',
            color: '#1e40af'
          }}>
            <span className="material-icons" style={{ fontSize: '16px' }}>info</span>
            <span>Scan the QR code displayed at the event venue or enter the attendance code manually</span>
          </div>
        </div>
      </div>

      {/* Instructions */}
      <div className="scan-instructions">
        <div className="instruction-item">
          <span className="material-icons">qr_code_scanner</span>
          <p>Position QR code in frame</p>
        </div>
        <div className="instruction-item">
          <span className="material-icons">straighten</span>
          <p>Keep device steady</p>
        </div>
        <div className="instruction-item">
          <span className="material-icons">flash_on</span>
          <p>Ensure good lighting</p>
        </div>
      </div>
    </div>
  )
}

export default Scan

