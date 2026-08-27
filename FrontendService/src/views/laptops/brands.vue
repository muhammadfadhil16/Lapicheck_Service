<template>
  <section class="space-y-xl">
    <div class="flex flex-wrap items-end justify-between gap-md">
      <div>
        <h1 class="font-h1 text-h1 text-primary">Brand Laptop</h1>
        <p class="mt-sm text-on-surface-variant">Kelola daftar brand laptop.</p>
      </div>
      <button class="rounded-full bg-primary px-lg py-md font-bold text-white" @click="openCreate">
        Tambah Brand
      </button>
    </div>
    <Modal
      :open="formOpen"
      :title="editing ? 'Edit Brand' : 'Tambah Brand'"
      @close="formOpen = false"
    >
      <form class="flex flex-col gap-md sm:flex-row" @submit.prevent="save">
        <input
          v-model="name"
          required
          class="field-input min-w-0 flex-1"
          placeholder="Nama brand"
        />
        <button
          :disabled="saving"
          class="inline-flex items-center justify-center gap-sm rounded-full bg-primary px-lg py-md font-bold text-white disabled:opacity-50"
        >
          <span v-if="saving" class="material-symbols-outlined animate-spin text-[18px]">sync</span
          >{{ saving ? 'Menyimpan...' : editing ? 'Simpan Perubahan' : 'Simpan' }}
        </button>
      </form>
    </Modal>
    <div class="flex items-center gap-md rounded-2xl bg-surface p-md shadow-sm">
      <label for="brand-search" class="font-bold text-primary">Cari Brand</label
      ><input
        id="brand-search"
        v-model="searchQuery"
        class="field-input"
        placeholder="Cari nama brand..."
      />
    </div>
    <div class="table-shell">
      <div
        v-if="loading"
        class="flex min-h-[320px] flex-col items-center justify-center gap-md p-xl"
      >
        <span class="material-symbols-outlined animate-spin text-[48px] text-primary">sync</span>
        <p class="font-label-bold text-outline">Memuat data brand...</p>
      </div>
      <table v-else class="min-w-[560px]">
        <thead>
          <tr>
            <th>Nama Brand</th>
            <th>Jumlah Model</th>
            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="brand in filteredBrands"
            :key="brand.id"
            class="border-b border-outline-variant/20 last:border-0"
          >
            <td class="font-bold text-primary">{{ brand.name }}</td>
            <td>{{ brand.laptops_count ?? 0 }} model</td>
            <td>
              <div class="flex justify-center gap-md">
                <button class="font-bold text-primary hover:underline" @click="edit(brand)">
                  Edit</button
                ><button class="font-bold text-error hover:underline" @click="remove(brand)">
                  Arsipkan
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-if="!loading && !filteredBrands.length" class="p-xl text-center text-on-surface-variant">
        Belum ada brand.
      </p>
      <PaginationControl :pagination="pagination" label="brand" :loading="loading" @page="load" />
    </div>
  </section>
</template>
<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import Swal from 'sweetalert2'
import type { LaptopBrand } from '@/constants/assessment'
import { evaluationService, type ApiError } from '@/services/evaluation'
import Modal from '@/components/ui/modal.vue'
import PaginationControl from '@/components/ui/pagination-control.vue'
defineOptions({ name: 'LaptopBrands' })
const { getLaptopBrandsPage, createLaptopBrand, updateLaptopBrand, deleteLaptopBrand } =
  evaluationService()
const brands = ref<LaptopBrand[]>([])
const searchQuery = ref('')
const formOpen = ref(false)
const editing = ref<LaptopBrand | null>(null)
const name = ref('')
const loading = ref(true)
const saving = ref(false)
const pagination = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 })
const filteredBrands = computed(() => {
  const query = searchQuery.value.toLowerCase().trim()
  return query
    ? brands.value.filter((brand) => brand.name.toLowerCase().includes(query))
    : brands.value
})
const load = async (page = 1) => {
  loading.value = true
  try {
    const response = await getLaptopBrandsPage(page)
    brands.value = response.data
    pagination.value = {
      current_page: response.current_page,
      last_page: response.last_page,
      total: response.total,
      from: response.from,
      to: response.to,
    }
  } finally {
    loading.value = false
  }
}
const openCreate = () => {
  editing.value = null
  name.value = ''
  formOpen.value = true
}
const edit = (brand: LaptopBrand) => {
  editing.value = brand
  name.value = brand.name
  formOpen.value = true
}
const save = async () => {
  saving.value = true
  try {
    await (editing.value
      ? updateLaptopBrand(editing.value.id, name.value.trim())
      : createLaptopBrand(name.value.trim()))
    await load(pagination.value.current_page)
    formOpen.value = false
    await Swal.fire({
      title: 'Berhasil',
      text: 'Brand tersimpan.',
      icon: 'success',
      timer: 1400,
      showConfirmButton: false,
    })
  } catch (error) {
    await Swal.fire({
      title: 'Tidak dapat menyimpan',
      text: (error as ApiError).message,
      icon: 'error',
    })
  } finally {
    saving.value = false
  }
}
const remove = async (brand: LaptopBrand) => {
  const result = await Swal.fire({
    title: 'Arsipkan brand?',
    text: brand.name,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Arsipkan',
    cancelButtonText: 'Batal',
  })
  if (!result.isConfirmed) return
  await deleteLaptopBrand(brand.id)
  brands.value = brands.value.filter((item) => item.id !== brand.id)
}
onMounted(load)
</script>
