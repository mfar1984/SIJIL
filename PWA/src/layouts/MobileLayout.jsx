import { useState, useEffect } from 'react'
import { useLocation } from 'react-router-dom'
import Header from '../components/Header'
import BottomNav from '../components/BottomNav'

const MobileLayout = ({ children, user, onLogout }) => {
  const location = useLocation()
  const isLoginPage = location.pathname === '/login'

  return (
    <div className="App">
      {!isLoginPage && (
        <>
          <Header user={user} onLogout={onLogout} />
          <main className="app-content">
            {children}
          </main>
          <BottomNav />
        </>
      )}
      {isLoginPage && children}
    </div>
  )
}

export default MobileLayout

