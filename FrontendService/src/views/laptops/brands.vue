<template>
  <section class="space-y-xl">
    <div class="flex flex-wrap items-end justify-between gap-md"><div><h1 class="font-h1 text-h1 text-primary">Brand Laptop</h1><p class="mt-sm text-on-surface-variant">Kelola daftar brand laptop.</p></div><button class="rounded-full bg-primary px-lg py-md font-bold text-white" @click="openCreate">Tambah Brand</button></div>
    <form v-if="formOpen" class="flex flex-col gap-md rounded-[24px] bg-surface p-xl shadow-sm sm:flex-row" @submit.prevent="save">
      <input v-model="name" required class="min-w-0 flex-1 rounded-2xl border border-outline-variant/50 bg-surface-container px-lg py-md" placeholder="Nama brand" />
      <button class="rounded-full bg-primary px-lg py-md font-bold text-white">{{ editing ? 'Simpan Perubahan' : 'Simpan' }}</button>
    </form>
    <div class="overflow-x-auto rounded-[24px] border border-outline-variant/30 bg-surface shadow-[0_8px_40px_rgba(0,0,0,0.03)]"><table class="w-full min-w-[560px] text-left"><thead><tr class="border-b border-outline-variant/30 bg-surface-container/50"><th class="px-lg py-md text-[12px] uppercase tracking-wider text-primary">Nama Brand</th><th class="px-lg py-md text-[12px] uppercase tracking-wider text-primary">Jumlah Model</th><th class="px-lg py-md text-center text-[12px] uppercase tracking-wider text-primary">Aksi</th></tr></thead><tbody><tr v-for="brand in brands" :key="brand.id" class="border-b border-outline-variant/20 last:border-0"><td class="px-lg py-md font-bold text-primary">{{ brand.name }}</td><td class="px-lg py-md">{{ brand.laptops_count ?? 0 }} model</td><td class="px-lg py-md"><div class="flex justify-center gap-md"><button class="font-bold text-primary hover:underline" @click="edit(brand)">Edit</button><button class="font-bold text-error hover:underline" @click="remove(brand)">Arsipkan</button></div></td></tr></tbody></table><p v-if="!brands.length" class="p-xl text-center text-on-surface-variant">Belum ada brand.</p></div>
  </section>
</template>
<script setup lang="ts">
import { onMounted, ref } from 'vue'
import Swal from 'sweetalert2'
import type { LaptopBrand } from '@/constants/assessment'
import { evaluationService, type ApiError } from '@/services/evaluation'
defineOptions({ name: 'LaptopBrands' })
const { getLaptopBrands, createLaptopBrand, updateLaptopBrand, deleteLaptopBrand } = evaluationService()
const brands = ref<LaptopBrand[]>([]); const formOpen = ref(false); const editing = ref<LaptopBrand | null>(null); const name = ref('')
const load = async () => { brands.value = await getLaptopBrands() }
const openCreate = () => { editing.value = null; name.value = ''; formOpen.value = true }
const edit = (brand: LaptopBrand) => { editing.value = brand; name.value = brand.name; formOpen.value = true }
const save = async () => { try { const brand = editing.value ? await updateLaptopBrand(editing.value.id, name.value.trim()) : await createLaptopBrand(name.value.trim()); const index = brands.value.findIndex((item) => item.id === brand.id); if (index >= 0) brands.value[index] = brand; else brands.value.push(brand); formOpen.value = false; await Swal.fire({ title: 'Berhasil', text: 'Brand tersimpan.', icon: 'success', timer: 1400, showConfirmButton: false }) } catch (error) { await Swal.fire({ title: 'Tidak dapat menyimpan', text: (error as ApiError).message, icon: 'error' }) } }
const remove = async (brand: LaptopBrand) => { const result = await Swal.fire({ title: 'Arsipkan brand?', text: brand.name, icon: 'warning', showCancelButton: true, confirmButtonText: 'Arsipkan', cancelButtonText: 'Batal' }); if (!result.isConfirmed) return; await deleteLaptopBrand(brand.id); brands.value = brands.value.filter((item) => item.id !== brand.id) }
onMounted(load)
</script>
