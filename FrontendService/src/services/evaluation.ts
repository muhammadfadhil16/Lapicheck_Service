import { useApi, type ApiError } from '@/composables/useApi'
import type { Laptop, LaptopBrand } from '@/constants/assessment'

export interface EvaluationInput {
  customer_name: string
  laptop_name: string
  price: number
  lcd_score: number
  keyboard_score: number
  ram_capacity: number
  battery_health: number
  laptop_id?: number
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
  laptop?: Laptop
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

export interface PaginatedResponse<T> extends PaginationMeta {
  data: T[]
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

  const getAiStatus = async (): Promise<boolean> => {
    const response = await api.get('/api/ai/status')
    return response.data.available === true
  }

  const getAiModels = async (): Promise<{
    models: { id: string; name: string }[]
    selected: string
  }> => {
    const response = await api.get('/api/ai/models')
    return { models: response.data.data, selected: response.data.selected }
  }

  const updateAiModel = async (model: string): Promise<void> => {
    await api.put('/api/ai/model', { model })
  }

  const testAiConnection = async (model: string): Promise<string> => {
    const response = await api.post('/api/ai/test-connection', { model })
    return response.data.message
  }

  const getAiKeywords = async (): Promise<string[]> => {
    const response = await api.get('/api/ai/keywords')
    return response.data.data
  }

  const addAiKeyword = async (keyword: string): Promise<string> => {
    const response = await api.post('/api/ai/keywords', { keyword })
    return response.data.data.keyword
  }

  const deleteAiKeyword = async (keyword: string): Promise<void> => {
    await api.delete(`/api/ai/keywords/${encodeURIComponent(keyword)}`)
  }

  const getLaptopBrands = async (): Promise<LaptopBrand[]> => {
    const response = await api.get('/api/laptop-brands', { params: { per_page: 100 } })
    const data = response.data.data ?? response.data
    return Array.isArray(data) ? data : []
  }

  const getLaptopBrandsPage = async (page: number): Promise<PaginatedResponse<LaptopBrand>> => {
    const response = await api.get('/api/laptop-brands', { params: { page } })
    return response.data
  }

  const createLaptopBrand = async (name: string): Promise<LaptopBrand> => {
    const response = await api.post('/api/laptop-brands', { name })
    return response.data.data
  }

  const updateLaptopBrand = async (id: number, name: string): Promise<LaptopBrand> => {
    const response = await api.put(`/api/laptop-brands/${id}`, { name })
    return response.data.data
  }

  const deleteLaptopBrand = async (id: number): Promise<void> => {
    await api.delete(`/api/laptop-brands/${id}`)
  }

  const getLaptops = async (brandId?: number): Promise<Laptop[]> => {
    const response = await api.get('/api/laptops', {
      params: { per_page: 100, ...(brandId ? { brand_id: brandId } : {}) },
    })
    const data = response.data.data ?? response.data
    return Array.isArray(data) ? data : []
  }

  const getLaptopsPage = async (
    page: number,
    brandId?: number,
    search?: string,
  ): Promise<PaginatedResponse<Laptop>> => {
    const response = await api.get('/api/laptops', {
      params: { page, ...(brandId ? { brand_id: brandId } : {}), ...(search ? { search } : {}) },
    })
    return response.data
  }

  const createLaptop = async (data: {
    brand_name: string
    model_name: string
    processor_name: string
    benchmark_score: number
  }): Promise<Laptop> => {
    const response = await api.post('/api/laptops', data)
    return response.data.data
  }

  const updateLaptop = async (
    id: number,
    data: { brand_id: number; model_name: string; processor_name: string; benchmark_score: number },
  ): Promise<Laptop> => {
    const response = await api.put(`/api/laptops/${id}`, data)
    return response.data.data
  }

  const deleteLaptop = async (id: number): Promise<void> => {
    await api.delete(`/api/laptops/${id}`)
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
      if (data.laptop_id) {
        formData.append('laptop_id', String(data.laptop_id))
      } else if (data.processor_id) {
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
    filters?: { search?: string; start_date?: string; end_date?: string },
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

  return {
    evaluate,
    getAiStatus,
    getAiModels,
    updateAiModel,
    testAiConnection,
    getAiKeywords,
    addAiKeyword,
    deleteAiKeyword,
    getAllAssessments,
    getAssessmentById,
    deleteAssessment,
    getLaptopBrands,
    getLaptopBrandsPage,
    createLaptopBrand,
    updateLaptopBrand,
    deleteLaptopBrand,
    getLaptops,
    getLaptopsPage,
    createLaptop,
    updateLaptop,
    deleteLaptop,
  }
}
