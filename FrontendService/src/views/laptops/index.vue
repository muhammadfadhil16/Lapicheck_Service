<template>
  <section class="space-y-xl">
    <div class="flex flex-wrap items-end justify-between gap-md">
      <div>
        <h1 class="font-h1 text-h1 text-primary">Data Laptop</h1>
        <p class="mt-sm text-on-surface-variant">Kelola model laptop dan spesifikasinya.</p>
      </div>
      <button class="rounded-full bg-primary px-lg py-md font-bold text-white" @click="startCreate">
        Tambah Data Laptop
      </button>
    </div>
    <Modal
      :open="showForm"
      :title="editing ? 'Edit Data Laptop' : 'Tambah Data Laptop'"
      @close="showForm = false"
    >
      <form class="grid gap-md md:grid-cols-2" @submit.prevent="save">
        <select v-model="form.brand_name" required class="field-input">
          <option value="">Pilih brand</option>
          <option v-for="brand in brands" :key="brand.id" :value="brand.name">
            {{ brand.name }}
          </option>
        </select>
        <input v-model="form.model_name" required class="field-input" placeholder="Model laptop" />
        <input v-model="form.processor_name" required class="field-input" placeholder="Processor" />
        <input
          v-model.number="form.benchmark_score"
          required
          min="0"
          type="number"
          class="field-input"
          placeholder="Benchmark"
        />
        <button
          :disabled="saving"
          class="inline-flex items-center justify-center gap-sm rounded-full bg-primary px-xl py-md font-bold text-white disabled:opacity-50 md:col-span-2"
        >
          <span v-if="saving" class="material-symbols-outlined animate-spin text-[18px]">sync</span
          >{{ saving ? 'Menyimpan...' : 'Simpan' }}
        </button>
      </form>
    </Modal>
    <div
      class="flex flex-col gap-sm rounded-2xl bg-surface p-md shadow-sm sm:flex-row sm:items-center"
    >
      <label for="brand-filter" class="font-bold text-primary">Filter Brand</label
      ><select
        id="brand-filter"
        v-model="filterBrandId"
        class="field-input sm:flex-1"
        @change="loadLaptops(1)"
      >
        <option :value="null">Semua brand</option>
        <option v-for="brand in brands" :key="brand.id" :value="brand.id">
          {{ brand.name }}
        </option></select
      ><input
        v-model="searchQuery"
        class="field-input sm:flex-1"
        placeholder="Cari model, brand, atau processor..."
      />
    </div>
    <div class="table-shell">
      <div
        v-if="loading"
        class="flex min-h-[320px] flex-col items-center justify-center gap-md p-xl"
      >
        <span class="material-symbols-outlined animate-spin text-[48px] text-primary">sync</span>
        <p class="font-label-bold text-outline">Memuat data laptop...</p>
      </div>
      <table v-else class="min-w-[900px]">
        <thead>
          <tr>
            <th>Brand</th>
            <th>Model Laptop</th>
            <th>Processor</th>
            <th>Benchmark</th>
            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="laptop in filteredLaptops"
            :key="laptop.id"
            class="border-b border-outline-variant/20 last:border-0"
          >
            <td class="font-bold text-primary">{{ laptop.brand.name }}</td>
            <td>{{ laptop.model_name }}</td>
            <td class="text-on-surface-variant">{{ laptop.processor_name }}</td>
            <td>
              <span class="rounded-full bg-primary/10 px-sm py-xs font-bold text-primary">{{
                laptop.benchmark_score
              }}</span>
            </td>
            <td>
              <div class="flex justify-center gap-md">
                <button class="font-bold text-primary hover:underline" @click="startEdit(laptop)">
                  Edit</button
                ><button class="font-bold text-error hover:underline" @click="remove(laptop)">
                  Hapus
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <p
        v-if="!loading && !filteredLaptops.length"
        class="p-xl text-center text-on-surface-variant"
      >
        Belum ada data laptop.
      </p>
      <PaginationControl :pagination="pagination" label="laptop" :loading="loading" @page="loadLaptops" />
    </div>
  </section>
</template>
<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import Swal from 'sweetalert2'
import type { Laptop, LaptopBrand } from '@/constants/assessment'
import { evaluationService } from '@/services/evaluation'
import Modal from '@/components/ui/modal.vue'
import PaginationControl from '@/components/ui/pagination-control.vue'
defineOptions({ name: 'LaptopData' })
const { getLaptopBrands, getLaptopsPage, createLaptop, updateLaptop, deleteLaptop } =
  evaluationService()
const brands = ref<LaptopBrand[]>([])
const laptops = ref<Laptop[]>([])
const filterBrandId = ref<number | null>(null)
const searchQuery = ref('')
const showForm = ref(false)
const editing = ref<Laptop | null>(null)
const loading = ref(true)
const saving = ref(false)
const pagination = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })
const form = reactive({ brand_name: '', model_name: '', processor_name: '', benchmark_score: 0 })
const filteredLaptops = computed(() => {
  const query = searchQuery.value.toLowerCase().trim()
  if (!query) return laptops.value
  return laptops.value.filter((laptop) =>
    `${laptop.brand.name} ${laptop.model_name} ${laptop.processor_name} ${laptop.benchmark_score} ${laptop.category}`
      .toLowerCase()
      .includes(query),
  )
})
const loadLaptops = async (page = 1) => {
  loading.value = true
  try {
    const response = await getLaptopsPage(page, filterBrandId.value ?? undefined)
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
  Object.assign(form, { brand_name: '', model_name: '', processor_name: '', benchmark_score: 0 })
  showForm.value = true
}
const startEdit = (laptop: Laptop) => {
  editing.value = laptop
  Object.assign(form, {
    brand_name: laptop.brand.name,
    model_name: laptop.model_name,
    processor_name: laptop.processor_name,
    benchmark_score: laptop.benchmark_score,
  })
  showForm.value = true
}
const save = async () => {
  const brand = brands.value.find((item) => item.name === form.brand_name)
  if (!brand) return
  saving.value = true
  try {
    if (editing.value)
      await updateLaptop(editing.value.id, {
        brand_id: brand.id,
        model_name: form.model_name,
        processor_name: form.processor_name,
        benchmark_score: form.benchmark_score,
      })
    else await createLaptop(form)
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
onMounted(load)
</script>
