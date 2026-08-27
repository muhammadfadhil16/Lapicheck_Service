<template>
  <div class="space-y-xl">
    <!-- Page Header -->
    <div class="space-y-sm">
      <h1 class="font-h1 text-h1 text-primary">Riwayat Penilaian</h1>
      <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl">
        Akses dan kelola seluruh log penilaian laptop. Gunakan fitur pencarian untuk menemukan hasil
        spesifik berdasarkan ID atau model.
      </p>
    </div>

    <!-- Controls: Search & Filter -->
    <div class="flex flex-col sm:flex-row gap-md items-center justify-between">
      <div class="relative w-full sm:max-w-md">
        <span
          class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-outline"
          >search</span
        >
        <input
          v-model="searchQuery"
          class="w-full pl-[44px] pr-md py-md bg-surface-container border border-outline-variant/50 focus:bg-surface focus:border-primary focus:outline-none rounded-2xl font-body-md text-body-md text-on-surface placeholder:text-outline transition-all shadow-sm"
          placeholder="Cari Nama Customer, Laptop, atau ID..."
          type="text"
          @input="onSearchInput"
        />
      </div>
      <div class="flex items-center gap-md w-full sm:w-auto">
        <input
          v-model="startDate"
          type="date"
          class="bg-surface-container border border-outline-variant/50 text-on-surface font-body-md text-body-md py-md px-lg rounded-2xl focus:border-2 focus:border-primary focus:bg-surface focus:outline-none transition-all shadow-sm"
        />
        <span class="text-outline font-label-bold">s/d</span>
        <input
          v-model="endDate"
          type="date"
          class="bg-surface-container border border-outline-variant/50 text-on-surface font-body-md text-body-md py-md px-lg rounded-2xl focus:border-2 focus:border-primary focus:bg-surface focus:outline-none transition-all shadow-sm"
        />
        <button
          @click="applyFilters"
          class="flex items-center gap-sm px-lg py-md bg-primary text-on-primary font-label-bold text-label-bold rounded-full hover:bg-primary-container transition-all shadow-sm"
        >
          <span class="material-symbols-outlined text-[18px]">filter_list</span>
          Filter
        </button>
        <button
          v-if="hasActiveFilters"
          @click="resetFilters"
          class="flex items-center gap-sm px-lg py-md border border-outline-variant rounded-full text-on-surface font-label-bold text-label-bold hover:bg-surface-container transition-colors"
        >
          <span class="material-symbols-outlined text-[18px]">close</span>
          Reset
        </button>
      </div>
    </div>

    <!-- Data Table Card -->
    <div
      class="bg-surface rounded-[24px] shadow-[0_8px_40px_rgba(0,0,0,0.03)] border border-outline-variant/30 overflow-hidden min-h-[400px] flex flex-col"
    >
      <div v-if="loading" class="flex-1 flex flex-col items-center justify-center p-xl gap-md">
        <span class="material-symbols-outlined text-[48px] text-primary animate-spin">sync</span>
        <p class="font-label-bold text-outline">Memuat data riwayat...</p>
      </div>

      <div
        v-else-if="loadError"
        class="flex-1 flex flex-col items-center justify-center p-xl gap-md"
      >
        <span class="material-symbols-outlined text-[64px] text-error opacity-50">cloud_off</span>
        <p class="font-label-bold text-error">{{ loadError }}</p>
        <button
          @click="fetchHistory(1)"
          class="flex items-center gap-sm px-lg py-md bg-primary text-on-primary font-label-bold text-label-bold rounded-full hover:bg-primary-container transition-all shadow-sm mt-sm"
        >
          <span class="material-symbols-outlined text-[18px]">refresh</span>
          Muat Ulang
        </button>
      </div>

      <div
        v-else-if="filteredData.length === 0"
        class="flex-1 flex flex-col items-center justify-center p-xl gap-md"
      >
        <span class="material-symbols-outlined text-[64px] text-outline opacity-30"
          >history_off</span
        >
        <p class="font-label-bold text-outline">Tidak ada riwayat penilaian ditemukan.</p>
      </div>

      <div v-else class="overflow-x-auto text-on-surface">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-surface-container/50 border-b border-outline-variant/30">
              <th
                class="py-md px-lg font-label-bold text-label-bold text-primary uppercase tracking-wider text-[12px] whitespace-nowrap"
              >
                ID
              </th>
              <th
                class="py-md px-lg font-label-bold text-label-bold text-primary uppercase tracking-wider text-[12px] whitespace-nowrap"
              >
                Customer
              </th>
              <th
                class="py-md px-lg font-label-bold text-label-bold text-primary uppercase tracking-wider text-[12px] whitespace-nowrap"
              >
                Tanggal
              </th>
              <th
                class="py-md px-lg font-label-bold text-label-bold text-primary uppercase tracking-wider text-[12px] whitespace-nowrap"
              >
                Model Laptop
              </th>
              <th
                class="py-md px-lg font-label-bold text-label-bold text-primary uppercase tracking-wider text-[12px] whitespace-nowrap"
              >
                Skor
              </th>
              <th
                class="py-md px-lg font-label-bold text-label-bold text-primary uppercase tracking-wider text-[12px] whitespace-nowrap text-center"
              >
                Status
              </th>
              <th
                class="py-md px-lg font-label-bold text-label-bold text-primary uppercase tracking-wider text-[12px] whitespace-nowrap"
              >
                Estimasi Harga
              </th>
              <th
                class="py-md px-lg font-label-bold text-label-bold text-primary uppercase tracking-wider text-[12px] whitespace-nowrap text-right"
              >
                Aksi
              </th>
            </tr>
          </thead>
          <tbody class="font-body-md text-body-md">
            <tr
              v-for="item in filteredData"
              :key="item.id"
              class="border-b border-outline-variant/20 hover:bg-surface-container-low transition-colors"
            >
              <td class="py-lg px-lg font-bold text-primary">#{{ item.id }}</td>
              <td class="py-lg px-lg">
                <div class="flex items-center gap-xs">
                  <span class="material-symbols-outlined text-[16px] text-outline">person</span>
                  <span class="font-bold text-on-surface">{{ item.customer_name }}</span>
                </div>
              </td>
              <td class="py-lg px-lg text-on-surface-variant">{{ formatDate(item.created_at) }}</td>
              <td class="py-lg px-lg">
                <div class="flex flex-col">
                  <span class="font-bold">{{ item.laptop_name }}</span>
                  <span class="font-caption text-caption text-outline">
                    {{ item.processor_input }} • LCD {{ item.lcd_input }}% • Baterai
                    {{ item.battery_input }}%{{
                      item.ram_input !== undefined ? ' • RAM ' + item.ram_input + 'GB' : ''
                    }}
                  </span>
                </div>
              </td>
              <td class="py-lg px-lg">
                <div class="flex items-center gap-md">
                  <span class="inline-flex items-center gap-xs font-bold text-on-surface">
                    {{ item.final_score }}
                  </span>
                  <div class="w-16 h-1.5 bg-surface-container-high rounded-full overflow-hidden">
                    <div
                      class="h-full rounded-full"
                      :class="statusTone(item.status).bar"
                      :style="{ width: item.final_score + '%' }"
                    ></div>
                  </div>
                </div>
              </td>
              <td class="py-lg px-lg text-center">
                <span
                  class="inline-flex items-center justify-center px-md py-1 rounded-full font-label-bold text-[12px] uppercase tracking-wide"
                  :class="statusTone(item.status).badge"
                >
                  {{ item.status }}
                </span>
              </td>
              <td class="py-lg px-lg font-bold text-primary whitespace-nowrap">
                {{ formatCurrency(item.estimated_price) }}
              </td>
              <td class="py-lg px-lg text-right">
                <div class="flex items-center justify-end gap-md">
                  <button
                    @click="handleViewDetail(item)"
                    class="text-primary hover:text-primary-container font-label-bold text-label-bold inline-flex items-center gap-xs transition-colors group"
                  >
                    Lihat Detail
                    <span
                      class="material-symbols-outlined text-[18px] transition-transform group-hover:translate-x-1"
                      >chevron_right</span
                    >
                  </button>
                  <button
                    @click="handleDelete(item.id)"
                    class="text-error hover:text-error/80 transition-colors p-1"
                    title="Hapus Riwayat"
                  >
                    <span class="material-symbols-outlined text-[20px]">delete</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <PaginationControl :pagination="pagination" label="penilaian" :loading="loading" @page="fetchHistory" />


      <!-- Detail Modal -->
      <div
        v-if="isDetailModalOpen && selectedItem"
        class="fixed inset-0 z-50 flex items-center justify-center p-md sm:p-lg"
      >
        <!-- Backdrop -->
        <div
          class="absolute inset-0 bg-primary/40 backdrop-blur-sm transition-opacity"
          @click="isDetailModalOpen = false"
        ></div>

        <!-- Modal Content -->
        <div
          class="relative w-full max-w-2xl bg-surface rounded-[32px] shadow-2xl border border-outline-variant/30 overflow-hidden animate-in fade-in zoom-in duration-300"
        >
          <!-- Modal Header -->
          <div
            class="px-xl py-lg border-b border-outline-variant/30 flex items-center justify-between bg-surface-container/30"
          >
            <div class="flex items-center gap-md">
              <div class="bg-primary-fixed/30 p-2 rounded-2xl">
                <span class="material-symbols-outlined text-primary">analytics</span>
              </div>
              <div>
                <h3 class="font-h3 text-h3 text-primary">Detail Penilaian</h3>
                <p class="font-caption text-caption text-outline">
                  ID: #{{ selectedItem.id }} • {{ formatDate(selectedItem.created_at) }}
                </p>
              </div>
            </div>
            <button
              @click="isDetailModalOpen = false"
              class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-surface-container-high transition-colors"
            >
              <span class="material-symbols-outlined text-on-surface">close</span>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-xl space-y-xl overflow-y-auto max-h-[70vh]">
            <!-- Overview Section -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-md">
              <div
                class="bg-surface-container-low p-lg rounded-[24px] border border-outline-variant/20"
              >
                <p class="font-label-bold text-[12px] text-outline uppercase tracking-wider mb-xs">
                  Nama Customer
                </p>
                <p class="font-h3 text-primary font-bold">{{ selectedItem.customer_name }}</p>
              </div>
              <div
                class="bg-surface-container-low p-lg rounded-[24px] border border-outline-variant/20"
              >
                <p class="font-label-bold text-[12px] text-outline uppercase tracking-wider mb-xs">
                  Nama Perangkat
                </p>
                <p class="font-h3 text-primary font-bold">{{ selectedItem.laptop_name }}</p>
              </div>
              <div
                class="bg-surface-container-low p-lg rounded-[24px] border border-outline-variant/20 flex flex-col items-center justify-center text-center"
              >
                <p class="font-label-bold text-[12px] text-outline uppercase tracking-wider mb-xs">
                  Hasil Kelayakan
                </p>
                <span
                  class="inline-flex items-center px-lg py-1.5 rounded-full font-h3 text-h3 uppercase tracking-wide"
                  :class="statusTone(selectedItem.status).badge"
                >
                  {{ selectedItem.status }}
                </span>
              </div>
            </div>

            <!-- Price Detail -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-md">
              <div
                class="bg-surface-container-low p-lg rounded-[24px] border border-outline-variant/20"
              >
                <p class="font-label-bold text-[12px] text-outline uppercase tracking-wider mb-xs">
                  Harga Pasaran
                </p>
                <p class="font-h3 text-primary font-bold">
                  {{ formatCurrency(selectedItem.market_price) }}
                </p>
              </div>
              <div
                class="bg-surface-container-low p-lg rounded-[24px] border border-outline-variant/20"
              >
                <p class="font-label-bold text-[12px] text-outline uppercase tracking-wider mb-xs">
                  Estimasi Harga
                </p>
                <p class="font-h3 text-primary font-bold">
                  {{ formatCurrency(selectedItem.estimated_price) }}
                </p>
              </div>
            </div>

            <!-- Metrics Detail -->
            <div class="space-y-lg">
              <h4 class="font-label-bold text-primary flex items-center gap-sm">
                <span class="material-symbols-outlined text-[20px]">equalizer</span>
                Parameter Penilaian
              </h4>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-xl">
                <!-- LCD -->
                <div class="space-y-sm">
                  <div class="flex justify-between font-label-bold text-on-surface-variant">
                    <span>Kondisi LCD</span>
                    <span class="text-primary">{{ selectedItem.lcd_input }}%</span>
                  </div>
                  <div class="h-2.5 bg-surface-container-high rounded-full overflow-hidden">
                    <div
                      class="h-full bg-primary rounded-full"
                      :style="{ width: selectedItem.lcd_input + '%' }"
                    ></div>
                  </div>
                </div>
                <!-- Baterai -->
                <div class="space-y-sm">
                  <div class="flex justify-between font-label-bold text-on-surface-variant">
                    <span>Kesehatan Baterai</span>
                    <span class="text-primary">{{ selectedItem.battery_input }}%</span>
                  </div>
                  <div class="h-2.5 bg-surface-container-high rounded-full overflow-hidden">
                    <div
                      class="h-full bg-primary rounded-full"
                      :style="{ width: selectedItem.battery_input + '%' }"
                    ></div>
                  </div>
                </div>
                <!-- Processor -->
                <div class="space-y-sm">
                  <div class="flex justify-between font-label-bold text-on-surface-variant">
                    <span>Processor</span>
                    <span class="text-primary">{{ selectedItem.processor_input }}</span>
                  </div>
                  <div class="h-2.5 bg-surface-container-high rounded-full overflow-hidden">
                    <div class="h-full bg-primary rounded-full w-full"></div>
                  </div>
                </div>
                <!-- Keyboard -->
                <div class="space-y-sm">
                  <div class="flex justify-between font-label-bold text-on-surface-variant">
                    <span>Fungsi Keyboard</span>
                    <span class="text-primary">{{ selectedItem.keyboard_input }}%</span>
                  </div>
                  <div class="h-2.5 bg-surface-container-high rounded-full overflow-hidden">
                    <div
                      class="h-full bg-primary rounded-full"
                      :style="{ width: selectedItem.keyboard_input + '%' }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- RAM Metric (if available) -->
            <div v-if="selectedItem.ram_input !== undefined" class="mt-lg space-y-sm">
              <div class="flex justify-between font-label-bold text-on-surface-variant">
                <span>Kapasitas RAM</span>
                <span class="text-primary">{{ selectedItem.ram_input }} GB</span>
              </div>
              <div class="h-2.5 bg-surface-container-high rounded-full overflow-hidden">
                <div
                  class="h-full bg-primary rounded-full"
                  :style="{ width: Math.min(100, (selectedItem.ram_input / 32) * 100) + '%' }"
                ></div>
              </div>
            </div>

            <!-- Description -->
            <div v-if="selectedItem.description" class="space-y-sm">
              <h4 class="font-label-bold text-primary flex items-center gap-sm">
                <span class="material-symbols-outlined text-[20px]">description</span>
                Deskripsi / Catatan Perangkat
              </h4>
              <div
                class="bg-surface-container/50 border border-outline-variant/20 p-md rounded-[20px]"
              >
                <p class="font-body-md text-on-surface-variant leading-relaxed">
                  {{ selectedItem.description }}
                </p>
              </div>
            </div>

            <!-- AI Recommendation (Gemini) -->
            <div
              v-if="
                selectedItem.ai_conclusion &&
                selectedItem.ai_conclusion !== 'tidak ada catatan tambahan'
              "
              class="space-y-sm"
            >
              <h4 class="font-label-bold text-primary flex items-center gap-sm">
                <span class="material-symbols-outlined text-[20px]">smart_toy</span>
                Hasil Analisis
              </h4>
              <div
                class="bg-primary/[0.04] border border-primary/10 p-md rounded-[20px] shadow-[0_2px_10px_rgba(0,0,0,0.01)] relative overflow-hidden"
              >
                <p class="font-body-md text-on-surface font-medium leading-relaxed">
                  {{ selectedItem.ai_conclusion }}
                </p>
                <p
                  v-if="selectedItem.ai_warning"
                  class="mt-md px-md py-sm bg-red-50 border border-red-200 rounded-xl text-red-600 font-semibold text-sm leading-relaxed"
                >
                  {{ selectedItem.ai_warning }}
                </p>
              </div>
            </div>

            <!-- Uploaded Images -->
            <div v-if="selectedItem.images && selectedItem.images.length > 0" class="space-y-sm">
              <h4 class="font-label-bold text-primary flex items-center gap-sm">
                <span class="material-symbols-outlined text-[20px]">photo_library</span>
                Foto Laptop
              </h4>
              <div class="flex flex-wrap gap-md">
                <div
                  v-for="img in selectedItem.images"
                  :key="img.id"
                  class="w-32 h-32 rounded-2xl overflow-hidden border border-outline-variant/30 shadow-sm"
                >
                  <img
                    :src="getImageUrl(img.image_path)"
                    alt="Foto laptop"
                    class="w-full h-full object-cover"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div
            class="px-xl py-lg bg-surface-container/30 border-t border-outline-variant/30 flex justify-end gap-md"
          >
            <button
              class="px-xl py-md border border-outline-variant/50 text-primary font-label-bold rounded-full hover:bg-surface-container transition-all flex items-center gap-sm"
              @click="handleDownloadPDF(selectedItem)"
            >
              <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
              Download PDF
            </button>
            <button
              @click="isDetailModalOpen = false"
              class="px-xl py-md bg-primary text-on-primary font-label-bold rounded-full hover:bg-primary-container transition-all shadow-md active:scale-95"
            >
              Tutup Detail
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { evaluationService, type EvaluationData, type ApiError } from '@/services/evaluation'
import { renderReportToCanvas, saveCanvasAsPDF } from '@/utils/assessment'
import { getImageUrl } from '@/composables/useApi'
import Swal from 'sweetalert2'
import PaginationControl from '@/components/ui/pagination-control.vue'

