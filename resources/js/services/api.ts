import axios from 'axios'

// Create a dedicated axios instance for your Vue app
const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    }
})

// Add request interceptor to automatically include auth tokens
api.interceptors.request.use(
    (config: any) => {
        const token = localStorage.getItem('auth_token')
        if (token) {
            config.headers.Authorization = `Bearer ${token}`
        }
        return config
    },
    (error: any) => {
        return Promise.reject(error)
    }
)

// Add response interceptor for error handling
api.interceptors.response.use(
    (response: any) => response,
    (error: any) => {
        // Handle common errors like expired tokens
        if (error.response?.status === 401) {
            localStorage.removeItem('auth_token')
            // todo - add proper logout handling when we create the auth store
        }
        return Promise.reject(error)
    }
)

export default api
