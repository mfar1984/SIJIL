import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { participantAPI, eventsAPI } from '../services/api'
import LoadingScreen from '../components/LoadingScreen'
import EventDrawer from '../components/EventDrawer'

const Events = () => {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(true)
  const [refreshing, setRefreshing] = useState(false)
  const [events, setEvents] = useState([])
  const [searchQuery, setSearchQuery] = useState('')
  const [activeTab, setActiveTab] = useState('all') // all, upcoming, attended
  const [selectedEvent, setSelectedEvent] = useState(null)
  const [drawerOpen, setDrawerOpen] = useState(false)

  useEffect(() => {
    fetchEvents()
  }, [])

  const fetchEvents = async () => {
    try {
      const response = await participantAPI.getEvents()
      // Backend returns { success, data: { events: [...] } }
      const list = response?.data?.data?.events || []
      // eslint-disable-next-line no-console
      console.log('Events fetched:', Array.isArray(list) ? list.length : 0)
      setEvents(list)
    } catch (error) {
      console.error('Error fetching events:', error)
    } finally {
      setLoading(false)
      setRefreshing(false)
    }
  }

  const handleRefresh = () => {
    setRefreshing(true)
    fetchEvents()
  }

  const handleEventClick = (event) => {
    setSelectedEvent(event)
    setDrawerOpen(true)
  }

  const getEventStatus = (event) => {
    const now = new Date()
    const eventDate = new Date(event.date)
    const eventEndTime = new Date(`${event.date} ${event.end_time}`)
    
    if (event.status === 'attended') return 'attended'
    if (now >= eventDate && now <= eventEndTime) return 'live'
    if (now > eventEndTime) return 'completed'
    return 'registered'
  }

  const getStatusIcon = (status) => {
    switch (status) {
      case 'live': return 'radio_button_checked'
      case 'attended': return 'check_circle'
      case 'registered': return 'event_available'
      case 'completed': return 'event_busy'
      default: return 'event'
    }
  }

  const getStatusClass = (status) => {
    return `status-badge-icon status-${status}`
  }

  const getCountdown = (date) => {
    const now = new Date()
    const eventDate = new Date(date)
    const diffTime = eventDate - now
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    
    if (diffDays === 0) return 'Today'
    if (diffDays === 1) return 'Tomorrow'
    if (diffDays < 7) return `In ${diffDays} days`
    return null
  }

  // Filter events
  const filteredEvents = events.filter(event => {
    const matchesSearch = event.title?.toLowerCase().includes(searchQuery.toLowerCase()) ||
                         event.location?.toLowerCase().includes(searchQuery.toLowerCase())
    
    const now = new Date()
    const eventDate = new Date(event.date)
    
    if (activeTab === 'upcoming') {
      return matchesSearch && eventDate > now
    } else if (activeTab === 'attended') {
      return matchesSearch && event.status === 'attended'
    }
    
    return matchesSearch
  })

  if (loading) {
    return <LoadingScreen message="Loading events..." />
  }

  return (
    <div className="page-events">
      {/* Header */}
      <div className="page-header-events">
        <div className="header-top">
          <h1 className="page-title">My Events</h1>
          <button className="refresh-btn" onClick={handleRefresh} disabled={refreshing}>
            <span className={`material-icons ${refreshing ? 'spinning' : ''}`}>refresh</span>
          </button>
        </div>

        {/* Search */}
        <div className="search-box">
          <span className="material-icons search-icon">search</span>
          <input
            type="text"
            className="search-input"
            placeholder="Search events..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
          />
          {searchQuery && (
            <button className="clear-search" onClick={() => setSearchQuery('')}>
              <span className="material-icons">close</span>
            </button>
          )}
        </div>

        {/* Tabs */}
        <div className="tabs-container">
          <button 
            className={`tab-btn ${activeTab === 'all' ? 'active' : ''}`}
            onClick={() => setActiveTab('all')}
          >
            All
          </button>
          <button 
            className={`tab-btn ${activeTab === 'upcoming' ? 'active' : ''}`}
            onClick={() => setActiveTab('upcoming')}
          >
            Upcoming
          </button>
          <button 
            className={`tab-btn ${activeTab === 'attended' ? 'active' : ''}`}
            onClick={() => setActiveTab('attended')}
          >
            Attended
          </button>
        </div>
      </div>

      {/* Events List */}
      <div className="events-list">
        {filteredEvents.length === 0 ? (
          <div className="empty-state">
            <span className="material-icons empty-icon">event_busy</span>
            <p className="empty-text">No events found</p>
            <p className="empty-subtext">
              {searchQuery ? 'Try adjusting your search' : 'You haven\'t registered for any events yet'}
            </p>
          </div>
        ) : (
          filteredEvents.map(event => {
            const status = getEventStatus(event)
            const countdown = getCountdown(event.date)
            
            return (
              <div 
                key={event.id} 
                className="event-card-enhanced"
                onClick={() => handleEventClick(event)}
              >
                <div className="event-card-header">
                  <h3 className="event-title">{event.title}</h3>
                  <div className={getStatusClass(status)}>
                    <span className="material-icons">{getStatusIcon(status)}</span>
                  </div>
                </div>

                {event.description && (
                  <p className="event-description">{event.description}</p>
                )}

                <div className="event-details-grid">
                  <div className="detail-row-compact">
                    <span className="material-icons detail-icon-sm">calendar_today</span>
                    <span className="detail-text-sm">
                      {new Date(event.date).toLocaleDateString('en-MY', { 
                        weekday: 'short', 
                        month: 'short', 
                        day: 'numeric',
                        year: 'numeric'
                      })}
                    </span>
                  </div>
                  <div className="detail-row-compact">
                    <span className="material-icons detail-icon-sm">schedule</span>
                    <span className="detail-text-sm">{event.start_time} - {event.end_time}</span>
                  </div>
                  <div className="detail-row-compact full-width">
                    <span className="material-icons detail-icon-sm">location_on</span>
                    <span className="detail-text-sm">{event.location}</span>
                  </div>
                </div>

                <div className="event-card-footer">
                  <span className={`status-badge ${status === 'live' ? 'status-live' : status === 'attended' ? 'attended' : status === 'completed' ? 'status-completed' : 'registered'}`}>
                    {status === 'live' && '🔴 LIVE NOW'}
                    {status === 'attended' && 'ATTENDED'}
                    {status === 'registered' && 'REGISTERED'}
                    {status === 'completed' && 'COMPLETED'}
                  </span>
                  {countdown && status !== 'live' && status !== 'attended' && status !== 'completed' && (
                    <div className="countdown-text">
                      <span className="material-icons">schedule</span>
                      <span>{countdown}</span>
                    </div>
                  )}
                </div>
              </div>
            )
          })
        )}
      </div>

      {/* Event Drawer */}
      {drawerOpen && selectedEvent && (
        <EventDrawer 
          open={drawerOpen}
          event={selectedEvent} 
          onClose={() => setDrawerOpen(false)} 
        />
      )}
    </div>
  )
}

export default Events

