import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { participantAPI } from '../services/api'
import LoadingScreen from '../components/LoadingScreen'

const Home = ({ user }) => {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(true)
  const [events, setEvents] = useState([])
  const [certificates, setCertificates] = useState([])
  const [attendanceHistory, setAttendanceHistory] = useState([])
  const [currentSlide, setCurrentSlide] = useState(0)
  const [touchStart, setTouchStart] = useState(0)
  const [touchEnd, setTouchEnd] = useState(0)

  useEffect(() => {
    fetchDashboardData()
  }, [])

  // Handle touch swipe for carousel
  const handleTouchStart = (e) => {
    setTouchStart(e.targetTouches[0].clientX)
  }

  const handleTouchMove = (e) => {
    setTouchEnd(e.targetTouches[0].clientX)
  }

  const handleTouchEnd = () => {
    if (!touchStart || !touchEnd) return
    
    const distance = touchStart - touchEnd
    const isLeftSwipe = distance > 50
    const isRightSwipe = distance < -50
    
    if (isLeftSwipe && currentSlide < upcomingEvents.length - 1) {
      setCurrentSlide(currentSlide + 1)
    }
    
    if (isRightSwipe && currentSlide > 0) {
      setCurrentSlide(currentSlide - 1)
    }
    
    setTouchStart(0)
    setTouchEnd(0)
  }

  const fetchDashboardData = async () => {
    try {
      const [eventsRes, certsRes, attendanceRes] = await Promise.all([
        participantAPI.getEvents(),
        participantAPI.getCertificates(),
        participantAPI.getAttendanceHistory()
      ])
      
      // Backend responses:
      // - events:        { success, data: { events: [...] } }
      // - certificates:  { success, data: { certificates: [...] } }
      // - attendance:    { success, data: [...] }
      const eventsList = eventsRes?.data?.data?.events || []
      const certsList = certsRes?.data?.data?.certificates || []
      const attendanceList = attendanceRes?.data?.data || []
      // eslint-disable-next-line no-console
      console.log('Dashboard fetched:', { events: eventsList.length, certificates: certsList.length, attendance: attendanceList.length })
      setEvents(eventsList)
      setCertificates(certsList)
      setAttendanceHistory(attendanceList)
    } catch (error) {
      console.error('Error fetching dashboard data:', error)
    } finally {
      setLoading(false)
    }
  }

  if (loading) {
    return <LoadingScreen message="Loading your dashboard..." />
  }

  const getGreeting = () => {
    const hour = new Date().getHours()
    if (hour < 12) return 'Good morning'
    if (hour < 18) return 'Good afternoon'
    return 'Good evening'
  }
  
  // Helper to build proper start/end Date objects using date + time
  const getEventRange = (evt) => {
    const getDatePart = (d) => (d ? String(d).substring(0, 10) : '')
    const datePart = getDatePart(evt.date)
    const endDatePart = getDatePart(evt.end_date) || datePart
    const startTime = (evt.start_time || '00:00').substring(0,5)
    const endTime = (evt.end_time || '23:59').substring(0,5)
    const start = new Date(`${datePart}T${startTime}:00`)
    const end = new Date(`${endDatePart}T${endTime}:00`)
    return { start, end }
  }

  const now = new Date()
  const upcomingEvents = events
    // Upcoming if event has not ended yet
    .filter(e => {
      const { end } = getEventRange(e)
      return now <= end
    })
    // Sort by start time ascending
    .sort((a, b) => getEventRange(a).start - getEventRange(b).start)
  const nextEvent = upcomingEvents[currentSlide]
  const attendedEvents = events.filter(e => e.status === 'attended').length
  const upcomingCount = upcomingEvents.length

  const getCountdown = (date, startTime) => {
    const eventDate = new Date(`${String(date).substring(0,10)}T${(startTime || '00:00').substring(0,5)}:00`)
    const diffTime = eventDate - now
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    
    if (diffDays === 0) return 'Today'
    if (diffDays === 1) return '1 day away'
    if (diffDays < 7) return `${diffDays} days away`
    if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks away`
    return `${Math.floor(diffDays / 30)} months away`
  }

  const recentCheckins = attendanceHistory
    .filter(r => r.checkin_time || r.event_date)
    .sort((a, b) => new Date(b.checkin_time || b.event_date) - new Date(a.checkin_time || a.event_date))
    .slice(0, 3)

  return (
    <div className="page-home">
      {/* Welcome Section */}
      <div className="welcome-section">
        <h1 className="greeting">{getGreeting()}, {user?.name?.split(' ')[0]}! 👋</h1>
        <p className="subtitle">Here's your activity summary</p>
      </div>

      {/* Next Event Carousel */}
      {upcomingEvents.length > 0 && (
        <div className="next-event-carousel">
          <div 
            className="next-event-card" 
            onTouchStart={handleTouchStart}
            onTouchMove={handleTouchMove}
            onTouchEnd={handleTouchEnd}
          >
            <div className="next-event-header">
              {(() => {
                const { start, end } = getEventRange(nextEvent)
                const isLive = now >= start && now <= end
                return (
                  <>
                    <span className="material-icons next-event-icon">{isLive ? 'broadcast_on_home' : 'event'}</span>
                    <span className="next-event-label">{isLive ? 'Live now' : 'Next Event'}</span>
                  </>
                )
              })()}
            </div>
            <h3 className="next-event-title">{nextEvent.title}</h3>
            {(() => {
              const { start, end } = getEventRange(nextEvent)
              const isLive = now >= start && now <= end
              return isLive ? (
                <div className="live-badge">
                  <span className="pulse-dot" />
                  LIVE NOW
                </div>
              ) : (
                <div className="countdown-badge">{getCountdown(nextEvent.date, nextEvent.start_time)}</div>
              )
            })()}
            <div className="next-event-details">
              <div className="next-event-detail">
                <span className="material-icons">calendar_today</span>
                <span>{new Date(nextEvent.date).toLocaleDateString('en-MY', { 
                  weekday: 'long', 
                  year: 'numeric', 
                  month: 'long', 
                  day: 'numeric' 
                })}</span>
              </div>
              <div className="next-event-detail">
                <span className="material-icons">schedule</span>
                <span>{(nextEvent.start_time || '').substring(0,5)} - {(nextEvent.end_time || '').substring(0,5)}</span>
              </div>
              <div className="next-event-detail">
                <span className="material-icons">location_on</span>
                <span>{nextEvent.location}</span>
              </div>
            </div>
            <button 
              className="view-event-btn"
              onClick={() => navigate('/events')}
            >
              <span>View Event Details</span>
              <span className="material-icons">arrow_forward</span>
            </button>
          </div>
          
          {upcomingEvents.length > 1 && (
            <div className="carousel-indicators">
              {upcomingEvents.map((_, index) => (
                <div
                  key={index}
                  className={`indicator ${index === currentSlide ? 'active' : ''}`}
                  onClick={() => setCurrentSlide(index)}
                />
              ))}
            </div>
          )}
        </div>
      )}

      {/* Stats Grid */}
      <div className="stats-grid-expanded">
        <div className="stat-card-mini blue">
          <span className="material-icons stat-mini-icon">event_available</span>
          <div className="stat-mini-value">{events.length}+</div>
          <div className="stat-mini-label">Total<br/>Events</div>
        </div>
        <div className="stat-card-mini green">
          <span className="material-icons stat-mini-icon">check_circle</span>
          <div className="stat-mini-value">{attendedEvents}+</div>
          <div className="stat-mini-label">Attended</div>
        </div>
        <div className="stat-card-mini purple">
          <span className="material-icons stat-mini-icon">schedule</span>
          <div className="stat-mini-value">{upcomingCount}</div>
          <div className="stat-mini-label">Upcoming</div>
        </div>
        <div className="stat-card-mini orange">
          <span className="material-icons stat-mini-icon">workspace_premium</span>
          <div className="stat-mini-value">{certificates.length}+</div>
          <div className="stat-mini-label">Certificates</div>
        </div>
      </div>

      {/* Quick Actions */}
      <div className="section">
        <h2 className="section-title">Quick Actions</h2>
        <div className="action-list">
          <button className="action-item primary-action" onClick={() => navigate('/scan')}>
            <span className="material-icons action-icon-lg">qr_code_scanner</span>
            <div className="action-content">
              <span className="action-text-main">Scan QR for Attendance</span>
              <span className="action-text-sub">Check-in or check-out from events</span>
            </div>
            <span className="material-icons action-arrow">chevron_right</span>
          </button>
          <button className="action-item" onClick={() => navigate('/settings')}>
            <span className="material-icons action-icon">person</span>
            <span className="action-text">View My Profile</span>
            <span className="material-icons action-arrow">chevron_right</span>
          </button>
          <button className="action-item" onClick={() => navigate('/certificates')}>
            <span className="material-icons action-icon">workspace_premium</span>
            <span className="action-text">My Certificates</span>
            <span className="material-icons action-arrow">chevron_right</span>
          </button>
        </div>
      </div>

      {/* Recent Check-ins */}
      <div className="section">
        <h2 className="section-title">Recent Check-ins</h2>
        {recentCheckins.length > 0 ? (
          <div className="recent-checkins-list">
            {recentCheckins.map((checkin, index) => (
              <div key={index} className="checkin-item">
                <div className="checkin-icon-wrapper">
                  <span className="material-icons checkin-icon">check_circle</span>
                </div>
                <div className="checkin-info">
                  <div className="checkin-title">{checkin.event_name}</div>
                  <div className="checkin-time">
                    {new Date(checkin.checkin_time || checkin.event_date).toLocaleDateString('en-MY', { 
                      month: 'short', 
                      day: 'numeric', 
                      hour: '2-digit', 
                      minute: '2-digit' 
                    })}
                  </div>
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div style={{ 
            textAlign: 'center', 
            padding: 'var(--space-6)', 
            color: 'var(--gray-500)',
            fontSize: '13px',
            background: 'white',
            borderRadius: 'var(--radius-lg)',
            border: '1px solid var(--gray-200)'
          }}>
            <span className="material-icons" style={{ fontSize: '40px', display: 'block', marginBottom: 'var(--space-2)', opacity: 0.5 }}>
              history
            </span>
            <p style={{ margin: 0, fontWeight: 500 }}>No recent check-ins</p>
            <p style={{ margin: '4px 0 0', fontSize: '12px' }}>Your attendance history will appear here</p>
          </div>
        )}
      </div>
    </div>
  )
}

export default Home

