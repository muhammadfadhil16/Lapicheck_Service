<template>
  <div class="space-y-xl">
    <!-- Page Header -->
    <div class="space-y-sm">
      <h1 class="font-h1 text-h1 text-primary">Data Laptop</h1>
      <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl">
        Kelola master model laptop, prosesor, benchmark skor, dan referensi harga pasaran terkini.
      </p>
    </div>

    <!-- Action Buttons (Below Header, Above Filter) -->
    <div class="flex flex-wrap items-center justify-between gap-md">
      <div class="flex flex-wrap items-center gap-sm">
        <button
          type="button"
          class="inline-flex items-center gap-xs rounded-full bg-primary px-xl py-md font-label-bold text-label-bold text-on-primary shadow-sm hover:bg-primary-container transition-all"
          @click="startCreate"
        >
          <span class="material-symbols-outlined text-[18px]">add</span>
          Tambah Laptop
        </button>
        <button
          type="button"
          class="inline-flex items-center gap-xs rounded-full border border-primary/30 bg-primary/10 text-primary px-lg py-md font-label-bold text-label-bold hover:bg-primary/20 transition-all shadow-sm"
          title="Impor batch data dari file Excel/CSV"
          @click="showImportModal = true"
        >
          <span class="material-symbols-outlined text-[18px]">upload_file</span>
          Import Excel
        </button>
      </div>

      <div class="flex flex-wrap items-center gap-sm">
        <button
          type="button"
          class="inline-flex items-center gap-xs rounded-full border border-outline-variant/60 bg-surface px-lg py-md font-label-bold text-label-bold text-primary shadow-sm hover:bg-surface-container transition-all"
          title="Ekspor seluruh data laptop ke format Excel"
          @click="handleExport"
        >
          <span class="material-symbols-outlined text-[18px]">ios_share</span>
          Export
        </button>
        <button
          type="button"
          class="inline-flex items-center gap-xs rounded-full border border-outline-variant/60 bg-surface px-lg py-md font-label-bold text-label-bold text-primary shadow-sm hover:bg-surface-container transition-all"
          title="Unduh format spreadsheet untuk impor data"
          @click="handleDownloadTemplate"
        >
          <span class="material-symbols-outlined text-[18px]">file_download</span>
          Template
        </button>
      </div>
    </div>

    <!-- Modal Form Tambah/Edit Laptop -->
    <Modal
      :open="showForm"
      :title="editing ? 'Edit Data Laptop' : 'Tambah Data Laptop'"
      @close="showForm = false"
    >
      <form class="grid gap-md md:grid-cols-2" @submit.prevent="save">
        <div class="space-y-xs md:col-span-2">
          <label class="font-label-bold text-label-bold text-on-surface-variant text-xs uppercase tracking-wider">Brand Laptop *</label>
          <select v-model="form.brand_name" required class="w-full bg-surface-container border border-outline-variant/50 text-on-surface font-body-md text-body-md py-md px-lg rounded-2xl focus:border-2 focus:border-primary focus:bg-surface focus:outline-none transition-all shadow-sm">
            <option value="">Pilih Brand</option>
            <option v-for="brand in brands" :key="brand.id" :value="brand.name">
              {{ brand.name }}
            </option>
          </select>
        </div>

        <div class="space-y-xs">
          <label class="font-label-bold text-label-bold text-on-surface-variant text-xs uppercase tracking-wider">Model Laptop *</label>
          <input
            v-model="form.model_name"
            required
            class="w-full bg-surface-container border border-outline-variant/50 text-on-surface font-body-md text-body-md py-md px-lg rounded-2xl focus:border-2 focus:border-primary focus:bg-surface focus:outline-none transition-all shadow-sm"
            placeholder="Contoh: IdeaPad Slim 3 14IAU7"
          />
        </div>

        <div class="space-y-xs">
          <label class="font-label-bold text-label-bold text-on-surface-variant text-xs uppercase tracking-wider">Nama Processor *</label>
          <input
            v-model="form.processor_name"
            required
            class="w-full bg-surface-container border border-outline-variant/50 text-on-surface font-body-md text-body-md py-md px-lg rounded-2xl focus:border-2 focus:border-primary focus:bg-surface focus:outline-none transition-all shadow-sm"
            placeholder="Contoh: Intel Core i5-1235U"
          />
        </div>

        <div class="space-y-xs">
          <label class="font-label-bold text-label-bold text-on-surface-variant text-xs uppercase tracking-wider">Skor Benchmark *</label>
          <input
            v-model.number="form.benchmark_score"
            required
            min="0"
            type="number"
            class="w-full bg-surface-container border border-outline-variant/50 text-on-surface font-body-md text-body-md py-md px-lg rounded-2xl focus:border-2 focus:border-primary focus:bg-surface focus:outline-none transition-all shadow-sm"
            placeholder="Contoh: 12500"
          />
        </div>

        <div class="space-y-xs">
          <label class="font-label-bold text-label-bold text-on-surface-variant text-xs uppercase tracking-wider">Harga Pasaran Referensi (Rp)</label>
          <input
            v-model.number="form.market_price"
            min="0"
            type="number"
            class="w-full bg-surface-container border border-outline-variant/50 text-on-surface font-body-md text-body-md py-md px-lg rounded-2xl focus:border-2 focus:border-primary focus:bg-surface focus:outline-none transition-all shadow-sm"
            placeholder="Contoh: 6500000"
          />
          <p class="text-caption text-on-surface-variant">Bulan & tahun harga otomatis dicatat dari tanggal saat ini.</p>
        </div>

        <button
          :disabled="saving"
          class="inline-flex items-center justify-center gap-sm rounded-full bg-primary px-xl py-md font-label-bold text-label-bold text-on-primary disabled:opacity-50 md:col-span-2 mt-sm shadow-sm hover:bg-primary-container transition-all"
        >
          <span v-if="saving" class="material-symbols-outlined animate-spin text-[18px]">sync</span>
          {{ saving ? 'Menyimpan...' : 'Simpan Data Laptop' }}
        </button>
      </form>
    </Modal>

    <!-- Modal Import Excel (XLSX / CSV) -->
    <Modal
      :open="showImportModal"
      title="Import Data Laptop (Excel / CSV)"
      @close="closeImportModal"
    >
      <div class="space-y-lg">
        <p class="font-body-md text-body-md text-on-surface-variant">
          Unggah spreadsheet format <strong class="text-primary font-bold">.xlsx</strong>, <strong class="text-primary font-bold">.xls</strong>, atau <strong class="text-primary font-bold">.csv</strong> sesuai struktur kolom template.
        </p>

        <div
          class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-outline-variant/60 bg-surface-container/30 p-xl text-center hover:bg-surface-container/60 hover:border-primary/50 transition-all cursor-pointer"
          @click="fileInputRef?.click()"
          @dragover.prevent
          @drop.prevent="handleFileDrop"
        >
          <input
            ref="fileInputRef"
            type="file"
            accept=".xlsx,.xls,.csv"
            class="hidden"
            @change="handleFileSelected"
          />
          <span class="material-symbols-outlined text-[48px] text-primary mb-sm">upload_file</span>
          <p v-if="!selectedFile" class="font-label-bold text-label-bold text-on-surface">
            Klik atau seret file Excel/CSV ke sini
          </p>
          <div v-else class="space-y-xs">
            <p class="font-bold text-primary flex items-center justify-center gap-xs">
              <span class="material-symbols-outlined text-[18px]">description</span>
              {{ selectedFile.name }}
            </p>
            <p class="text-caption text-on-surface-variant">
              {{ (selectedFile.size / 1024).toFixed(1) }} KB
            </p>
          </div>
        </div>

        <!-- Import Summary / Error List -->
        <div v-if="importResult && importResult.errors.length > 0" class="rounded-2xl bg-error-container/40 border border-error/30 p-md text-error space-y-xs max-h-40 overflow-y-auto">
          <p class="font-label-bold text-label-bold text-xs">Peringatan baris tidak valid:</p>
          <ul class="list-disc list-inside text-xs space-y-1">
            <li v-for="(err, idx) in importResult.errors" :key="idx">
              Baris {{ err.row }}: {{ err.message }}
            </li>
          </ul>
        </div>

        <div class="flex items-center justify-between pt-sm border-t border-outline-variant/30">
          <button
            type="button"
            class="font-label-bold text-caption text-primary hover:underline"
            @click="handleDownloadTemplate"
          >
            Unduh format template
          </button>
          <div class="flex items-center gap-sm">
            <button
              type="button"
              class="rounded-full px-lg py-sm font-label-bold text-label-bold text-on-surface-variant hover:bg-surface-container transition-colors"
              @click="closeImportModal"
            >
              Batal
            </button>
            <button
              :disabled="!selectedFile || importing"
              class="inline-flex items-center gap-xs rounded-full bg-primary px-xl py-sm font-label-bold text-label-bold text-on-primary disabled:opacity-50 hover:bg-primary-container shadow-sm transition-all"
              @click="submitImport"
            >
              <span v-if="importing" class="material-symbols-outlined animate-spin text-[18px]">sync</span>
              {{ importing ? 'Mengimpor...' : 'Mulai Import' }}
            </button>
          </div>
        </div>
      </div>
    </Modal>

    <!-- Controls: Search & Filter -->
    <div class="flex flex-col sm:flex-row gap-md items-center justify-between">
      <div class="relative w-full sm:max-w-md">
        <span
          class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-outline"
        >search</span>
        <input
          v-model="searchQuery"
          class="w-full pl-[44px] pr-md py-md bg-surface-container border border-outline-variant/50 focus:bg-surface focus:border-primary focus:outline-none rounded-2xl font-body-md text-body-md text-on-surface placeholder:text-outline transition-all shadow-sm"
          placeholder="Cari model, brand, atau processor..."
          type="text"
        />
      </div>
      <div class="flex items-center gap-md w-full sm:w-auto">
        <div class="relative w-full sm:w-56">
          <select
            id="brand-filter"
            v-model="filterBrandId"
            class="w-full bg-surface-container border border-outline-variant/50 text-on-surface font-body-md text-body-md py-md px-lg rounded-2xl focus:border-2 focus:border-primary focus:bg-surface focus:outline-none transition-all shadow-sm appearance-none cursor-pointer pr-10"
            @change="loadLaptops(1)"
          >
            <option :value="null">Semua Brand</option>
            <option v-for="brand in brands" :key="brand.id" :value="brand.id">
              {{ brand.name }}
            </option>
          </select>
          <span class="material-symbols-outlined absolute right-md top-1/2 -translate-y-1/2 text-outline pointer-events-none text-[20px]">
            expand_more
          </span>
        </div>
      </div>
    </div>

    <!-- Data Table Card -->
    <div
      class="bg-surface rounded-[24px] shadow-[0_8px_40px_rgba(0,0,0,0.03)] border border-outline-variant/30 overflow-hidden min-h-[400px] flex flex-col"
    >
      <div v-if="loading" class="flex-1 flex flex-col items-center justify-center p-xl gap-md">
        <span class="material-symbols-outlined text-[48px] text-primary animate-spin">sync</span>
        <p class="font-label-bold text-outline">Memuat data laptop...</p>
      </div>

      <div
        v-else-if="filteredLaptops.length === 0"
        class="flex-1 flex flex-col items-center justify-center p-xl gap-md"
      >
        <span class="material-symbols-outlined text-[64px] text-outline opacity-30">laptop_windows</span>
        <p class="font-label-bold text-outline">Tidak ada data laptop ditemukan.</p>
      </div>

      <div v-else class="overflow-x-auto text-on-surface">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-surface-container/50 border-b border-outline-variant/30">
              <th class="py-md px-lg font-label-bold text-label-bold text-primary uppercase tracking-wider text-[12px] whitespace-nowrap">
                Brand
              </th>
              <th class="py-md px-lg font-label-bold text-label-bold text-primary uppercase tracking-wider text-[12px] whitespace-nowrap">
                Model Laptop
              </th>
              <th class="py-md px-lg font-label-bold text-label-bold text-primary uppercase tracking-wider text-[12px] whitespace-nowrap">
                Processor
              </th>
              <th class="py-md px-lg font-label-bold text-label-bold text-primary uppercase tracking-wider text-[12px] whitespace-nowrap text-center">
                Skor Benchmark
              </th>
              <th class="py-md px-lg font-label-bold text-label-bold text-primary uppercase tracking-wider text-[12px] whitespace-nowrap">
                Harga Pasaran
              </th>
              <th class="py-md px-lg font-label-bold text-label-bold text-primary uppercase tracking-wider text-[12px] whitespace-nowrap">
                Periode Harga
              </th>
              <th class="py-md px-lg font-label-bold text-label-bold text-primary uppercase tracking-wider text-[12px] whitespace-nowrap text-right">
                Aksi
              </th>
            </tr>
          </thead>
          <tbody class="font-body-md text-body-md">
            <tr
              v-for="laptop in filteredLaptops"
              :key="laptop.id"
              class="border-b border-outline-variant/20 hover:bg-surface-container-low transition-colors"
            >
              <td class="py-lg px-lg font-bold text-primary">
                {{ laptop.brand?.name || '-' }}
              </td>
              <td class="py-lg px-lg font-bold text-on-surface">
                {{ laptop.model_name }}
              </td>
              <td class="py-lg px-lg text-on-surface-variant">
                {{ laptop.processor_name }}
              </td>
              <td class="py-lg px-lg text-center">
                <span class="inline-flex items-center justify-center px-md py-0.5 rounded-full font-bold text-[13px] bg-primary/10 text-primary">
                  {{ (laptop.benchmark_score || 0).toLocaleString('id-ID') }}
                </span>
              </td>
              <td class="py-lg px-lg font-bold text-primary whitespace-nowrap">
                {{ formatCurrency(laptop.market_price) }}
              </td>
              <td class="py-lg px-lg text-on-surface-variant text-body-md whitespace-nowrap">
                {{ formatPeriod(laptop.price_month, laptop.price_year) }}
              </td>
              <td class="py-lg px-lg text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-sm">
                  <button
                    class="inline-flex items-center gap-xs px-md py-1.5 rounded-full text-primary hover:bg-primary/10 font-label-bold text-label-bold text-[13px] transition-colors"
                    title="Edit Data Laptop"
                    @click="startEdit(laptop)"
                  >
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                    Edit
                  </button>
                  <button
                    class="inline-flex items-center justify-center w-8 h-8 rounded-full text-error hover:bg-error/10 transition-colors"
                    title="Hapus Laptop"
                    @click="remove(laptop)"
                  >
                    <span class="material-symbols-outlined text-[18px]">delete</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <PaginationControl :pagination="pagination" label="laptop" :loading="loading" @page="loadLaptops" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue'
