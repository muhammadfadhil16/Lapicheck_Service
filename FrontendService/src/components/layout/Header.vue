<template>
  <header
    class="bg-surface/80 backdrop-blur-md border-b border-outline-variant/30 flex justify-between items-center h-20 px-margin sticky top-0 z-10 w-full"
  >
    <div class="flex items-center gap-md">
      <button
        class="lg:hidden hover:text-primary transition-colors opacity-80 hover:opacity-100 flex items-center justify-center bg-surface-container rounded-full p-2 text-primary"
        type="button"
        aria-label="Buka sidebar"
        @click="$emit('toggleSidebar')"
      >
        <span class="material-symbols-outlined">menu</span>
      </button>
      <span class="font-h3 text-h3 font-black text-primary lg:hidden">LapiCheck</span>
    </div>
    <div class="flex items-center gap-lg text-primary">
      <button
        class="hover:text-primary transition-colors opacity-80 hover:opacity-100 flex items-center justify-center bg-surface-container rounded-full p-2"
      >
        <span class="material-symbols-outlined">notifications</span>
      </button>
      <button
        class="hover:text-primary transition-colors opacity-80 hover:opacity-100 flex items-center justify-center bg-surface-container rounded-full p-2"
      >
        <span class="material-symbols-outlined">account_circle</span>
      </button>
    </div>
  </header>
</template>

<script setup lang="ts">
defineOptions({ name: 'AppHeader' })
defineEmits<{ toggleSidebar: [] }>()
</script>
