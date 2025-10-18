import React, { useEffect, useState } from 'react'
import { eventsAPI } from '../services/api'

const EventDrawer = ({ open, onClose, event }) => {
  const [details, setDetails] = useState(null)
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    const fetchDetails = async () => {
      if (!event?.id || !open) return
      setLoading(true)
      try {
        const res = await eventsAPI.getEventDetails(event.id)
        setDetails(res.data?.data || null)
      } catch (e) {
        setDetails(null)
      } finally {
        setLoading(false)
      }
    }
    fetchDetails()
  }, [event, open])
  useEffect(() => {
    if (open) {
      document.body.style.overflow = 'hidden'
    } else {
      document.body.style.overflow = ''
    }
    return () => { document.body.style.overflow = '' }
  }, [open])

  if (!open || !event) return null

  const getBackendBaseURL = () => {
    const hostname = window.location.hostname
    const port = '8000'
    if (hostname === 'localhost' || hostname === '127.0.0.1') {
      return `http://localhost:${port}`
    }
    return `http://${hostname}:${port}`
  }

  const getRenderableTerms = () => {
    let html = details?.terms || ''
    if (!html) return ''
    const base = getBackendBaseURL()
    // Replace absolute localhost links and prefix /storage images
    html = html.replace(/http:\/\/localhost:8000/gi, base)
    html = html.replace(/src=\"\/storage\//gi, `src=\"${base}/storage/`)
    html = html.replace(/href=\"\/storage\//gi, `href=\"${base}/storage/`)
    return html
  }

  return (
    <>
      <div className="drawer-backdrop" onClick={onClose} />
      <div className="drawer-sheet" role="dialog" aria-modal="true">
        <div className="drawer-handle" />
        <div className="drawer-header">
          <h3 className="drawer-title">{details?.title || event.title}</h3>
          <button className="drawer-close" onClick={onClose}>
            <span className="material-icons">close</span>
          </button>
        </div>

        {details?.poster_url && (
          <div className="drawer-poster">
            <img src={details.poster_url} alt="Event Poster" />
          </div>
        )}

        {(details?.description || event.description) && (
          <p className="drawer-desc">{details?.description || event.description}</p>
        )}

        <div className="drawer-detail">
          <div className="drawer-row">
            <span className="material-icons">event</span>
            <span>{new Date(details?.start_date || event.date).toLocaleDateString('en-MY', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}</span>
          </div>
          <div className="drawer-row">
            <span className="material-icons">schedule</span>
            <span>{details?.time || event.time || 'TBA'}</span>
          </div>
          <div className="drawer-row">
            <span className="material-icons">location_on</span>
            <span>{details?.location || event.location || 'TBA'}</span>
          </div>
          {details?.address && (
            <div className="drawer-row">
              <span className="material-icons">home</span>
              <span className="address-text">{details.address}</span>
            </div>
          )}
        </div>

        {/* Contact & Organizer */}
        <div className="drawer-detail">
          {details?.organizer && (
            <div className="drawer-row">
              <span className="material-icons">groups</span>
              <span>Organizer: {details.organizer}</span>
            </div>
          )}
          {details?.contact_person && (
            <div className="drawer-row">
              <span className="material-icons">person</span>
              <span>Contact Person: {details.contact_person}</span>
            </div>
          )}
          {details?.contact_phone && (
            <div className="drawer-row">
              <span className="material-icons">call</span>
              <span>{details.contact_phone}</span>
            </div>
          )}
          {details?.contact_email && (
            <div className="drawer-row">
              <span className="material-icons">email</span>
              <span>{details.contact_email}</span>
            </div>
          )}
        </div>

        {details?.terms && (
          <div className="drawer-terms">
            <h4>Event Terms & Conditions</h4>
            <div className="drawer-terms-box rich-html" dangerouslySetInnerHTML={{ __html: getRenderableTerms() }} />
          </div>
        )}

        <div className="drawer-actions">
          <button className="drawer-btn primary">
            <span className="material-icons">info</span>
            View Full Details
          </button>
          <button className="drawer-btn secondary" onClick={onClose}>
            <span className="material-icons">close</span>
            Close
          </button>
        </div>
      </div>
    </>
  )
}

export default EventDrawer


