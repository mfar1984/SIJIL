import React from 'react'

const Header = ({ user, onLogout }) => {
  return (
    <header className="header">
      <div className="header-content">
        <div className="logo">
          <div className="logo-icon">
            <span style={{ fontSize: '18px', color: 'white' }}>📜</span>
          </div>
          <h1>E-Certificate</h1>
        </div>

        {user && (
          <div className="user-info">
            <span className="user-name">{user.name}</span>
            <button onClick={onLogout} className="logout-btn">
              <span style={{ fontSize: '14px' }}>🚪</span>
              Logout
            </button>
          </div>
        )}
      </div>
    </header>
  )
}

export default Header 