import axios from 'axios'
import router from '@/router'

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL + '/api/v1',
  headers: { 'Content-Type': 'application/json' },
})

apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers = config.headers || {}
    config.headers.Authorization = `Bearer ${token}`
  }
  // If sending FormData, remove Content-Type to let Axios auto-detect multipart/form-data with boundary
  if (config.data instanceof FormData) {
    delete config.headers['Content-Type']
  }
  return config
})

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_membre')
      router.replace({
        name: 'login',
        query: { redirect: router.currentRoute.value.fullPath },
      })
    } else if (error.response?.status === 403 && error.response?.data?.code === 'DOCUMENTS_NOT_VERIFIED') {
      const message = error.response?.data?.message || "Vos documents n'ont pas encore été vérifiés."

      if (router.currentRoute.value.path !== '/documents/pending') {
        router.replace({
          path: '/documents/pending',
          query: { message },
        })
      }
    }
    return Promise.reject(error)
  }
)

export default apiClient