defineOptions({ name: 'AssessmentHistory' })

const { getAllAssessments, getAssessmentById, deleteAssessment } = evaluationService()

type AssessmentItem = EvaluationData

type AssessmentHistoryResponse = {
  data: AssessmentItem[]
  current_page: number
  last_page: number
  total: number
  from: number
  to: number
}

const historyData = ref<AssessmentItem[]>([])
const loading = ref(true)
const loadError = ref<string | null>(null)
const searchQuery = ref('')
const startDate = ref('')
const endDate = ref('')
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  from: 0,
  to: 0,
})

let searchTimeout: ReturnType<typeof setTimeout> | null = null

const hasActiveFilters = computed(() => {
  return searchQuery.value !== '' || startDate.value !== '' || endDate.value !== ''
})

// Modal states
const isDetailModalOpen = ref(false)
const selectedItem = ref<AssessmentItem | null>(null)

const fetchHistory = async (page = 1) => {
  loading.value = true
  loadError.value = null
  try {
    const filters: { search?: string; start_date?: string; end_date?: string } = {}
    if (searchQuery.value.trim()) filters.search = searchQuery.value.trim()
    if (startDate.value) filters.start_date = startDate.value
    if (endDate.value) filters.end_date = endDate.value
    const response = (await getAllAssessments(page, filters)) as AssessmentHistoryResponse
    historyData.value = response.data
    pagination.value = {
      current_page: response.current_page,
      last_page: response.last_page,
      total: response.total,
      from: response.from,
      to: response.to,
    }
  } catch (error) {
    const apiError = error as ApiError
    console.error('Gagal mengambil data riwayat:', apiError.message)
    historyData.value = []
    if (apiError.status === 0) {
      loadError.value = 'Tidak dapat terhubung ke server. Periksa koneksi internet dan pastikan backend berjalan.'
    } else if (apiError.status === 500) {
      loadError.value = 'Terjadi kesalahan pada server. Silakan coba lagi nanti.'
    } else {
      loadError.value = apiError.message
    }
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  pagination.value.current_page = 1
  fetchHistory(1)
}

const resetFilters = () => {
  searchQuery.value = ''
  startDate.value = ''
  endDate.value = ''
  pagination.value.current_page = 1
  fetchHistory(1)
}

const onSearchInput = () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    fetchHistory(1)
  }, 400)
}