import Swal from 'sweetalert2'
import type { Laptop, LaptopBrand } from '@/constants/assessment'
import { evaluationService } from '@/services/evaluation'
import Modal from '@/components/ui/modal.vue'
import PaginationControl from '@/components/ui/pagination-control.vue'

defineOptions({ name: 'LaptopData' })

const {
  getLaptopBrands,
  getLaptopsPage,
  createLaptop,
  updateLaptop,
  deleteLaptop,
  downloadLaptopTemplate,
  exportLaptops,
  importLaptops,
} = evaluationService()

const brands = ref<LaptopBrand[]>([])
const laptops = ref<Laptop[]>([])
const filterBrandId = ref<number | null>(null)
const searchQuery = ref('')
const showForm = ref(false)
const showImportModal = ref(false)
const editing = ref<Laptop | null>(null)
const loading = ref(true)
const saving = ref(false)
const importing = ref(false)
const selectedFile = ref<File | null>(null)
const fileInputRef = ref<HTMLInputElement | null>(null)
const importResult = ref<{
  total_rows: number
  imported_count: number
  updated_count: number
  errors: Array<{ row: number; message: string }>
} | null>(null)

const pagination = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })
const form = reactive({
  brand_name: '',
  model_name: '',
  processor_name: '',
  benchmark_score: 0,
  market_price: 0,
})

