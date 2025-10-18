import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { participantAPI } from '../services/api'

const ChangePassword = () => {
  const navigate = useNavigate()
  const [formData, setFormData] = useState({
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
  })
  const [showPasswords, setShowPasswords] = useState({
    current: false,
    new: false,
    confirm: false
  })
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [success, setSuccess] = useState(false)

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    })
    setError('')
  }

  const togglePasswordVisibility = (field) => {
    setShowPasswords({
      ...showPasswords,
      [field]: !showPasswords[field]
    })
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')

    // Validation
    if (!formData.current_password || !formData.new_password || !formData.new_password_confirmation) {
      setError('All fields are required')
      return
    }

    if (formData.new_password.length < 8) {
      setError('New password must be at least 8 characters')
      return
    }

    if (formData.new_password !== formData.new_password_confirmation) {
      setError('New passwords do not match')
      return
    }

    setLoading(true)
    try {
      await participantAPI.changePassword(formData)
      setSuccess(true)
      
      // Redirect after 2 seconds
      setTimeout(() => {
        navigate('/settings')
      }, 2000)
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to change password. Please try again.')
    } finally {
      setLoading(false)
    }
  }

  if (success) {
    return (
      <div className="page-change-password">
        <div className="success-screen">
          <span className="material-icons success-icon-large">check_circle</span>
          <h2 className="success-title">Password Changed!</h2>
          <p className="success-message">Your password has been updated successfully</p>
          <p className="redirect-text">Redirecting to settings...</p>
        </div>
      </div>
    )
  }

  return (
    <div className="page-change-password">
      {/* Header */}
      <div className="change-password-header">
        <button className="back-btn" onClick={() => navigate('/settings')}>
          <span className="material-icons">arrow_back</span>
        </button>
        <h1 className="page-title">Change Password</h1>
        <div style={{ width: '40px' }} />
      </div>

      {/* Content */}
      <div className="change-password-content">
        {/* Info Box */}
        <div className="password-info-box">
          <span className="material-icons info-icon-box">info</span>
          <div>
            <p className="info-text-bold">Password Requirements:</p>
            <ul className="requirements-list">
              <li>Minimum 8 characters</li>
              <li>Must not match current password</li>
              <li>For security, use a mix of letters and numbers</li>
            </ul>
          </div>
        </div>

        {/* Error Message */}
        {error && (
          <div className="login-error">
            <span className="material-icons">error</span>
            <span>{error}</span>
          </div>
        )}

        {/* Form */}
        <form className="password-form" onSubmit={handleSubmit}>
          {/* Current Password */}
          <div className="form-group-password">
            <label>Current Password</label>
            <div className="password-input-wrapper">
              <span className="material-icons input-icon">lock</span>
              <input
                type={showPasswords.current ? 'text' : 'password'}
                name="current_password"
                value={formData.current_password}
                onChange={handleChange}
                placeholder="Enter current password"
                disabled={loading}
              />
              <button
                type="button"
                className="toggle-password-btn"
                onClick={() => togglePasswordVisibility('current')}
              >
                <span className="material-icons">
                  {showPasswords.current ? 'visibility_off' : 'visibility'}
                </span>
              </button>
            </div>
          </div>

          {/* New Password */}
          <div className="form-group-password">
            <label>New Password</label>
            <div className="password-input-wrapper">
              <span className="material-icons input-icon">lock_open</span>
              <input
                type={showPasswords.new ? 'text' : 'password'}
                name="new_password"
                value={formData.new_password}
                onChange={handleChange}
                placeholder="Enter new password"
                disabled={loading}
              />
              <button
                type="button"
                className="toggle-password-btn"
                onClick={() => togglePasswordVisibility('new')}
              >
                <span className="material-icons">
                  {showPasswords.new ? 'visibility_off' : 'visibility'}
                </span>
              </button>
            </div>
          </div>

          {/* Confirm New Password */}
          <div className="form-group-password">
            <label>Confirm New Password</label>
            <div className="password-input-wrapper">
              <span className="material-icons input-icon">verified_user</span>
              <input
                type={showPasswords.confirm ? 'text' : 'password'}
                name="new_password_confirmation"
                value={formData.new_password_confirmation}
                onChange={handleChange}
                placeholder="Re-enter new password"
                disabled={loading}
              />
              <button
                type="button"
                className="toggle-password-btn"
                onClick={() => togglePasswordVisibility('confirm')}
              >
                <span className="material-icons">
                  {showPasswords.confirm ? 'visibility_off' : 'visibility'}
                </span>
              </button>
            </div>
          </div>

          {/* Submit Button */}
          <button type="submit" className="submit-password-btn" disabled={loading}>
            {loading ? (
              <>
                <div className="loading-spinner" />
                <span>Updating Password...</span>
              </>
            ) : (
              <>
                <span className="material-icons">check_circle</span>
                <span>Update Password</span>
              </>
            )}
          </button>
        </form>
      </div>
    </div>
  )
}

export default ChangePassword

