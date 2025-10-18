import { useState, useEffect } from 'react'
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom'
import './App.css'

// Layout
import MobileLayout from './layouts/MobileLayout'

// Pages
import Login from './components/Login'
import Home from './pages/Home'
import Events from './pages/Events'
import Scan from './pages/Scan'
import Certificates from './pages/Certificates'
import Settings from './pages/Settings'
import PersonalInformation from './pages/PersonalInformation'
import ChangePassword from './pages/ChangePassword'
import AttendanceHistory from './pages/AttendanceHistory'

function App() {
  const [isAuthenticated, setIsAuthenticated] = useState(false)
  const [user, setUser] = useState(null)

  useEffect(() => {
    // Check if user is already logged in
    const token = localStorage.getItem('token')
    const savedUser = localStorage.getItem('user')
    if (token && savedUser) {
      try {
        setUser(JSON.parse(savedUser))
        setIsAuthenticated(true)
      } catch (e) {
        localStorage.removeItem('token')
        localStorage.removeItem('user')
      }
    }
  }, [])

  const handleLogin = (userData) => {
    setUser(userData)
    setIsAuthenticated(true)
  }

  const handleLogout = () => {
    setUser(null)
    setIsAuthenticated(false)
  }

  return (
    <Router>
      <MobileLayout user={user} onLogout={handleLogout}>
        <Routes>
          <Route 
            path="/login" 
            element={
              !isAuthenticated ? (
                <Login onLogin={handleLogin} />
              ) : (
                <Navigate to="/home" replace />
              )
            } 
          />
          <Route 
            path="/home" 
            element={
              isAuthenticated ? (
                <Home user={user} />
              ) : (
                <Navigate to="/login" replace />
              )
            } 
          />
          <Route 
            path="/events" 
            element={
              isAuthenticated ? (
                <Events />
              ) : (
                <Navigate to="/login" replace />
              )
            } 
          />
          <Route 
            path="/scan" 
            element={
              isAuthenticated ? (
                <Scan />
              ) : (
                <Navigate to="/login" replace />
              )
            } 
          />
          <Route 
            path="/certificates" 
            element={
              isAuthenticated ? (
                <Certificates />
              ) : (
                <Navigate to="/login" replace />
              )
            } 
          />
          <Route 
            path="/settings/profile" 
            element={
              isAuthenticated ? (
                <PersonalInformation />
              ) : (
                <Navigate to="/login" replace />
              )
            } 
          />
          <Route 
            path="/settings" 
            element={
              isAuthenticated ? (
                <Settings user={user} onLogout={handleLogout} />
              ) : (
                <Navigate to="/login" replace />
              )
            } 
          />
          <Route 
            path="/change-password" 
            element={
              isAuthenticated ? (
                <ChangePassword />
              ) : (
                <Navigate to="/login" replace />
              )
            } 
          />
          <Route 
            path="/attendance-history" 
            element={
              isAuthenticated ? (
                <AttendanceHistory />
              ) : (
                <Navigate to="/login" replace />
              )
            } 
          />
          <Route 
            path="/" 
            element={<Navigate to={isAuthenticated ? "/home" : "/login"} replace />} 
          />
        </Routes>
      </MobileLayout>
    </Router>
  )
}

export default App