const filteredLaptops = computed(() => laptops.value)

let searchTimeout: ReturnType<typeof setTimeout>
watch(searchQuery, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadLaptops(1)
  }, 300)
})

const formatCurrency = (val?: number | null) => {
  if (val === null || val === undefined || val <= 0) return '-'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(val)
}

const formatPeriod = (month?: number, year?: number) => {
  if (!year) return '-'
  const monthNames = [
    'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
    'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
  ]
  const mStr = month && month >= 1 && month <= 12 ? monthNames[month - 1] : ''
  return mStr ? `${mStr} ${year}` : `${year}`
}

const loadLaptops = async (page = 1) => {
  loading.value = true
  try {
    const response = await getLaptopsPage(page, filterBrandId.value ?? undefined, searchQuery.value)
    laptops.value = Array.isArray(response.data) ? response.data : []
    pagination.value = {
      current_page: response.current_page ?? 1,
      last_page: response.last_page ?? 1,
      total: response.total ?? 0,
      from: response.from ?? 0,
      to: response.to ?? 0,
    }
  } finally {
    loading.value = false
  }
}

const load = async () => {
  brands.value = await getLaptopBrands()
  await loadLaptops(1)
}

const startCreate = () => {
  editing.value = null
  Object.assign(form, {
    brand_name: '',
    model_name: '',
    processor_name: '',
    benchmark_score: 0,
    market_price: 0,
  })
  showForm.value = true
}

