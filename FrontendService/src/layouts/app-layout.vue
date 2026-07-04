<template>
  <div class="bg-background text-on-background min-h-screen flex antialiased">
    <Sidebar :open="sidebarOpen" @close="sidebarOpen = false" />
    <button
      v-if="sidebarOpen"
      class="fixed inset-0 z-20 bg-black/40 lg:hidden"
      type="button"
      aria-label="Tutup sidebar"
      @click="sidebarOpen = false"
    ></button>

    <div class="flex-1 flex flex-col min-h-screen lg:ml-[280px] min-w-0">
      <Header @toggle-sidebar="sidebarOpen = !sidebarOpen" />
      <main class="flex-1 p-md sm:p-xl flex flex-col gap-xl bg-background min-w-0">
        <slot />
      </main>
      <Footer />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import Header from '../components/layout/Header.vue'
import Footer from '../components/layout/Footer.vue'
import Sidebar from '../components/layout/Sidebar.vue'

const sidebarOpen = ref(false)
</script>

