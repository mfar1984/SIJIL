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
    <div className="login-container">
      <div className="login-card">
        <div className="login-header">
          <div style={{
            width: '64px',
            height: '64px',
            background: 'linear-gradient(135deg, var(--primary-500), var(--secondary-500))',
            borderRadius: 'var(--radius-2xl)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            margin: '0 auto var(--space-4)',
            boxShadow: 'var(--shadow-lg)'
          }}>
            <span style={{ fontSize: '32px' }}>🔐</span>
          </div>
          <h2>Welcome Back</h2>
          <p>Sign in to access your events and certificates</p>
        </div>

        {error && (
          <div className="error-message">
            <span style={{ fontSize: '16px' }}>⚠️</span> {error}
          </div>
        )}

        <form onSubmit={handleSubmit} className="login-form">
          <div className="form-group">
            <label htmlFor="email">
              <span style={{ marginRight: '8px' }}>📧</span>
              Email Address
            </label>
            <input
              type="email"
              id="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              required
              placeholder="Enter your email address"
              autoComplete="email"
              autoCapitalize="none"
              autoCorrect="off"
            />
          </div>

          <div className="form-group">
            <label htmlFor="password">
              <span style={{ marginRight: '8px' }}>🔒</span>
              Password
            </label>
            <div className="password-input-container">
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
              />
              <button
                type="button"
                className="password-toggle"
                onClick={() => setShowPassword(!showPassword)}
                aria-label={showPassword ? "Hide password" : "Show password"}
              >
                {showPassword ? "🙈" : "👁️"}
              </button>
            </div>
          </div>

          <button
            type="submit"
            disabled={loading || !formData.email || !formData.password}
            className="login-btn"
          >
            {loading ? (
              <>
                <span className="loading-spinner"></span>
                Signing In...
              </>
            ) : (
              <>
                <span style={{ fontSize: '16px' }}>🚀</span>
                Sign In
              </>
            )}
          </button>
        </form>

        <div className="login-footer">
          <button
            onClick={handleForgotPassword}
            className="forgot-password-btn"
            type="button"
          >
            <span style={{ marginRight: '4px' }}>❓</span>
            Forgot your password?
          </button>
        </div>

        <div className="login-help">
          <p>
            <span style={{ marginRight: '4px' }}>💬</span>
            Need help? Contact your event organizer
          </p>
        </div>
      </div>
    </div>
  )
}

export default Login 