const startEdit = (laptop: Laptop) => {
  editing.value = laptop
  Object.assign(form, {
    brand_name: laptop.brand.name,
    model_name: laptop.model_name,
    processor_name: laptop.processor_name,
    benchmark_score: laptop.benchmark_score,
    market_price: laptop.market_price ?? 0,
  })
  showForm.value = true
}

const save = async () => {
  const brand = brands.value.find((item) => item.name === form.brand_name)
  if (!brand) return
  saving.value = true
  try {
    if (editing.value) {
      await updateLaptop(editing.value.id, {
        brand_id: brand.id,
        model_name: form.model_name,
        processor_name: form.processor_name,
        benchmark_score: form.benchmark_score,
        market_price: form.market_price || 0,
      })
    } else {
      await createLaptop(form)
    }
    await loadLaptops()
    showForm.value = false
    await Swal.fire({
      title: 'Berhasil',
      text: 'Data laptop tersimpan.',
      icon: 'success',
      timer: 1400,
      showConfirmButton: false,
    })
  } finally {
    saving.value = false
  }
}

const remove = async (laptop: Laptop) => {
  const result = await Swal.fire({
    title: 'Hapus data laptop?',
    text: laptop.model_name,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Hapus',
    cancelButtonText: 'Batal',
  })
  if (!result.isConfirmed) return
  await deleteLaptop(laptop.id)
  await loadLaptops()
}

