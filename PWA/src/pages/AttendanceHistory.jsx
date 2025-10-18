import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { participantAPI } from '../services/api'
import LoadingScreen from '../components/LoadingScreen'

const AttendanceHistory = () => {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(true)
  const [attendance, setAttendance] = useState([])
  const [filter, setFilter] = useState('all') // all, completed, checked-in, pending

  useEffect(() => {
    fetchAttendanceHistory()
  }, [])

  const fetchAttendanceHistory = async () => {
    try {
      const response = await participantAPI.getAttendanceHistory()
      setAttendance(response.data.data || [])
    } catch (error) {
      // Silent error handling
    } finally {
      setLoading(false)
    }
  }

  const getAttendanceStatus = (record) => {
    if (record.checkin_time && record.checkout_time) return 'completed'
    if (record.checkin_time) return 'checked-in'
    return 'pending'
  }

  const getStatusIcon = (status) => {
    switch (status) {
      case 'completed': return 'check_circle'
      case 'checked-in': return 'schedule'
      case 'pending': return 'radio_button_unchecked'
      default: return 'event'
    }
  }

  const filteredAttendance = attendance.filter(record => {
    if (filter === 'all') return true
    return getAttendanceStatus(record) === filter
  })

  if (loading) {
    return <LoadingScreen message="Loading attendance history..." />
  }

  return (
    <div className="page-attendance-history">
      {/* Header */}
      <div className="attendance-history-header">
        <button className="back-btn" onClick={() => navigate('/settings')}>
          <span className="material-icons">arrow_back</span>
        </button>
        <h1 className="page-title">Attendance History</h1>
        <div style={{ width: '40px' }} />
      </div>

      {/* Filter Tabs */}
      <div className="filter-tabs">
        <button 
          className={`filter-tab ${filter === 'all' ? 'active' : ''}`}
          onClick={() => setFilter('all')}
        >
          All
        </button>
        <button 
          className={`filter-tab ${filter === 'completed' ? 'active' : ''}`}
          onClick={() => setFilter('completed')}
        >
          Completed
        </button>
        <button 
          className={`filter-tab ${filter === 'checked-in' ? 'active' : ''}`}
          onClick={() => setFilter('checked-in')}
        >
          Checked-In
        </button>
        <button 
          className={`filter-tab ${filter === 'pending' ? 'active' : ''}`}
          onClick={() => setFilter('pending')}
        >
          Pending
        </button>
      </div>

      {/* Attendance List */}
      <div className="attendance-list">
        {filteredAttendance.length === 0 ? (
          <div className="empty-state">
            <span className="material-icons empty-icon">history</span>
            <p className="empty-text">No attendance records</p>
            <p className="empty-subtext">
              {filter !== 'all' 
                ? 'No records match the selected filter' 
                : 'Start attending events to see your history'}
            </p>
          </div>
        ) : (
          filteredAttendance.map((record, index) => {
            const status = getAttendanceStatus(record)
            
            return (
              <div key={index} className="attendance-card">
                {/* Header */}
                <div className="attendance-header">
                  <h3 className="attendance-event-name">{record.event_name}</h3>
                  <div className={`attendance-status-badge ${status}`}>
                    <span className="material-icons">{getStatusIcon(status)}</span>
                  </div>
                </div>

                {/* Meta Info */}
                <div className="attendance-meta">
                  <div className="meta-row">
                    <span className="material-icons meta-icon-small">calendar_today</span>
                    <span>
                      {new Date(record.event_date).toLocaleDateString('en-MY', { 
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                      })}
                    </span>
                  </div>
                  <div className="meta-row">
                    <span className="material-icons meta-icon-small">location_on</span>
                    <span>{record.location}</span>
                  </div>
                </div>

                {/* Check-in/out Times */}
                <div className="attendance-times">
                  {/* Check-in */}
                  {record.checkin_time && (
                    <div className="time-item">
                      <div className="time-icon-wrapper checkin">
                        <span className="material-icons">login</span>
                      </div>
                      <div className="time-info">
                        <div className="time-label">Check-in</div>
                        <div className="time-value">
                          {new Date(record.checkin_time).toLocaleTimeString('en-MY', { 
                            hour: '2-digit', 
                            minute: '2-digit' 
                          })}
                        </div>
                        {record.checkin_lat && record.checkin_lng && (
                          <div className="location-coords">
                            <span className="material-icons">location_on</span>
                            <span>{record.checkin_lat.toFixed(6)}, {record.checkin_lng.toFixed(6)}</span>
                          </div>
                        )}
                      </div>
                    </div>
                  )}

                  {/* Check-out */}
                  {record.checkout_time && (
                    <div className="time-item">
                      <div className="time-icon-wrapper checkout">
                        <span className="material-icons">logout</span>
                      </div>
                      <div className="time-info">
                        <div className="time-label">Check-out</div>
                        <div className="time-value">
                          {new Date(record.checkout_time).toLocaleTimeString('en-MY', { 
                            hour: '2-digit', 
                            minute: '2-digit' 
                          })}
                        </div>
                        {record.checkout_lat && record.checkout_lng && (
                          <div className="location-coords">
                            <span className="material-icons">location_on</span>
                            <span>{record.checkout_lat.toFixed(6)}, {record.checkout_lng.toFixed(6)}</span>
                          </div>
                        )}
                      </div>
                    </div>
                  )}

                  {/* Pending State */}
                  {!record.checkin_time && (
                    <div style={{ 
                      textAlign: 'center', 
                      padding: 'var(--space-4)', 
                      color: 'var(--gray-500)',
                      fontSize: '13px'
                    }}>
                      <span className="material-icons" style={{ fontSize: '32px', display: 'block', marginBottom: '8px', opacity: 0.5 }}>
                        pending_actions
                      </span>
                      Waiting for check-in
                    </div>
                  )}
                </div>
              </div>
            )
          })
        )}
      </div>
    </div>
  )
}

export default AttendanceHistory

