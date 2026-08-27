<script setup lang="ts">
type Pagination = {
  current_page: number
  last_page: number
  total: number
  from: number
  to: number
}

defineProps<{
  pagination: Pagination
  label: string
  loading?: boolean
}>()

const emit = defineEmits<{ page: [number] }>()
</script>

<template>
  <div
    v-if="!loading && pagination.total > 0"
    class="flex flex-col items-center justify-between gap-md border-t border-outline-variant/30 bg-surface-container/30 px-lg py-md sm:flex-row"
  >
    <span class="font-caption text-caption text-on-surface-variant">
      Menampilkan {{ pagination.from }}-{{ pagination.to }} dari {{ pagination.total }} {{ label }}
    </span>
    <div class="flex gap-sm">
      <button
        :disabled="pagination.current_page === 1"
        class="flex h-9 w-9 items-center justify-center rounded-xl border border-outline-variant/50 text-on-surface transition-all hover:bg-surface-container-high disabled:cursor-not-allowed disabled:opacity-30"
        @click="emit('page', pagination.current_page - 1)"
      >
        <span class="material-symbols-outlined text-[20px]">chevron_left</span>
      </button>
      <div class="flex gap-xs">
        <button
          v-for="page in pagination.last_page"
          :key="page"
          :class="[
            'flex h-9 w-9 items-center justify-center rounded-xl font-label-bold transition-all',
            pagination.current_page === page
              ? 'bg-primary text-on-primary shadow-md'
              : 'border border-outline-variant/50 text-on-surface hover:bg-surface-container-high',
          ]"
          @click="emit('page', page)"
        >
          {{ page }}
        </button>
      </div>
      <button
        :disabled="pagination.current_page === pagination.last_page"
        class="flex h-9 w-9 items-center justify-center rounded-xl border border-outline-variant/50 text-on-surface transition-all hover:bg-surface-container-high disabled:cursor-not-allowed disabled:opacity-30"
        @click="emit('page', pagination.current_page + 1)"
      >
        <span class="material-symbols-outlined text-[20px]">chevron_right</span>
      </button>
    </div>
  </div>
</template>
