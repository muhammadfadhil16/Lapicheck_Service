<template>
  <nav
    class="fixed left-0 top-0 z-30 h-full w-[280px] bg-primary border-r border-outline-variant/30 flex flex-col py-6 transition-transform duration-300 lg:translate-x-0"
    :class="open ? 'translate-x-0' : '-translate-x-full'"
  >
    <div class="px-margin mb-xl flex items-start justify-between gap-md">
      <div>
        <h1 class="font-h2 text-h2 font-bold text-primary-fixed">LapiCheck</h1>
        <p class="font-body-md text-body-md text-secondary-fixed-dim opacity-80 mt-xs">
          Technical Assessment
        </p>
      </div>
      <button
        class="lg:hidden text-primary-fixed-dim hover:text-white transition-colors"
        type="button"
        aria-label="Tutup sidebar"
        @click="$emit('close')"
      >
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>
    <div class="flex-1 flex flex-col gap-sm px-md pr-0">
      <!-- Assessment Link -->
      <RouterLink to="/" v-slot="{ isActive }">
        <div
          :class="[
            'flex items-center gap-md px-md py-md border-l-4 font-bold scale-100 active:scale-95 transition-all duration-200 rounded-r-full mr-md cursor-pointer',
            isActive
              ? 'border-primary-fixed-dim bg-on-primary-fixed-variant text-white'
              : 'border-transparent text-primary-fixed-dim hover:bg-on-primary-fixed-variant/30',
          ]"
          @click="$emit('close')"
        >
          <span class="material-symbols-outlined icon-filled">analytics</span>
          <span class="font-label-bold text-label-bold">Assessment</span>
        </div>
      </RouterLink>

      <!-- History Link -->
      <RouterLink to="/history" v-slot="{ isActive }">
        <div
          :class="[
            'flex items-center gap-md px-md py-md border-l-4 font-bold scale-100 active:scale-95 transition-all duration-200 rounded-r-full mr-md cursor-pointer',
            isActive
              ? 'border-primary-fixed-dim bg-on-primary-fixed-variant text-white'
              : 'border-transparent text-primary-fixed-dim hover:bg-on-primary-fixed-variant/30',
          ]"
          @click="$emit('close')"
        >
          <span class="material-symbols-outlined icon-filled">history</span>
          <span class="font-label-bold text-label-bold">History</span>
        </div>
      </RouterLink>
    </div>
    <div class="px-margin mt-auto">
      <button
        class="w-full bg-primary-fixed text-on-primary-fixed py-md font-label-bold text-label-bold rounded-full border border-transparent hover:bg-primary-fixed-dim transition-colors duration-200 flex items-center justify-center gap-sm shadow-[0_4px_14px_rgba(214,227,255,0.15)]"
      >
        <span class="material-symbols-outlined text-[18px]">add</span>
        New Assessment
      </button>
    </div>
  </nav>
</template>

<script setup lang="ts">
defineOptions({ name: 'AppSidebar' })
defineProps<{ open: boolean }>()
defineEmits<{ close: [] }>()
</script>
