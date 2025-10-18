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
    alert('Forgot password feature will be implemented soon.')
  }

  return (
    <div className="login-page">
      {/* Top Section - Logo & Branding */}
      <div className="login-top">
        <div className="login-logo-section">
          <img src="/logo.png" alt="SIJIL" className="login-logo" />
          <h1 className="login-brand">SIJIL</h1>
          <p className="login-tagline">E-Certificate Mobile</p>
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
        <p>Powered by SIJIL © 2025</p>
      </div>
    </div>
  )
}

export default Login 