import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { participantAPI } from '../services/api'
import LoadingScreen from '../components/LoadingScreen'

const PersonalInformation = () => {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [message, setMessage] = useState({ type: '', text: '' })
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    organization: '',
    job_title: '',
    address1: '',
    address2: '',
    city: '',
    state: '',
    postcode: '',
    country: '',
  })

  useEffect(() => {
    fetchProfile()
  }, [])

  const fetchProfile = async () => {
    try {
      const response = await participantAPI.getProfile()
      const data = response.data.data
      setFormData({
        name: data.name || '',
        email: data.email || '',
        phone: data.phone || '',
        organization: data.organization || '',
        job_title: data.job_title || '',
        address1: data.address1 || '',
        address2: data.address2 || '',
        city: data.city || '',
        state: data.state || '',
        postcode: data.postcode || '',
        country: data.country || 'Malaysia',
      })
    } catch (error) {
      console.error('Error fetching profile:', error)
      setMessage({ type: 'error', text: 'Failed to load profile data' })
    } finally {
      setLoading(false)
    }
  }

  const handleChange = (e) => {
    const { name, value } = e.target
    setFormData(prev => ({ ...prev, [name]: value }))
    if (message.text) setMessage({ type: '', text: '' })
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setSaving(true)
    setMessage({ type: '', text: '' })

    try {
      const response = await participantAPI.updateProfile(formData)
      
      if (response.data.success) {
        setMessage({ type: 'success', text: 'Profile updated successfully!' })
        
        // Update localStorage user data
        const savedUser = localStorage.getItem('user')
        if (savedUser) {
          const user = JSON.parse(savedUser)
          user.name = formData.name
          user.phone = formData.phone
          user.organization = formData.organization
          localStorage.setItem('user', JSON.stringify(user))
        }
        
        // Redirect back to settings after 1.5s
        setTimeout(() => {
          navigate('/settings')
        }, 1500)
      }
    } catch (error) {
      console.error('Error updating profile:', error)
      setMessage({ 
        type: 'error', 
        text: error.response?.data?.message || 'Failed to update profile. Please try again.' 
      })
    } finally {
      setSaving(false)
    }
  }

  if (loading) {
    return <LoadingScreen message="Loading profile..." />
  }

  return (
    <div className="page-profile">
      {/* Header with Back Button */}
      <div className="page-header-with-back">
        <button className="back-btn" onClick={() => navigate('/settings')}>
          <span className="material-icons">arrow_back</span>
        </button>
        <h2 className="page-title">Personal Information</h2>
        <div style={{ width: '40px' }}></div> {/* Spacer for centering */}
      </div>

      {/* Alert Message */}
      {message.text && (
        <div className={`alert-message ${message.type}`}>
          <span className="material-icons">
            {message.type === 'success' ? 'check_circle' : 'error'}
          </span>
          <span>{message.text}</span>
        </div>
      )}

      {/* Form */}
      <form onSubmit={handleSubmit} className="profile-form">
        {/* Basic Information Section */}
        <div className="form-section">
          <h3 className="form-section-title">Basic Information</h3>
          
          <div className="form-group">
            <label htmlFor="name" className="form-label required">Full Name</label>
            <input
              type="text"
              id="name"
              name="name"
              value={formData.name}
              onChange={handleChange}
              required
              className="form-input"
              placeholder="Enter your full name"
            />
          </div>

          <div className="form-group">
            <label htmlFor="email" className="form-label">Email</label>
            <input
              type="email"
              id="email"
              name="email"
              value={formData.email}
              disabled
              className="form-input disabled"
              placeholder="Email cannot be changed"
            />
            <p className="form-help-text">Email cannot be changed for security reasons</p>
          </div>

          <div className="form-group">
            <label htmlFor="phone" className="form-label">Phone Number</label>
            <input
              type="tel"
              id="phone"
              name="phone"
              value={formData.phone}
              onChange={handleChange}
              className="form-input"
              placeholder="e.g. 60123456789"
            />
          </div>

          <div className="form-group">
            <label htmlFor="organization" className="form-label">Organization</label>
            <input
              type="text"
              id="organization"
              name="organization"
              value={formData.organization}
              onChange={handleChange}
              className="form-input"
              placeholder="Your organization name"
            />
          </div>

          <div className="form-group">
            <label htmlFor="job_title" className="form-label">Job Title</label>
            <input
              type="text"
              id="job_title"
              name="job_title"
              value={formData.job_title}
              onChange={handleChange}
              className="form-input"
              placeholder="Your job title or position"
            />
          </div>
        </div>

        {/* Address Section */}
        <div className="form-section">
          <h3 className="form-section-title">Address</h3>
          
          <div className="form-group">
            <label htmlFor="address1" className="form-label">Address Line 1</label>
            <input
              type="text"
              id="address1"
              name="address1"
              value={formData.address1}
              onChange={handleChange}
              className="form-input"
              placeholder="Street address, P.O. box"
            />
          </div>

          <div className="form-group">
            <label htmlFor="address2" className="form-label">Address Line 2</label>
            <input
              type="text"
              id="address2"
              name="address2"
              value={formData.address2}
              onChange={handleChange}
              className="form-input"
              placeholder="Apartment, suite, unit, building, floor, etc."
            />
          </div>

          <div className="form-row">
            <div className="form-group">
              <label htmlFor="city" className="form-label">City</label>
              <input
                type="text"
                id="city"
                name="city"
                value={formData.city}
                onChange={handleChange}
                className="form-input"
                placeholder="City"
              />
            </div>

            <div className="form-group">
              <label htmlFor="postcode" className="form-label">Postcode</label>
              <input
                type="text"
                id="postcode"
                name="postcode"
                value={formData.postcode}
                onChange={handleChange}
                className="form-input"
                placeholder="Postcode"
              />
            </div>
          </div>

          <div className="form-group">
            <label htmlFor="state" className="form-label">State</label>
            <select
              id="state"
              name="state"
              value={formData.state}
              onChange={handleChange}
              className="form-select"
            >
              <option value="">Select State</option>
              <option value="Johor">Johor</option>
              <option value="Kedah">Kedah</option>
              <option value="Kelantan">Kelantan</option>
              <option value="Melaka">Melaka</option>
              <option value="Negeri Sembilan">Negeri Sembilan</option>
              <option value="Pahang">Pahang</option>
              <option value="Penang">Penang</option>
              <option value="Perak">Perak</option>
              <option value="Perlis">Perlis</option>
              <option value="Sabah">Sabah</option>
              <option value="Sarawak">Sarawak</option>
              <option value="Selangor">Selangor</option>
              <option value="Terengganu">Terengganu</option>
              <option value="Kuala Lumpur">Kuala Lumpur</option>
              <option value="Labuan">Labuan</option>
              <option value="Putrajaya">Putrajaya</option>
            </select>
          </div>

          <div className="form-group">
            <label htmlFor="country" className="form-label">Country</label>
            <input
              type="text"
              id="country"
              name="country"
              value={formData.country}
              onChange={handleChange}
              className="form-input"
              placeholder="Country"
            />
          </div>
        </div>

        {/* Action Buttons */}
        <div className="form-actions">
          <button
            type="button"
            className="btn-secondary"
            onClick={() => navigate('/settings')}
            disabled={saving}
          >
            Cancel
          </button>
          <button
            type="submit"
            className="btn-primary"
            disabled={saving}
          >
            {saving ? (
              <>
                <span className="loading-spinner-small"></span>
                Saving...
              </>
            ) : (
              <>
                <span className="material-icons">save</span>
                Save Changes
              </>
            )}
          </button>
        </div>
      </form>
    </div>
  )
}

export default PersonalInformation

