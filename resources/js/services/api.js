import axios from 'axios'
import { useGlobalToast } from '@/composables/useGlobalToast'

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
})

const { toast } = useGlobalToast()

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
        if (error.response?.status === 403 && !window.location.pathname.startsWith('/403')) {
            window.location.href = '/403'
        }
        if (error.response?.status === 404 && error.response?.data?.message?.includes('No query results for model')) {
            error.response.data.message = 'Data tidak ditemukan.'
        }
        if (!error.response) {
            if (error.code !== 'ERR_CANCELED') {
                toast('Tidak dapat terhubung ke server. Periksa koneksi Anda.', 'error')
            }
        } else if (error.response.status >= 500) {
            toast(error.response.data?.message || 'Terjadi kesalahan pada server.', 'error')
        }
        return Promise.reject(error)
    }
)

export default api
