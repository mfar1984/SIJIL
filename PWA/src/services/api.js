import axios from 'axios'

// Automatically detect the correct API base URL
const getApiBaseURL = () => {
  const hostname = window.location.hostname
  const port = '8000'
  
  // If accessing from localhost, use localhost for API
  if (hostname === 'localhost' || hostname === '127.0.0.1') {
    return `http://localhost:${port}`
  }
  
  // If accessing from network IP, use network IP for API
  return `http://${hostname}:${port}`
}

const api = axios.create({
  baseURL: getApiBaseURL(),
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
  getEvents: () => api.get('/api/participant/events'),
  getCertificates: () => api.get('/api/participant/certificates'),
  downloadCertificate: (certificateId) =>
    api.get(`/api/participant/certificates/${certificateId}/download`, {
      responseType: 'blob'
    }),
  checkIn: (eventId, qrCode) => api.post('/api/participant/checkin', {
    event_id: eventId,
    qr_code: qrCode
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