const filteredData = computed(() => {
  if (!searchQuery.value) return historyData.value
  const query = searchQuery.value.toLowerCase()
  return historyData.value.filter(
    (item) =>
      item.customer_name.toLowerCase().includes(query) ||
      item.laptop_name.toLowerCase().includes(query) ||
      item.id.toString().includes(query) ||
      item.status.toLowerCase().includes(query),
  )
})

const detailLoading = ref(false)

const handleViewDetail = async (item: AssessmentItem) => {
  selectedItem.value = item
  isDetailModalOpen.value = true

  detailLoading.value = true
  try {
    const detail = await getAssessmentById(item.id)
    selectedItem.value = detail
  } catch (error) {
    const apiError = error as ApiError
    console.error('Gagal memuat detail:', apiError.message)
    if (apiError.status === 404) {
      Swal.fire({
        title: 'Data Tidak Ditemukan',
        text: 'Data penilaian ini sudah tidak tersedia.',
        icon: 'error',
        background: '#faf9fd',
        customClass: { popup: 'rounded-[24px]' },
      })
    } else if (apiError.status === 0) {
      Swal.fire({
        title: 'Koneksi Gagal',
        text: 'Tidak dapat memuat detail. Pastikan backend berjalan.',
        icon: 'error',
        background: '#faf9fd',
        customClass: { popup: 'rounded-[24px]' },
      })
    } else {
      Swal.fire({
        title: 'Gagal Memuat Detail',
        text: apiError.message,
        icon: 'error',
        background: '#faf9fd',
        customClass: { popup: 'rounded-[24px]' },
      })
    }
    isDetailModalOpen.value = false
  } finally {
    detailLoading.value = false
  }
}

