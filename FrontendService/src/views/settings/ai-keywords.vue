<template>
  <section class="mx-auto flex w-full max-w-4xl flex-col gap-xl">
    <div>
      <p class="font-label-bold text-label-bold uppercase tracking-wider text-primary">
        Pengaturan sistem
      </p>
      <h1 class="mt-sm font-h1 text-h1 font-bold text-on-surface">Kosakata AI</h1>
      <p class="mt-sm max-w-2xl text-body-md text-on-surface-variant">
        Tambahkan kata yang biasa digunakan untuk menjelaskan komponen atau kondisi laptop. AI akan
        mengenalinya saat membaca catatan assessment.
      </p>
    </div>

    <div class="rounded-2xl border border-outline-variant/50 bg-surface p-lg shadow-sm sm:p-xl">
      <h2 class="font-h3 text-h3 font-bold text-on-surface">Model AI</h2>
      <p class="mt-sm text-caption text-on-surface-variant">
        Pilih model yang tersedia pada API key Google AI Studio. API key tetap aman di server.
      </p>
      <div
        v-if="loadingSettings"
        class="mt-md flex items-center gap-sm text-caption text-on-surface-variant"
      >
        <span class="material-symbols-outlined animate-spin text-[20px] text-primary">sync</span
        >Memuat model AI...
      </div>
      <select
        v-else
        v-model="selectedModel"
        class="mt-md w-full rounded-xl border border-outline-variant/50 bg-surface-container px-md py-md text-body-md text-on-surface focus:border-primary focus:outline-none"
        :disabled="!models.length || savingModel || testingConnection"
        @change="saveModel"
      >
        <option v-if="!models.length" value="">Tidak ada model yang tersedia</option>
        <option v-for="model in models" :key="model.id" :value="model.id">
          {{ model.name }}
        </option>
      </select>
      <button
        type="button"
        class="mt-md inline-flex items-center justify-center gap-sm rounded-xl border border-primary px-lg py-sm font-label-bold text-label-bold text-primary disabled:cursor-not-allowed disabled:opacity-50"
        :disabled="!selectedModel || testingConnection"
        @click="testConnection"
      >
        <span v-if="testingConnection" class="material-symbols-outlined animate-spin text-[18px]"
          >sync</span
        >
        {{ testingConnection ? 'Menguji koneksi...' : 'Tes koneksi' }}
      </button>
      <p
        v-if="modelMessage"
        class="mt-sm text-caption"
        :class="connectionSuccessful ? 'text-primary' : 'text-error'"
      >
        {{ modelMessage }}
      </p>
    </div>

    <div class="rounded-2xl border border-outline-variant/50 bg-surface p-lg shadow-sm sm:p-xl">
      <form class="flex flex-col gap-md sm:flex-row" @submit.prevent="addKeyword">
        <label class="sr-only" for="ai-keyword">Kosakata baru</label>
        <input
          id="ai-keyword"
          v-model="newKeyword"
          class="min-w-0 flex-1 rounded-xl border border-outline-variant/50 bg-surface-container px-md py-md text-body-md text-on-surface focus:border-primary focus:outline-none"
          placeholder="Contoh: speaker pecah, fingerprint, engsel"
          maxlength="80"
        />
        <button
          type="submit"
          class="inline-flex items-center justify-center gap-sm rounded-xl bg-primary px-lg py-md font-label-bold text-label-bold text-on-primary disabled:opacity-50"
          :disabled="!newKeyword.trim() || loading"
        >
          <span v-if="loading" class="material-symbols-outlined animate-spin text-[18px]"
            >sync</span
          >
          {{ loading ? 'Menyimpan...' : 'Tambah kosakata' }}
        </button>
      </form>
      <p class="mt-sm text-caption text-on-surface-variant">
        Gunakan kata atau frasa pendek yang mudah dikenali teknisi.
      </p>
    </div>

    <div class="rounded-2xl border border-outline-variant/50 bg-surface p-lg shadow-sm sm:p-xl">
      <div class="flex items-center justify-between gap-md">
        <h2 class="font-h3 text-h3 font-bold text-on-surface">Kosakata tersimpan</h2>
        <span class="rounded-full bg-primary/10 px-md py-xs text-caption text-primary"
          >{{ keywords.length }} kata</span
        >
      </div>
      <p
        v-if="!loadingSettings && !keywords.length"
        class="mt-lg text-body-md text-on-surface-variant"
      >
        Belum ada kosakata tambahan.
      </p>
      <div v-if="loadingSettings" class="mt-lg flex items-center gap-sm text-on-surface-variant">
        <span class="material-symbols-outlined animate-spin text-[22px] text-primary">sync</span
        >Memuat pengaturan...
      </div>
      <div v-else-if="keywords.length" class="mt-lg flex flex-wrap gap-sm">
        <span
          v-for="keyword in keywords"
          :key="keyword"
          class="inline-flex items-center gap-xs rounded-full bg-surface-container px-md py-sm text-body-md text-on-surface"
        >
          {{ keyword }}
          <button
            type="button"
            class="text-on-surface-variant hover:text-error"
            :aria-label="`Hapus ${keyword}`"
            @click="removeKeyword(keyword)"
          >
            <span class="material-symbols-outlined text-[18px]">close</span>
          </button>
        </span>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { evaluationService } from '@/services/evaluation'

const {
  getAiModels,
  updateAiModel,
  testAiConnection,
  getAiKeywords,
  addAiKeyword,
  deleteAiKeyword,
} = evaluationService()
const models = ref<{ id: string; name: string }[]>([])
const selectedModel = ref('')
const modelMessage = ref('')
const connectionSuccessful = ref(true)
const savingModel = ref(false)
const testingConnection = ref(false)
const keywords = ref<string[]>([])
const newKeyword = ref('')
const loading = ref(false)
const loadingSettings = ref(true)

const loadSettings = async () => {
  try {
    const [modelsRes, keywordsRes] = await Promise.allSettled([getAiModels(), getAiKeywords()])

    if (modelsRes.status === 'fulfilled') {
      models.value = modelsRes.value.models
      selectedModel.value = modelsRes.value.selected || 'gemini-2.5-flash'
    }

    if (keywordsRes.status === 'fulfilled') {
      keywords.value = keywordsRes.value
    }
  } finally {
    loadingSettings.value = false
  }
}

const saveModel = async () => {
  if (!selectedModel.value) return
  savingModel.value = true
  try {
    await updateAiModel(selectedModel.value)
    modelMessage.value = 'Model berhasil disimpan.'
    connectionSuccessful.value = true
  } finally {
    savingModel.value = false
  }
}

const testConnection = async () => {
  if (!selectedModel.value) return
  testingConnection.value = true
  try {
    modelMessage.value = await testAiConnection(selectedModel.value)
    connectionSuccessful.value = true
  } catch {
    modelMessage.value = 'Koneksi gagal. Periksa API key, model, atau kuota Google AI Studio.'
    connectionSuccessful.value = false
  } finally {
    testingConnection.value = false
  }
}

const addKeyword = async () => {
  const keyword = newKeyword.value.trim().toLowerCase()
  if (!keyword) return
  loading.value = true
  try {
    const saved = await addAiKeyword(keyword)
    if (!keywords.value.includes(saved)) keywords.value = [...keywords.value, saved].sort()
    newKeyword.value = ''
  } finally {
    loading.value = false
  }
}

const removeKeyword = async (keyword: string) => {
  await deleteAiKeyword(keyword)
  keywords.value = keywords.value.filter((item) => item !== keyword)
}

onMounted(loadSettings)
</script>
