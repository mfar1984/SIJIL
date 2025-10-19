import { useState } from 'react'
import { authAPI } from '../services/api'

const Login = ({ onLogin }) => {
  const [formData, setFormData] = useState({
    email: '',
    password: ''
  })
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [showPassword, setShowPassword] = useState(false)
  const [showForgotModal, setShowForgotModal] = useState(false)
  const [forgotEmail, setForgotEmail] = useState('')
  const [forgotLoading, setForgotLoading] = useState(false)
  const [forgotSuccess, setForgotSuccess] = useState(false)
  const [forgotError, setForgotError] = useState('')

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    })
    if (error) setError('')
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)
    setError('')

    try {
      const response = await authAPI.login(formData)

      if (response.data.success) {
        localStorage.setItem('token', response.data.token)
        localStorage.setItem('user', JSON.stringify(response.data.user))
        onLogin(response.data.user)
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Login failed. Please check your credentials and try again.')
    } finally {
      setLoading(false)
    }
  }

  const handleForgotPassword = (e) => {
    e.preventDefault()
    setForgotEmail(formData.email || '')
    setShowForgotModal(true)
    setForgotSuccess(false)
    setForgotError('')
  }

  const handleSubmitForgot = async (e) => {
    e.preventDefault()
    const email = forgotEmail.trim()
    
    if (!email || !email.includes('@')) {
      setForgotError('Please enter a valid email address.')
      return
    }

    try {
      setForgotLoading(true)
      setForgotError('')
      const response = await authAPI.resetPassword({ email })
      
      if (response.data.success) {
        setForgotSuccess(true)
        setForgotError('')
      } else {
        setForgotError(response.data.message || 'Failed to send password reset.')
        setForgotLoading(false)
      }
    } catch (err) {
      const errorMsg = err.response?.data?.message || 'Failed to send password reset. Please try again.'
      setForgotError(errorMsg)
      setForgotLoading(false)
    }
  }

  const closeForgotModal = () => {
    setShowForgotModal(false)
    setForgotEmail('')
    setForgotSuccess(false)
    setForgotError('')
  }

  return (
    <div className="login-page">
      {/* Top Section - Logo & Branding */}
      <div className="login-top">
        <div className="login-logo-section">
          <img src="/logo.png" alt="E-Certificate" className="login-logo" />
          <h1 className="login-brand">E-Certificate</h1>
          <p className="login-tagline">Certiicate Generator</p>
        </div>
      </div>

      {/* Form Section */}
      <div className="login-form-section">
        <div className="login-welcome">
          <h2 className="login-title">Welcome Back</h2>
          <p className="login-subtitle">Sign in to continue</p>
        </div>

        {error && (
          <div className="login-error">
            <span className="material-icons">error</span>
            <span>{error}</span>
          </div>
        )}

        <form onSubmit={handleSubmit} className="login-form-modern">
          {/* Email Input */}
          <div className="form-field">
            <label htmlFor="email" className="field-label">Email</label>
            <div className="input-wrapper">
              <span className="material-icons input-icon-left">email</span>
              <input
                type="email"
                id="email"
                name="email"
                value={formData.email}
                onChange={handleChange}
                required
                placeholder="your@email.com"
                autoComplete="email"
                autoCapitalize="none"
                autoCorrect="off"
                className="input-modern"
              />
            </div>
          </div>

          {/* Password Input */}
          <div className="form-field">
            <label htmlFor="password" className="field-label">Password</label>
            <div className="input-wrapper">
              <span className="material-icons input-icon-left">lock</span>
              <input
                type={showPassword ? "text" : "password"}
                id="password"
                name="password"
                value={formData.password}
                onChange={handleChange}
                required
                placeholder="Enter your password"
                autoComplete="current-password"
                minLength="6"
                className="input-modern"
              />
              <button
                type="button"
                className="password-toggle-modern"
                onClick={() => setShowPassword(!showPassword)}
              >
                <span className="material-icons">
                  {showPassword ? 'visibility_off' : 'visibility'}
                </span>
              </button>
            </div>
          </div>

          {/* Login Button */}
          <button
            type="submit"
            disabled={loading || !formData.email || !formData.password}
            className="login-btn-modern"
          >
            {loading ? (
              <>
                <span className="loading-spinner"></span>
                Signing In...
              </>
            ) : (
              <>
                <span className="material-icons">login</span>
                Sign In
              </>
            )}
          </button>

          {/* Forgot Password Link */}
          <button
            onClick={handleForgotPassword}
            className="forgot-link"
            type="button"
          >
            Forgot password?
          </button>
        </form>

        {/* Footer Help */}
        <div className="login-help-modern">
          <span className="material-icons help-icon">help_outline</span>
          <p>Need help? Contact your event organizer</p>
        </div>
      </div>

      {/* Bottom Branding */}
      <div className="login-footer-brand">
        <p>E-Certificate © {new Date().getFullYear()}</p>
      </div>

      {/* Forgot Password Modal */}
      {showForgotModal && (
        <div className="modal-overlay" onClick={closeForgotModal}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()}>
            {!forgotSuccess ? (
              <>
                <div className="modal-header">
                  <span className="material-icons modal-icon">lock_reset</span>
                  <h3 className="modal-title">Reset Password</h3>
                  <p className="modal-subtitle">Enter your email to receive a new password</p>
                </div>

                {forgotError && (
                  <div className="modal-error">
                    <span className="material-icons">error</span>
                    <span>{forgotError}</span>
                  </div>
                )}

                <form onSubmit={handleSubmitForgot} className="modal-form">
                  <div className="form-field">
                    <label htmlFor="forgot-email" className="field-label">Email Address</label>
                    <div className="input-wrapper">
                      <span className="material-icons input-icon-left">email</span>
                      <input
                        type="email"
                        id="forgot-email"
                        value={forgotEmail}
                        onChange={(e) => {
                          setForgotEmail(e.target.value)
                          if (forgotError) setForgotError('')
                        }}
                        required
                        placeholder="your@email.com"
                        autoComplete="email"
                        autoFocus
                        className="input-modern"
                      />
                    </div>
                  </div>
                  <div className="modal-actions">
                    <button
                      type="button"
                      onClick={closeForgotModal}
                      className="modal-btn secondary"
                      disabled={forgotLoading}
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      className="modal-btn primary"
                      disabled={forgotLoading || !forgotEmail}
                    >
                      {forgotLoading ? (
                        <>
                          <span className="loading-spinner"></span>
                          Sending...
                        </>
                      ) : (
                        <>
                          <span className="material-icons">send</span>
                          Send Reset
                        </>
                      )}
                    </button>
                  </div>
                </form>
              </>
            ) : (
              <>
                <div className="modal-header">
                  <span className="material-icons modal-icon success">check_circle</span>
                  <h3 className="modal-title">Email Sent!</h3>
                  <p className="modal-subtitle">
                    If your email exists, a password reset has been sent to <strong>{forgotEmail}</strong>
                  </p>
                  <p className="modal-subtitle" style={{ marginTop: '12px', fontSize: '12px', color: 'var(--gray-600)' }}>
                    Please check your inbox and login with the new password.
                  </p>
                </div>
                <div className="modal-actions">
                  <button
                    type="button"
                    onClick={closeForgotModal}
                    className="modal-btn primary full"
                  >
                    <span className="material-icons">arrow_back</span>
                    Back to Login
                  </button>
                </div>
              </>
            )}
          </div>
        </div>
      )}
    </div>
  )
}

export default Login 