const handleDelete = async (id: number) => {
  const result = await Swal.fire({
    title: 'Hapus Riwayat?',
    text: 'Data yang dihapus tidak dapat dikembalikan!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ba1a1a', // Error color
    cancelButtonColor: '#74777f', // Outline color
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal',
    background: '#faf9fd',
    color: '#1a1c1e',
    customClass: {
      popup: 'rounded-[24px]',
      confirmButton: 'rounded-full px-lg py-md font-label-bold',
      cancelButton: 'rounded-full px-lg py-md font-label-bold',
    },
  })

  if (result.isConfirmed) {
    try {
      await deleteAssessment(id)
      Swal.fire({
        title: 'Terhapus!',
        text: 'Data penilaian telah dihapus.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
        background: '#faf9fd',
        customClass: {
          popup: 'rounded-[24px]',
        },
      })
      fetchHistory(pagination.value.current_page)
    } catch (error) {
      const apiError = error as ApiError
      console.error('Gagal menghapus data:', apiError.message)
      if (apiError.status === 0) {
        Swal.fire({
          title: 'Koneksi Gagal',
          text: 'Tidak dapat menghubungi server. Periksa koneksi Anda.',
          icon: 'error',
          background: '#faf9fd',
          customClass: { popup: 'rounded-[24px]' },
        })
      } else if (apiError.status === 404) {
        Swal.fire({
          title: 'Data Tidak Ditemukan',
          text: 'Data yang ingin dihapus sudah tidak tersedia.',
          icon: 'error',
          background: '#faf9fd',
          customClass: { popup: 'rounded-[24px]' },
        })
        fetchHistory(pagination.value.current_page)
      } else {
        Swal.fire({
          title: 'Gagal Menghapus',
          text: apiError.message,
          icon: 'error',
          background: '#faf9fd',
          customClass: { popup: 'rounded-[24px]' },
        })
      }
    }
  }
}

