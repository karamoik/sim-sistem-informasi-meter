import axios from 'axios'

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
})

api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token')
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    const fingerprint = localStorage.getItem('session_fingerprint')
    if (fingerprint) {
        config.headers['X-Session-Fingerprint'] = fingerprint
    }
    return config
})

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            const data = error.response?.data
            if (data?.code === 'session_killed') {
                localStorage.removeItem('token')
                localStorage.removeItem('user')
                localStorage.removeItem('session_fingerprint')
                window.location.href = '/login?reason=session_killed'
                return Promise.reject(error)
            }
            localStorage.removeItem('token')
            localStorage.removeItem('user')
            localStorage.removeItem('session_fingerprint')
            window.location.href = '/login'
        }
        return Promise.reject(error)
    }
)

export default api
