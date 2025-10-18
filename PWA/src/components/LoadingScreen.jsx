import { useState, useEffect } from 'react'

const LoadingScreen = ({ message = 'Loading' }) => {
  const [showPatience, setShowPatience] = useState(false)

  useEffect(() => {
    const timer = setTimeout(() => {
      setShowPatience(true)
    }, 5000) // 5 seconds

    return () => clearTimeout(timer)
  }, [])

  return (
    <div className="loading-screen">
      <div className="loading-content">
        <div className="loading-spinner-large"></div>
        <p className="loading-text">{message}...</p>
        {showPatience && (
          <p className="loading-patience">
            Please be patient.<br />
            This may take a moment.
          </p>
        )}
      </div>
    </div>
  )
}

export default LoadingScreen