const handleDownloadPDF = async (item: AssessmentItem) => {
  Swal.fire({
    title: 'Menyiapkan pratinjau...',
    html: 'Harap tunggu sebentar.',
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading(),
    background: '#faf9fd',
    customClass: { popup: 'rounded-[24px]' },
  })

  try {
    const canvas = await renderReportToCanvas(item)
    const dataUrl = canvas.toDataURL('image/png')
    Swal.close()

    const confirmed = await Swal.fire({
      title: 'Pratinjau Laporan',
      html: `<img src="${dataUrl}" style="width:100%;max-width:760px;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,0.08);display:block;" />`,
      showCancelButton: true,
      confirmButtonText: 'Download PDF',
      cancelButtonText: 'Tutup',
      confirmButtonColor: '#002045',
      cancelButtonColor: '#74777f',
      background: '#faf9fd',
      color: '#1a1c1e',
      width: '820px',
      customClass: {
        popup: 'rounded-[24px]',
        confirmButton: 'rounded-full px-lg py-md font-label-bold',
        cancelButton: 'rounded-full px-lg py-md font-label-bold',
      },
    })

    if (!confirmed.isConfirmed) return

    await saveCanvasAsPDF(canvas, item.laptop_name, item.id)
    Swal.fire({
      title: 'PDF Berhasil diunduh!',
      icon: 'success',
      timer: 2000,
      showConfirmButton: false,
      background: '#faf9fd',
      customClass: { popup: 'rounded-[24px]' },
    })
  } catch (error) {
    console.error('Gagal mengunduh PDF:', error)
    Swal.close()
    Swal.fire({
      title: 'Gagal Mengunduh PDF',
      text: error instanceof Error
        ? error.message
        : 'Terjadi kesalahan saat menghasilkan PDF. Silakan coba lagi.',
      icon: 'error',
      background: '#faf9fd',
      customClass: { popup: 'rounded-[24px]' },
    })
  }
}