const handleDownloadTemplate = async () => {
  try {
    await downloadLaptopTemplate('xlsx')
  } catch (error) {
    Swal.fire('Gagal', 'Gagal mengunduh template.', 'error')
  }
}

const handleExport = async () => {
  try {
    await exportLaptops('xlsx', filterBrandId.value ?? undefined, searchQuery.value)
  } catch (error) {
    Swal.fire('Gagal', 'Gagal mengekspor data.', 'error')
  }
}

const handleFileSelected = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    selectedFile.value = target.files[0]
    importResult.value = null
  }
}

const handleFileDrop = (e: DragEvent) => {
  if (e.dataTransfer && e.dataTransfer.files[0]) {
    selectedFile.value = e.dataTransfer.files[0]
    importResult.value = null
  }
}

const closeImportModal = () => {
  showImportModal.value = false
  selectedFile.value = null
  importResult.value = null
  if (fileInputRef.value) fileInputRef.value.value = ''
}

const submitImport = async () => {
  if (!selectedFile.value) return
  importing.value = true
  try {
    const res = await importLaptops(selectedFile.value)
    importResult.value = res.data
    await loadLaptops(1)
    brands.value = await getLaptopBrands()

    if (res.data.errors && res.data.errors.length > 0) {
      await Swal.fire({
        title: 'Import Sebagian Berhasil',
        html: `<p>${res.data.imported_count} data baru ditambahkan, ${res.data.updated_count} diperbarui.</p><p class="text-error mt-2">${res.data.errors.length} baris dilewati karena format tidak sesuai.</p>`,
        icon: 'warning',
      })
    } else {
      closeImportModal()
      await Swal.fire({
        title: 'Berhasil',
        text: `Berhasil mengimpor ${res.data.imported_count} data baru dan memperbarui ${res.data.updated_count} data.`,
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
      })
    }
  } catch (error: any) {
    const msg = error?.response?.data?.message || 'Gagal memproses file import.'
    Swal.fire('Gagal Import', msg, 'error')
  } finally {
    importing.value = false
  }
}

onMounted(load)
</script>
