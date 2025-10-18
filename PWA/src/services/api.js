import axios from 'axios'

// Automatically detect the correct API base URL
const getApiBaseURL = () => {
  const hostname = window.location.hostname
  
  // Production domains - PWA at apps.e-certificate.com.my OR user.e-certificate.com.my
  // API always at login.e-certificate.com.my
  if (hostname === 'apps.e-certificate.com.my' || hostname === 'user.e-certificate.com.my') {
    return 'https://login.e-certificate.com.my'
  }
  
  // Development - localhost
  if (hostname === 'localhost' || hostname === '127.0.0.1') {
    return 'http://localhost:8000'
  }
  
  // Network IP for testing (e.g., 192.168.x.x)
  return `http://${hostname}:8000`
}

// Get the API base URL
const API_BASE_URL = getApiBaseURL()

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Request interceptor to attach token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor to handle 401 errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      window.location.href = '/login' // Redirect to login page
    }
    return Promise.reject(error)
  }
)

// PWA Participant Authentication API
export const authAPI = {
  login: (credentials) => api.post('/api/participant/login', credentials),
  register: (userData) => api.post('/api/participant/register', userData),
  logout: () => api.post('/api/participant/logout'),
  forgotPassword: (email) => api.post('/api/participant/forgot-password', { email }),
  resetPassword: (data) => api.post('/api/participant/reset-password', data),
}

// PWA Participant Data API
export const participantAPI = {
  getProfile: () => api.get('/api/participant/profile'),
  updateProfile: (data) => api.put('/api/participant/profile', data),
  changePassword: (data) => api.post('/api/participant/change-password', data),
  getAttendanceHistory: () => api.get('/api/participant/attendance-history'),
  getEvents: () => api.get('/api/participant/events'),
  getCertificates: () => api.get('/api/participant/certificates'),
  downloadCertificate: (certificateId) =>
    api.get(`/api/participant/certificates/${certificateId}/download`, {
      responseType: 'blob'
    }),
  checkIn: (eventId, qrCode, lat = null, lng = null) => api.post('/api/attendance/scan', {
    code: qrCode,
    mode: 'auto',
    lat: lat,
    lng: lng,
    device: 'pwa_web'
  }),
}

// Events API
export const eventsAPI = {
  getEventDetails: (eventId) => api.get(`/api/events/${eventId}`),
  getEventQRCode: (eventId) => api.get(`/api/events/${eventId}/qrcode`),
  registerForEvent: (eventId, participantData) =>
    api.post(`/api/events/${eventId}/register`, participantData),
}

export default api 