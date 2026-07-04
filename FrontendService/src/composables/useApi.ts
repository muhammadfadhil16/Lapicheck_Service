import axios from 'axios'
import type { AxiosError } from 'axios'

export const BASE_URL = import.meta.env.VITE_BASE_URL || ''

export interface ValidationErrors {
  [field: string]: string[]
}

export interface ApiError {
  status: number
  message: string
  errors: ValidationErrors | null
}

export const useApi = () => {
  const api = axios.create({
    baseURL: BASE_URL,
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
  })

  api.interceptors.response.use(
    (response) => response,
    (error: AxiosError<{ message?: string; errors?: ValidationErrors }>) => {
      const apiError: ApiError = {
        status: error.response?.status ?? 0,
        message: 'Terjadi kesalahan yang tidak terduga.',
        errors: null,
      }

      if (!error.response) {
        apiError.message =
          'Tidak dapat terhubung ke server. Periksa koneksi internet dan pastikan backend berjalan.'
        return Promise.reject(apiError)
      }

      const { status, data } = error.response

      if (status === 422) {
        apiError.message = data?.message ?? 'Validasi gagal. Periksa kembali input Anda.'
        apiError.errors = data?.errors ?? null
      } else if (status === 404) {
        apiError.message = data?.message ?? 'Data yang diminta tidak ditemukan.'
      } else if (status === 500) {
        apiError.message = data?.message ?? 'Terjadi kesalahan pada server. Silakan coba lagi.'
      } else if (status === 413) {
        apiError.message = 'Ukuran file terlalu besar. Maksimal 2MB per file.'
      } else if (status === 429) {
        apiError.message = 'Terlalu banyak permintaan. Silakan tunggu beberapa saat.'
      } else {
        apiError.message = data?.message ?? apiError.message
      }

      return Promise.reject(apiError)
    },
  )

  return { api }
}

export const getImageUrl = (path: string | null | undefined): string => {
  if (!path) return ''
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  const cleanPath = path.replace(/^\/+/, '')
  const baseUrl = BASE_URL.replace(/\/+$/, '')
  return `${baseUrl}/storage/${cleanPath}`
}