const formatDate = (dateString: string) => {
  const options: Intl.DateTimeFormatOptions = {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }
  return new Date(dateString).toLocaleDateString('id-ID', options)
}

const formatCurrency = (value?: number | null) => {
  if (value === null || value === undefined) return '-'

  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value ?? 0)
}

const statusTone = (status: string) => {
  const s = status.toLowerCase()
  const goodKeywords = ['bagus', 'baik', 'layak']
  const mediumKeywords = ['sedang', 'cukup', 'pertimbangan', 'normal']
  const badKeywords = ['buruk', 'kurang', 'tidak layak', 'tidak bagus']

  const matches = (keywords: string[]) => keywords.some((keyword) => s.includes(keyword))

  if (matches(mediumKeywords)) {
    return {
      badge: 'bg-amber-100 text-amber-700',
      dot: 'bg-amber-500',
      bar: 'bg-amber-500',
    }
  }

  if (matches(badKeywords)) {
    return {
      badge: 'bg-rose-100 text-rose-700',
      dot: 'bg-rose-500',
      bar: 'bg-rose-500',
    }
  }

  if (matches(goodKeywords) && !s.includes('tidak')) {
    return {
      badge: 'bg-emerald-100 text-emerald-700',
      dot: 'bg-emerald-500',
      bar: 'bg-emerald-500',
    }
  }

  return {
    badge: 'bg-surface-variant text-on-surface-variant',
    dot: 'bg-outline',
    bar: 'bg-outline',
  }
}

onMounted(() => {
  fetchHistory()
})
</script>
