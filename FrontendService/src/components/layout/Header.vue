<template>
  <header
    class="bg-surface/80 backdrop-blur-md border-b border-outline-variant/30 flex justify-between items-center h-20 px-margin sticky top-0 z-10 w-full"
  >
    <div class="flex items-center gap-md">
      <span class="font-h3 text-h3 font-black text-primary hidden">LapiCheck</span>
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
