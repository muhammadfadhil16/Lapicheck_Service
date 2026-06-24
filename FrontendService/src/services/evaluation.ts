import { useApi, type ApiError } from '@/composables/useApi'
import type { Processor } from '@/constants/assessment'

export interface EvaluationInput {
  customer_name: string
  laptop_name: string
  price: number
  lcd_score: number
  keyboard_score: number
  ram_capacity: number
  battery_health: number
  processor_id?: number
  processor_name?: string
  processor_input?: number
  images?: File[]
  description?: string
  use_ai?: boolean
}

export interface EvaluationImage {
  id: number
  assessment_id: number
  image_path: string
}

export interface ProcessorRelation {
  id: number
  name: string
  benchmark_score: number
  category: string
}

export interface EvaluationData {
  id: number
  customer_name: string
  laptop_name: string
  final_score: number
  status: 'Tidak Layak' | 'Cukup Layak' | 'Layak'
  estimated_price: number
  market_price?: number | null
  images: EvaluationImage[]
  ai_conclusion: string
  lcd_input?: number
  battery_input?: number
  processor_input?: number
  keyboard_input?: number
  ram_input?: number
  description?: string
  created_at: string
  processor?: ProcessorRelation
  description_ignored?: boolean
  ai_warning?: string | null
  ai_used?: boolean
}

export interface EvaluationResponse {
  status: 'success' | 'error'
  data: EvaluationData
}

export interface PaginationMeta {
  current_page: number
  last_page: number
  total: number
  from: number
  to: number
}

export interface AssessmentListResponse {
  data: EvaluationData[]
  current_page: number
  last_page: number
  total: number
  from: number
  to: number
}

export type { ApiError }

export const evaluationService = () => {
  const { api } = useApi()

  const getProcessors = async (): Promise<Processor[]> => {
    try {
      const response = await api.get('/api/processors')
      return response.data.data || response.data
    } catch (error) {
      const apiErr = error as ApiError
      if (apiErr.status === 0) {
        console.error('Koneksi gagal saat memuat processor:', apiErr.message)
      } else {
        console.error('Gagal memuat processor:', apiErr.message)
      }
      throw error
    }
  }

  const evaluate = async (data: EvaluationInput): Promise<EvaluationData> => {
    try {
      const formData = new FormData()
      formData.append('customer_name', data.customer_name)
      formData.append('laptop_name', data.laptop_name)
      formData.append('market_price', String(data.price))
      formData.append('lcd', String(data.lcd_score))
      formData.append('keyboard', String(data.keyboard_score))
      formData.append('ram', String(data.ram_capacity))
      formData.append('battery', String(data.battery_health))
      if (data.processor_id) {
        formData.append('processor_id', String(data.processor_id))
      } else if (data.processor_name) {
        formData.append('processor_name', data.processor_name)
        formData.append('processor_input', String(data.processor_input ?? 50))
      }

      if (data.description) {
        formData.append('description', data.description)
      }

      if (data.use_ai) {
        formData.append('use_ai', '1')
      }

      if (data.images && data.images.length > 0) {
        data.images.forEach((file) => {
          formData.append('images[]', file)
        })
      }

      const response = await api.post('/api/assessments', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })

      return response.data.data || response.data.result
    } catch (error) {
      const apiErr = error as ApiError
      if (apiErr.status === 422) {
        console.warn('Validasi gagal:', apiErr.errors)
      } else if (apiErr.status === 0) {
        console.error('Koneksi gagal saat evaluasi:', apiErr.message)
      } else {
        console.error('Gagal evaluasi:', apiErr.message)
      }
      throw error
    }
  }

  const getAllAssessments = async (
    page: number = 1,
    filters?: { search?: string; start_date?: string; end_date?: string }
  ): Promise<AssessmentListResponse> => {
    try {
      const params = new URLSearchParams({ page: String(page) })
      if (filters?.search) params.append('search', filters.search)
      if (filters?.start_date) params.append('start_date', filters.start_date)
      if (filters?.end_date) params.append('end_date', filters.end_date)
      const response = await api.get(`/api/assessments?${params}`)
      return response.data.data || response.data
    } catch (error) {
      const apiErr = error as ApiError
      if (apiErr.status === 0) {
        console.error('Koneksi gagal saat memuat riwayat:', apiErr.message)
      } else {
        console.error('Gagal memuat riwayat:', apiErr.message)
      }
      throw error
    }
  }

  const getAssessmentById = async (id: number): Promise<EvaluationData> => {
    try {
      const response = await api.get(`/api/assessments/${id}`)
      return response.data.data || response.data.result
    } catch (error) {
      const apiErr = error as ApiError
      if (apiErr.status === 404) {
        console.warn('Data tidak ditemukan:', apiErr.message)
      } else {
        console.error('Gagal memuat detail:', apiErr.message)
      }
      throw error
    }
  }

  const deleteAssessment = async (id: number): Promise<void> => {
    try {
      await api.delete(`/api/assessments/${id}`)
    } catch (error) {
      const apiErr = error as ApiError
      if (apiErr.status === 0) {
        console.error('Koneksi gagal saat menghapus:', apiErr.message)
      } else {
        console.error('Gagal menghapus data:', apiErr.message)
      }
      throw error
    }
  }

  return { evaluate, getAllAssessments, getAssessmentById, deleteAssessment, getProcessors }
}
