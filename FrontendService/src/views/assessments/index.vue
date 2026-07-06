<template>
  <div>
    <div class="mb-md px-sm">
      <h2 class="font-h1 text-h1 text-primary">Dashboard Penilaian</h2>
      <p class="font-body-lg text-body-lg text-on-surface-variant mt-sm max-w-2xl">
        Masukkan parameter teknis perangkat untuk estimasi nilai.
      </p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-xl items-start">
      <div class="xl:col-span-8 flex flex-col gap-xl">
        <div
          class="bg-surface border border-outline-variant/30 shadow-[0_8px_40px_rgba(0,0,0,0.03)] p-xl rounded-[24px]"
        >
          <h3 class="font-h3 text-h3 text-primary border-b border-outline-variant/50 pb-md mb-xl">
            Parameter Komponen
          </h3>
          <form class="space-y-xl" @submit.prevent="handleEstimation">
            <div class="flex flex-col gap-sm border-b border-outline-variant/50 pb-xl">
              <label
                class="font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider text-[13px]"
                for="laptop-name"
              >
                <span class="material-symbols-outlined text-[18px] text-primary align-middle mr-1">
                  laptop_mac
                </span>
                Nama Perangkat / Model
              </label>
              <input
                id="laptop-name"
                v-model="form.laptop_name"
                class="w-full bg-surface-container border text-on-surface font-body-md text-body-md py-md px-lg rounded-2xl focus:border-2 focus:bg-surface focus:outline-none transition-all shadow-sm"
                :class="formErrors.laptop_name ? 'border-error focus:border-error' : 'border-outline-variant/50 focus:border-primary'"
                placeholder="Contoh: MacBook Pro 2021, ThinkPad X1..."
                type="text"
                required
                @input="clearFieldError('laptop_name')"
              />
              <p v-if="formErrors.laptop_name" class="font-caption text-caption text-error flex items-center gap-1 mt-1">
                <span class="material-symbols-outlined text-[14px]">error</span>
                {{ formErrors.laptop_name }}
              </p>
            </div>

            <div class="flex flex-col gap-sm border-b border-outline-variant/50 pb-xl">
              <label
                class="font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider text-[13px]"
                for="customer-name"
              >
                <span class="material-symbols-outlined text-[18px] text-primary align-middle mr-1">
                  person
                </span>
                Nama Customer
              </label>
              <input
                id="customer-name"
                v-model="form.customer_name"
                class="w-full bg-surface-container border text-on-surface font-body-md text-body-md py-md px-lg rounded-2xl focus:border-2 focus:bg-surface focus:outline-none transition-all shadow-sm"
                :class="formErrors.customer_name ? 'border-error focus:border-error' : 'border-outline-variant/50 focus:border-primary'"
                placeholder="Contoh: Ahmad Fauzi, John Doe..."
                type="text"
                required
                @input="clearFieldError('customer_name')"
              />
              <p v-if="formErrors.customer_name" class="font-caption text-caption text-error flex items-center gap-1 mt-1">
                <span class="material-symbols-outlined text-[14px]">error</span>
                {{ formErrors.customer_name }}
              </p>
            </div>

            <div
              class="grid grid-cols-1 md:grid-cols-2 gap-xl border-b border-outline-variant/50 pb-xl"
            >
                <div class="flex flex-col gap-sm">
                  <div class="flex justify-between items-center">
                    <label
                      class="font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider text-[13px]"
                      for="lcd-slider"
                    >
                      <span
                        class="material-symbols-outlined text-[18px] text-primary align-middle mr-1"
                      >
                        monitor
                      </span>
                      Kondisi Fisik LCD
                    </label>
                    <span class="text-label-bold text-primary font-mono bg-primary/10 px-md py-xs rounded-full text-[14px]">
                      {{ form.lcd_score }} / 100
                    </span>
                  </div>
                  <div class="relative flex flex-col gap-xs py-xs">
                    <input
                      id="lcd-slider"
                      v-model.number="form.lcd_score"
                      type="range"
                      min="0"
                      max="100"
                      step="1"
                      class="w-full h-2 rounded-lg appearance-none cursor-pointer focus:outline-none transition-all"
                      :class="formErrors.lcd_score ? 'bg-error/30 accent-error' : 'bg-surface-container-highest accent-primary'"
                      required
                      @input="clearFieldError('lcd_score')"
                    />
                    <span class="text-caption text-on-surface-variant/90 mt-xs leading-relaxed italic block transition-all duration-300">
                      <span class="material-symbols-outlined text-[14px] align-middle mr-1 text-primary">info</span>
                      {{ getLcdGuidance(form.lcd_score) }}
                    </span>
                    <div class="grid grid-cols-2 gap-xs text-[11px] text-on-surface-variant sm:grid-cols-3">
                      <span v-for="item in lcdRanges" :key="item.label" class="rounded-lg bg-surface-container px-sm py-xs">
                        {{ item.range }}: {{ item.label }}
                      </span>
                    </div>
                    <p v-if="formErrors.lcd_score" class="font-caption text-caption text-error flex items-center gap-1">
                      <span class="material-symbols-outlined text-[14px]">error</span>
                      {{ formErrors.lcd_score }}
                    </p>
                  </div>
                </div>

              <div class="flex flex-col gap-sm">
                  <div class="flex justify-between items-center">
                    <label
                      class="font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider text-[13px]"
                      for="battery-slider"
                    >
                      <span
                        class="material-symbols-outlined text-[18px] text-primary align-middle mr-1"
                      >
                        battery_charging_full
                      </span>
                      Kesehatan Baterai
                    </label>
                    <span class="text-label-bold text-primary font-mono bg-primary/10 px-md py-xs rounded-full text-[14px]">
                      {{ form.battery_health }} %
                    </span>
                  </div>
                  <div class="relative flex flex-col gap-xs py-xs">
                    <input
                      id="battery-slider"
                      v-model.number="form.battery_health"
                      type="range"
                      min="0"
                      max="100"
                      step="1"
                      class="w-full h-2 rounded-lg appearance-none cursor-pointer focus:outline-none transition-all"
                      :class="formErrors.battery_health ? 'bg-error/30 accent-error' : 'bg-surface-container-highest accent-primary'"
                      required
                      @input="clearFieldError('battery_health')"
                    />
                    <span class="text-caption text-on-surface-variant/90 mt-xs leading-relaxed italic block transition-all duration-300">
                      <span class="material-symbols-outlined text-[14px] align-middle mr-1 text-primary">info</span>
                      {{ getBatteryGuidance(form.battery_health) }}
                    </span>
                    <div class="grid grid-cols-2 gap-xs text-[11px] text-on-surface-variant sm:grid-cols-3">
                      <span v-for="item in batteryRanges" :key="item.label" class="rounded-lg bg-surface-container px-sm py-xs">
                        {{ item.range }}: {{ item.label }}
                      </span>
                    </div>
                    <p v-if="formErrors.battery_health" class="font-caption text-caption text-error flex items-center gap-1">
                      <span class="material-symbols-outlined text-[14px]">error</span>
                      {{ formErrors.battery_health }}
                    </p>
                  </div>
                </div>
            </div>

            <div
              class="grid grid-cols-1 md:grid-cols-2 gap-xl border-b border-outline-variant/50 pb-xl"
            >
              <div class="flex flex-col gap-sm">
                <label
                  class="font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider text-[13px]"
                  for="ram-select"
                >
                  <span
                    class="material-symbols-outlined text-[18px] text-primary align-middle mr-1"
                  >
                    memory
                  </span>
                  Kapasitas RAM
                </label>
                <div class="relative">
                  <input
                    id="ram-select"
                    v-model="ramSearch"
                    class="w-full bg-surface-container border text-on-surface font-body-md text-body-md py-md pl-lg pr-12 rounded-2xl focus:border-2 focus:bg-surface focus:outline-none transition-all shadow-sm"
                    :class="formErrors.ram_capacity ? 'border-error focus:border-error' : 'border-outline-variant/50 focus:border-primary'"
                    placeholder="Pilih kapasitas RAM..."
                    autocomplete="off"
                    required
                    @focus="openDropdown = 'ram'"
                    @input="form.ram_capacity = null; clearFieldError('ram_capacity')"
                    @blur="closeDropdown"
                  />
                  <span
                    class="material-symbols-outlined pointer-events-none absolute right-md top-1/2 -translate-y-1/2 text-[22px] text-primary"
                  >
                    expand_more
                  </span>
                  <div
                    v-if="openDropdown === 'ram'"
                    class="absolute z-30 mt-xs max-h-72 w-full overflow-y-auto rounded-2xl border border-outline-variant/40 bg-surface shadow-[0_14px_40px_rgba(0,0,0,0.12)] p-xs"
                  >
                    <button
                      v-for="option in filteredRamOptions"
                      :key="option.value"
                      type="button"
                      class="w-full rounded-xl px-md py-sm text-left font-body-md text-body-md text-on-surface hover:bg-surface-container transition-colors"
                      @mousedown.prevent="selectRam(option)"
                    >
                      <span class="flex items-start gap-sm">
                        <span
                          class="shrink-0 rounded-lg bg-primary/10 px-sm py-[2px] font-label-bold text-label-bold text-primary"
                        >
                          {{ option.value }} GB
                        </span>
                        <span class="text-on-surface-variant leading-relaxed">
                          {{ option.label }}
                        </span>
                      </span>
                    </button>
                    <p
                      v-if="filteredRamOptions.length === 0"
                      class="px-md py-sm font-caption text-caption text-on-surface-variant"
                    >
                      Tidak ada kapasitas yang cocok.
                    </p>
                  </div>
                </div>
                <p v-if="formErrors.ram_capacity" class="font-caption text-caption text-error flex items-center gap-1">
                  <span class="material-symbols-outlined text-[14px]">error</span>
                  {{ formErrors.ram_capacity }}
                </p>
              </div>

              <div class="flex flex-col gap-sm">
                <label
                  class="font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider text-[13px]"
                >
                  <span
                    class="material-symbols-outlined text-[18px] text-primary align-middle mr-1"
                  >
                    speed
                  </span>
                  CPU / Processor
                </label>

                <template v-if="!showManualProcessor">
                  <div class="relative">
                    <input
                      id="processor-select"
                      v-model="processorSearch"
                      class="w-full bg-surface-container border text-on-surface font-body-md text-body-md py-md pl-lg pr-12 rounded-2xl focus:border-2 focus:bg-surface focus:outline-none transition-all shadow-sm"
                      :class="formErrors.processor ? 'border-error focus:border-error' : 'border-outline-variant/50 focus:border-primary'"
                      :placeholder="
                        processors.length
                          ? 'Cari atau pilih processor...'
                          : 'Memuat data processor...'
                      "
                      autocomplete="off"
                      required
                      @focus="openDropdown = 'processor'"
                      @input="form.processor_id = null; clearFieldError('processor')"
                      @blur="closeDropdown"
                    />
                    <span
                      class="material-symbols-outlined pointer-events-none absolute right-md top-1/2 -translate-y-1/2 text-[22px] text-primary"
                    >
                      expand_more
                    </span>
                    <div
                      v-if="openDropdown === 'processor'"
                      class="absolute z-30 mt-xs max-h-72 w-full overflow-y-auto rounded-2xl border border-outline-variant/40 bg-surface shadow-[0_14px_40px_rgba(0,0,0,0.12)] p-xs"
                    >
                      <button
                        v-for="proc in filteredProcessorOptions"
                        :key="proc.id"
                        type="button"
                        class="w-full rounded-xl px-md py-sm text-left font-body-md text-body-md text-on-surface hover:bg-surface-container transition-colors"
                        @mousedown.prevent="selectProcessor(proc)"
                      >
                        <span class="flex items-start justify-between gap-sm">
                          <span class="flex min-w-0 items-start gap-sm">
                            <span
                              class="shrink-0 rounded-lg bg-primary/10 px-sm py-[2px] font-label-bold text-label-bold text-primary"
                            >
                              {{ proc.benchmark_score }}
                            </span>
                            <span class="flex min-w-0 flex-col">
                              <span class="truncate text-on-surface font-medium">{{ proc.name }}</span>
                              <span class="text-caption text-outline">{{ proc.category }}</span>
                            </span>
                          </span>
                          <span
                            role="button"
                            tabindex="0"
                            class="material-symbols-outlined shrink-0 rounded-lg p-xs text-[18px] text-error hover:bg-error/10"
                            :aria-label="`Hapus ${proc.name}`"
                            @mousedown.stop.prevent="deleteProcessorOption(proc)"
                          >
                            delete
                          </span>
                        </span>
                      </button>
                      <div class="px-md py-sm">
                        <p
                          v-if="filteredProcessorOptions.length === 0"
                          class="font-caption text-caption text-on-surface-variant mb-xs"
                        >
                          Processor tidak ditemukan dalam database.
                        </p>
                        <button
                          type="button"
                          class="text-label-bold text-label-bold text-primary hover:underline"
                          @mousedown.prevent="toggleManualProcessor"
                        >
                          + Input processor manual
                        </button>
                      </div>
                    </div>
                  </div>
                  <button
                    type="button"
                    class="text-left text-caption text-primary hover:underline mt-1"
                    @click="toggleManualProcessor"
                  >
                    Processor tidak tersedia? Input manual
                  </button>
                </template>

                <template v-else>
                  <div class="flex flex-col gap-md p-md bg-surface-container/50 rounded-2xl border border-outline-variant/30">
                    <p class="font-label-bold text-label-bold text-on-surface-variant text-[12px]">
                      NAMA PROCESSOR
                    </p>
                    <input
                      v-model="form.processor_name"
                      class="w-full bg-surface border text-on-surface font-body-md text-body-md py-md px-lg rounded-2xl focus:border-2 focus:bg-surface focus:outline-none transition-all shadow-sm"
                      :class="formErrors.processor_name ? 'border-error focus:border-error' : 'border-outline-variant/50 focus:border-primary'"
                      placeholder="Contoh: Apple M3, AMD Ryzen 9..."
                      type="text"
                      required
                      @input="clearFieldError('processor_name')"
                    />
                    <div class="flex flex-col gap-xs">
                      <label class="font-label-bold text-label-bold text-on-surface-variant text-[12px]">
                        Benchmark <span class="font-normal text-outline">(contoh: 5000–40000)</span>
                      </label>
                      <input
                        v-model.number="form.processor_input"
                        class="w-full bg-surface border border-outline-variant/50 text-on-surface font-body-md text-body-md py-md px-lg rounded-2xl focus:border-2 focus:border-primary focus:bg-surface focus:outline-none transition-all shadow-sm"
                        type="number"
                        min="0"
                        placeholder="Contoh: 6500"
                      />
                    </div>
                    <button
                      type="button"
                      class="text-left text-caption text-primary hover:underline"
                      @click="toggleManualProcessor"
                    >
                      &larr; Kembali ke daftar processor
                    </button>
                  </div>
                  <p v-if="formErrors.processor_name" class="font-caption text-caption text-error flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    {{ formErrors.processor_name }}
                  </p>
                </template>
                <p v-if="formErrors.processor" class="font-caption text-caption text-error flex items-center gap-1">
                  <span class="material-symbols-outlined text-[14px]">error</span>
                  {{ formErrors.processor }}
                </p>
              </div>
            </div>

            <div
              class="grid grid-cols-1 md:grid-cols-2 gap-xl border-b border-outline-variant/50 pb-xl"
            >
                <div class="flex flex-col gap-sm">
                  <div class="flex justify-between items-center">
                    <label
                      class="font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider text-[13px]"
                      for="keyboard-slider"
                    >
                      <span
                        class="material-symbols-outlined text-[18px] text-primary align-middle mr-1"
                      >
                        keyboard
                      </span>
                      Fungsi Keyboard
                    </label>
                    <span class="text-label-bold text-primary font-mono bg-primary/10 px-md py-xs rounded-full text-[14px]">
                      {{ form.keyboard_score }} / 100
                    </span>
                  </div>
                  <div class="relative flex flex-col gap-xs py-xs">
                    <input
                      id="keyboard-slider"
                      v-model.number="form.keyboard_score"
                      type="range"
                      min="0"
                      max="100"
                      step="1"
                      class="w-full h-2 rounded-lg appearance-none cursor-pointer focus:outline-none transition-all"
                      :class="formErrors.keyboard_score ? 'bg-error/30 accent-error' : 'bg-surface-container-highest accent-primary'"
                      required
                      @input="clearFieldError('keyboard_score')"
                    />
                    <span class="text-caption text-on-surface-variant/90 mt-xs leading-relaxed italic block transition-all duration-300">
                      <span class="material-symbols-outlined text-[14px] align-middle mr-1 text-primary">info</span>
                      {{ getKeyboardGuidance(form.keyboard_score) }}
                    </span>
                    <div class="grid grid-cols-2 gap-xs text-[11px] text-on-surface-variant sm:grid-cols-3">
                      <span v-for="item in keyboardRanges" :key="item.label" class="rounded-lg bg-surface-container px-sm py-xs">
                        {{ item.range }}: {{ item.label }}
                      </span>
                    </div>
                    <p v-if="formErrors.keyboard_score" class="font-caption text-caption text-error flex items-center gap-1">
                      <span class="material-symbols-outlined text-[14px]">error</span>
                      {{ formErrors.keyboard_score }}
                    </p>
                  </div>
                </div>

              <div class="flex flex-col gap-sm">
                <label
                  class="font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider text-[13px]"
                  for="market-price-input"
                >
                  <span
                    class="material-symbols-outlined text-[18px] text-primary align-middle mr-1"
                  >
                    payments
                  </span>
                  Harga Pasaran
                </label>
                <div
                  class="grid grid-cols-1 sm:grid-cols-[minmax(0,1fr)_112px] shadow-sm rounded-2xl overflow-visible bg-surface-container transition-all"
                  :class="formErrors.price ? 'border-2 border-error' : 'border border-outline-variant/50 focus-within:border-2 focus-within:border-primary'"
                >
                  <div class="relative min-w-0">
                    <div
                      class="absolute inset-y-0 start-0 flex items-center ps-4 pointer-events-none"
                    >
                      <span class="text-primary font-label-bold text-label-bold whitespace-nowrap">
                        {{ selectedCurrency.symbol }}
                      </span>
                    </div>
                    <input
                      id="market-price-input"
                      :value="marketPriceDisplay"
                      class="block w-full min-w-0 rounded-t-2xl sm:rounded-l-2xl sm:rounded-tr-none ps-14 pe-4 py-md bg-transparent text-on-surface font-mono text-[15px] sm:text-body-md tracking-tight focus:bg-surface focus:outline-none transition-all"
                      inputmode="numeric"
                      required
                      @input="handleMarketPriceInput($event); clearFieldError('price')"
                      @blur="handleMarketPriceBlur"
                    />
                  </div>
                  <div
                    class="relative border-t sm:border-t-0 sm:border-l border-outline-variant/50"
                    tabindex="0"
                    @focusout="handleCurrencyBlur"
                  >
                    <button
                      class="w-full h-full px-md py-md bg-surface-container text-on-surface-variant font-label-bold text-label-bold flex items-center gap-2 justify-between rounded-b-2xl sm:rounded-bl-none sm:rounded-r-2xl"
                      type="button"
                      @click="openDropdown = 'currency'"
                    >
                      <span class="flex items-center gap-2 min-w-0">
                        <span
                          class="h-4 w-6 shrink-0 rounded-sm border border-outline-variant/40 shadow-sm"
                          :class="selectedCurrency.flagClass"
                        ></span>
                        <span class="truncate">{{ selectedCurrency.code }}</span>
                      </span>
                      <span class="material-symbols-outlined text-[20px] text-primary"
                        >expand_more</span
                      >
                    </button>
                    <div
                      v-if="openDropdown === 'currency'"
                      class="absolute right-0 z-30 mt-xs w-full min-w-[240px] rounded-2xl border border-outline-variant/40 bg-surface shadow-[0_14px_40px_rgba(0,0,0,0.12)]"
                    >
                      <div class="p-xs">
                        <div class="relative">
                          <span
                            class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-[18px] text-outline"
                            >search</span
                          >
                          <input
                            v-model="currencyQuery"
                            class="w-full pl-[40px] pr-md py-sm rounded-xl bg-surface-container text-on-surface font-body-md text-body-md focus:bg-surface focus:outline-none"
                            placeholder="Cari mata uang..."
                            @input="openDropdown = 'currency'"
                          />
                        </div>
                      </div>
                      <div class="max-h-56 overflow-y-auto p-xs pt-0">
                        <button
                          v-for="currency in filteredCurrencyOptions"
                          :key="currency.code"
                          type="button"
                          class="w-full rounded-xl px-md py-sm text-left font-label-bold text-label-bold text-on-surface hover:bg-surface-container transition-colors"
                          @mousedown.prevent="selectCurrency(currency)"
                        >
                          <span
                            class="inline-flex h-4 w-6 rounded-sm border border-outline-variant/40 shadow-sm mr-2 align-middle"
                            :class="currency.flagClass"
                          ></span>
                          <span class="text-primary">{{ currency.symbol }}</span>
                          <span class="text-on-surface-variant">({{ currency.code }})</span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                <div
                  class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-xs font-caption text-caption text-on-surface-variant"
                >
                  <span>Masukkan harga device di waktu sekarang bisa mencari di internet sesuai mata uang yang dipilih.</span>
                  <span v-if="form.price !== null" class="font-semibold text-primary">
                    Preview: {{ selectedCurrency.symbol
                    }}{{ formatNumber(form.price, selectedCurrency.locale) }}
                  </span>
                </div>
                <p v-if="formErrors.price" class="font-caption text-caption text-error flex items-center gap-1">
                  <span class="material-symbols-outlined text-[14px]">error</span>
                  {{ formErrors.price }}
                </p>
              </div>
            </div>

            <div class="flex flex-col gap-sm border-b border-outline-variant/50 pb-xl">
              <label
                class="font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider text-[13px]"
              >
                <span class="material-symbols-outlined text-[18px] text-primary align-middle mr-1">
                  photo_camera
                </span>
                Foto Laptop (Opsional, Maks. 3 Foto)
              </label>
              <div
                class="border-2 border-dashed border-outline-variant/50 rounded-2xl p-xl text-center cursor-pointer hover:border-primary/50 hover:bg-primary/[0.02] transition-all"
                @click="triggerFileInput"
                @dragover.prevent="isDragOver = true"
                @dragleave.prevent="isDragOver = false"
                @drop.prevent="handleDrop"
                :class="{ 'border-primary bg-primary/[0.04]': isDragOver }"
              >
                <input
                  ref="fileInputRef"
                  type="file"
                  accept=".jpg,.jpeg,.png"
                  multiple
                  class="hidden"
                  @change="handleFileSelect"
                />
                <span class="material-symbols-outlined text-[40px] text-outline mb-sm"
                  >cloud_upload</span
                >
                <p class="font-label-bold text-label-bold text-on-surface-variant">
                  Seret foto ke sini atau klik untuk unggah
                </p>
                <p class="font-caption text-caption text-outline mt-xs">
                  Format: JPG, JPEG, PNG. Maks. 2MB per file.
                </p>
              </div>

              <div v-if="imagePreviews.length > 0" class="flex flex-wrap gap-md mt-sm">
                <div
                  v-for="(preview, index) in imagePreviews"
                  :key="index"
                  class="relative w-24 h-24 rounded-2xl overflow-hidden border border-outline-variant/30 shadow-sm group"
                >
                  <img
                    :src="preview.url"
                    :alt="`Preview ${index + 1}`"
                    class="w-full h-full object-cover"
                  />
                  <button
                    type="button"
                    class="absolute top-1 right-1 w-6 h-6 bg-error/80 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                    @click="removeImage(index)"
                  >
                    <span class="material-symbols-outlined text-[14px]">close</span>
                  </button>
                  <div
                    class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-[10px] text-center py-0.5 font-medium"
                  >
                    {{ (preview.file.size / 1024).toFixed(0) }} KB
                  </div>
                </div>
              </div>
              <p v-if="imageErrors.length > 0" class="font-caption text-caption text-error">
                <span v-for="(err, i) in imageErrors" :key="i">{{ err }}<br /></span>
              </p>
            </div>

            <div class="flex flex-col gap-sm pt-xl">
              <label
                class="font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider text-[13px]"
                for="laptop-description"
              >
                <span class="material-symbols-outlined text-[18px] text-primary align-middle mr-1">
                  description
                </span>
                Deskripsi Tambahan / Catatan Kondisi (Opsional)
              </label>
              <textarea
                id="laptop-description"
                v-model="form.description"
                rows="3"
                class="w-full bg-surface-container border border-outline-variant/50 text-on-surface font-body-md text-body-md py-md px-lg rounded-2xl focus:border-2 focus:border-primary focus:bg-surface focus:outline-none transition-all shadow-sm resize-none"
                placeholder="Contoh: Bodi mulus 95%, ada lecet tipis di sudut kiri bawah, keyboard backlit menyala..."
              ></textarea>
            </div>

            <div class="flex items-center justify-between bg-surface-container border border-outline-variant/50 rounded-2xl px-lg py-md shadow-sm">
              <div class="flex items-center gap-sm">
                <span class="material-symbols-outlined text-[18px] text-primary">smart_toy</span>
                <div>
                  <p class="font-label-bold text-label-bold text-on-surface text-[14px]">Analisis AI</p>
                  <p class="font-body-xs text-on-surface-variant text-[12px]">Gunakan AI untuk analisis lebih mudah dan efektif</p>
                </div>
              </div>
              <button
                type="button"
                @click="form.use_ai = !form.use_ai"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                :class="form.use_ai ? 'bg-primary' : 'bg-outline-variant'"
              >
                <span
                  class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow-sm"
                  :class="form.use_ai ? 'translate-x-6' : 'translate-x-1'"
                />
              </button>
            </div>

            <div class="pt-xl mt-xl border-t border-outline-variant/50 flex justify-end">
              <button
                class="bg-primary text-on-primary border border-transparent font-label-bold text-label-bold py-md px-[40px] rounded-full hover:bg-primary-container active:border-primary-fixed-dim shadow-[0_8px_20px_rgba(0,32,69,0.15)] hover:shadow-[0_12px_24px_rgba(0,32,69,0.2)] hover:-translate-y-0.5 transition-all flex items-center gap-sm disabled:opacity-50 disabled:cursor-not-allowed"
                type="submit"
                :disabled="loading"
              >
                <span
                  class="material-symbols-outlined text-[20px]"
                  :class="{ 'animate-spin': loading }"
                >
                  {{ loading ? 'sync' : 'calculate' }}
                </span>
                {{ loading ? 'Memproses...' : 'Proses Estimasi Nilai' }}
              </button>
            </div>
          </form>
        </div>

        <div
          v-if="result"
          ref="resultSection"
          class="bg-surface border border-outline-variant/30 shadow-[0_8px_40px_rgba(0,0,0,0.03)] p-xl rounded-[24px] scroll-mt-lg"
        >
          <div
            class="flex items-center justify-between border-b border-outline-variant/50 pb-md mb-xl"
          >
            <h3 class="font-h3 text-h3 text-primary">Laporan Hasil Penilaian</h3>
            <button
              class="flex items-center gap-sm px-lg py-md bg-primary text-on-primary rounded-full font-label-bold text-label-bold hover:bg-primary-container transition-all shadow-md active:scale-95"
              @click="handleExportPDF"
              :disabled="pdfLoading"
            >
              <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
              {{ pdfLoading ? 'Memproses...' : 'Export PDF' }}
            </button>
            </div>

            <div
              class="grid grid-cols-[repeat(auto-fit,minmax(210px,1fr))] gap-lg mb-xl items-stretch"
            >
            <div
              class="min-w-0 min-h-[176px] w-full bg-surface border border-outline-variant/30 px-lg py-xl flex flex-col items-center text-center rounded-[16px] shadow-[0_6px_18px_rgba(0,0,0,0.08)]"
            >
              <div class="relative flex items-center justify-center mb-md">
                <svg class="w-[132px] h-[78px]" viewBox="0 0 120 70" fill="none" aria-hidden="true">
                  <path
                    d="M 10 60 A 50 50 0 0 1 110 60"
                    stroke="#e3e2e6"
                    stroke-width="12"
                    stroke-linecap="round"
                  />
                  <path
                    d="M 10 60 A 50 50 0 0 1 110 60"
                    :stroke="statusTextColor(result.status)"
                    stroke-width="12"
                    stroke-linecap="round"
                    pathLength="100"
                    :stroke-dasharray="scoreDash(result.final_score)"
                  />
                </svg>
                <span
                  class="absolute top-[40px] text-[30px] font-extrabold leading-none"
                  :style="{ color: statusTextColor(result.status) }"
                >
                  {{ result.final_score }}
                </span>
              </div>
              <p
                class="font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider text-[13px] leading-snug"
              >
                Skor Akhir
              </p>
            </div>
            <div
              class="min-w-0 min-h-[176px] w-full bg-surface border border-outline-variant/30 px-lg py-xl flex flex-col items-center justify-center text-center rounded-[16px] shadow-[0_6px_18px_rgba(0,0,0,0.08)]"
            >
              <span
                class="mb-xs flex h-12 w-12 items-center justify-center rounded-full bg-surface-container/60"
                :style="{ color: statusTextColor(result.status) }"
                aria-hidden="true"
              >
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                  <path
                    d="M7 10v7a2 2 0 0 0 2 2h5a2 2 0 0 0 2-2v-5a2 2 0 0 0-2-2h-3V5a2 2 0 0 0-2-2l-1 6"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M4 10h3v9H4z"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
              </span>
              <p
                class="font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider text-[13px] mb-sm leading-snug"
              >
                Kategori kelayakan CPU dan Kondisi Fisik
              </p>
              <p
                class="w-full max-w-full text-center text-[26px] leading-tight font-extrabold wrap-anywhere"
                :style="{ color: statusTextColor(result.status) }"
              >
                {{ result.status }}
              </p>
              <span
                class="mt-md h-2.5 w-2.5 rounded-full status-dot-pulse"
                :style="{ backgroundColor: statusTextColor(result.status) }"
                aria-hidden="true"
              ></span>
            </div>
            <div
              class="min-w-0 min-h-[176px] w-full bg-surface border border-outline-variant/30 px-lg py-xl flex flex-col items-center text-center rounded-[16px] shadow-[0_6px_18px_rgba(0,0,0,0.08)]"
            >
              <span
                class="mb-xs flex h-12 w-12 items-center justify-center rounded-full bg-surface-container/60"
                :style="{ color: statusTextColor(result.status) }"
                aria-hidden="true"
              >
                <svg width="32" height="32" viewBox="0 0 640 640" fill="none">
                  <path
                    d="M320 48C306.7 48 296 58.7 296 72L296 84L294.2 84C257.6 84 228 113.7 228 150.2C228 183.6 252.9 211.8 286 215.9L347 223.5C352.1 224.1 356 228.5 356 233.7C356 239.4 351.4 243.9 345.8 243.9L272 244C256.5 244 244 256.5 244 272C244 287.5 256.5 300 272 300L296 300L296 312C296 325.3 306.7 336 320 336C333.3 336 344 325.3 344 312L344 300L345.8 300C382.4 300 412 270.3 412 233.8C412 200.4 387.1 172.2 354 168.1L293 160.5C287.9 159.9 284 155.5 284 150.3C284 144.6 288.6 140.1 294.2 140.1L360 140C375.5 140 388 127.5 388 112C388 96.5 375.5 84 360 84L344 84L344 72C344 58.7 333.3 48 320 48zM141.3 405.5L98.7 448L64 448C46.3 448 32 462.3 32 480L32 544C32 561.7 46.3 576 64 576L384.5 576C413.5 576 441.8 566.7 465.2 549.5L591.8 456.2C609.6 443.1 613.4 418.1 600.3 400.3C587.2 382.5 562.2 378.7 544.4 391.8L424.6 480L312 480C298.7 480 288 469.3 288 456C288 442.7 298.7 432 312 432L384 432C401.7 432 416 417.7 416 400C416 382.3 401.7 368 384 368L231.8 368C197.9 368 165.3 381.5 141.3 405.5z"
                    fill="currentColor"
                  />
                </svg>
              </span>
              <p
                class="font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider text-[13px] mb-sm leading-snug"
              >
                Estimasi Harga
              </p>
              <p
                class="max-w-full text-[26px] leading-tight font-extrabold wrap-anywhere text-primary"
              >
                {{ formatCurrencyValue(result.estimated_price) }}
              </p>
              <span
                class="mt-md h-2 w-2 rounded-full"
                :style="{ backgroundColor: statusTextColor(result.status) }"
                aria-hidden="true"
              ></span>
            </div>
          </div>

          <div
            class="mb-xl bg-surface-container/30 p-lg rounded-[20px] border border-outline-variant/20"
          >
            <h4 class="font-label-bold text-label-bold text-primary mb-md">Distribusi Metrik</h4>
            <div class="space-y-md">
              <div v-for="metric in metricDistributions" :key="metric.id">
                <div
                  class="flex justify-between font-caption text-caption text-on-surface-variant mb-2"
                >
                  <span class="font-semibold text-primary">
                    <span
                      class="material-symbols-outlined text-[16px] text-primary align-middle mr-1"
                      >{{ metric.icon }}</span
                    >
                    {{ metric.label }}
                  </span>
                  <span class="font-bold">{{ metric.valueLabel }}</span>
                </div>
                <div
                  class="w-full bg-surface-variant h-3 rounded-full overflow-hidden shadow-inner"
                >
                  <div
                    class="h-full rounded-full transition-all duration-500"
                    :style="{ width: metric.barWidth, backgroundColor: metric.barColor }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <div
            v-if="result.images && result.images.length > 0"
            class="mb-xl bg-surface-container/30 p-lg rounded-[20px] border border-outline-variant/20"
          >
            <h4 class="font-label-bold text-label-bold text-primary mb-md">
              <span class="material-symbols-outlined text-[20px] text-primary">photo_library</span>
              Foto Laptop
            </h4>
            <div class="flex flex-wrap gap-md">
              <div
                v-for="img in result.images"
                :key="img.id"
                class="w-32 h-32 rounded-2xl overflow-hidden border border-outline-variant/30 shadow-sm"
              >
                <img
                  :src="getImageUrl(img.image_path)"
                  alt="Foto laptop"
                  class="w-full h-full object-cover"
                />
              </div>
            </div>
          </div>

          <div
            v-if="result.ai_conclusion && result.ai_conclusion !== 'tidak ada catatan tambahan'"
            class="mt-xl"
          >
            <h4 class="font-label-bold text-label-bold text-primary mb-md flex items-center gap-sm">
              <span class="material-symbols-outlined text-[20px] text-primary">model_training</span>
              Rekomendasi & Analisis AI
              <span
                v-if="result.ai_used === false"
                class="text-[11px] bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-semibold ml-auto"
              >Estimasi</span>
            </h4>
            <div
              class="bg-primary/[0.04] border border-primary/10 p-lg rounded-[24px] shadow-[0_4px_20px_rgba(0,0,0,0.01)] relative overflow-hidden"
            >
              <div
                class="absolute -right-6 -bottom-6 opacity-[0.04] text-primary pointer-events-none"
              >
                <span class="material-symbols-outlined text-[120px]">model_training</span>
              </div>
              <p
                class="font-body-md text-body-md text-on-surface font-medium leading-relaxed relative z-10"
              >
                {{ result.ai_conclusion }}
              </p>
              <p
                v-if="result.ai_warning"
                class="mt-lg px-md py-sm bg-red-50 border border-red-200 rounded-xl text-red-600 font-semibold text-sm leading-relaxed relative z-10"
              >
                {{ result.ai_warning }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <aside class="xl:col-span-4 flex flex-col gap-xl">
        <div
          class="bg-surface border border-outline-variant/30 shadow-[0_8px_40px_rgba(0,0,0,0.03)] p-xl rounded-[24px]"
        >
          <div class="flex items-center gap-md border-b border-outline-variant/50 pb-md mb-lg">
            <div class="bg-primary-fixed/30 p-2 rounded-full flex items-center justify-center">
              <span class="material-symbols-outlined text-primary">info</span>
            </div>
            <h3 class="font-h3 text-h3 text-primary">Informasi Layanan</h3>
          </div>
          <div class="space-y-lg font-body-md text-body-md text-on-surface-variant leading-relaxed">
            <p>
              Sistem penilaian LapiCheck menggunakan
              <strong class="text-primary font-semibold">Logika Fuzzy</strong> tingkat lanjut untuk
              memproses parameter teknis yang Anda masukkan.
            </p>
            <p>
              Tidak seperti penilaian biner tradisional (rusak/baik), logika fuzzy memungkinkan
              sistem mengevaluasi degradasi komponen secara gradien. Misalnya, kesehatan baterai 72%
              tidak dianggap sepenuhnya rusak, melainkan diproses sebagai "kondisi menengah-bawah"
              yang mempengaruhi nilai akhir secara proporsional.
            </p>
            <ul
              class="bg-surface-container/30 p-md rounded-2xl border border-outline-variant/20 space-y-md mt-lg"
            >
              <li class="flex items-start gap-md">
                <span class="material-symbols-outlined text-[20px] text-primary">check_circle</span>
                <span class="pt-0.5">Estimasi lebih presisi sesuai kondisi nyata pasar.</span>
              </li>
              <li class="flex items-start gap-md">
                <span class="material-symbols-outlined text-[20px] text-primary">check_circle</span>
                <span class="pt-0.5">Mengurangi subjektivitas dalam penentuan harga.</span>
              </li>
              <li class="flex items-start gap-md">
                <span class="material-symbols-outlined text-[20px] text-primary">check_circle</span>
                <span class="pt-0.5"
                  >Bobot nilai dihitung berdasarkan matriks prioritas komponen.</span
                >
              </li>
            </ul>
          </div>
        </div>

        <div
          class="bg-surface shadow-[0_8px_30px_rgba(0,0,0,0.04)] border border-outline-variant/30 p-lg rounded-[24px] flex items-center justify-between gap-md"
        >
          <div>
            <p class="font-label-bold text-label-bold text-primary">Butuh Bantuan?</p>
            <p class="font-caption text-caption text-on-surface-variant mt-xs">
              Baca panduan kalibrasi parameter.
            </p>
          </div>
          <button
            class="border border-outline-variant/50 text-primary px-lg py-md rounded-full font-label-bold text-caption hover:bg-surface-container hover:shadow-sm transition-all whitespace-nowrap"
          >
            Buka Panduan
          </button>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, reactive, ref, onMounted, nextTick } from 'vue'
import {
  CURRENCY_OPTIONS,
  DEFAULT_CURRENCY,
  RAM_OPTIONS,
  METRIC_DEFINITIONS,
  currencyLabel,
  type CurrencyOption,
  type MetricDefinition,
  type ScoreOption,
  type Processor,
} from '@/constants/assessment'
import {
  formatCurrency,
  formatNumber,
  metricColor,
  scoreDash,
  statusTextColor,
  renderReportToCanvas,
  saveCanvasAsPDF,
} from '@/utils/assessment'
import { getImageUrl } from '@/composables/useApi'
import { evaluationService, type EvaluationData, type ApiError } from '@/services/evaluation'
import Swal from 'sweetalert2'

defineOptions({ name: 'AssessmentIndex' })

const { evaluate, getProcessors, deleteProcessor } = evaluationService()

const form = reactive({
  customer_name: '',
  laptop_name: '',
  lcd_score: 100 as number | null,
  battery_health: 80 as number | null,
  ram_capacity: null as number | null,
  processor_id: null as number | null,
  processor_name: '',
  processor_input: 5000,
  keyboard_score: 100 as number | null,
  price: null as number | null,
  description: '',
  use_ai: false,
  images: [] as File[],
})

const formErrors = reactive<Record<string, string>>({
  customer_name: '',
  laptop_name: '',
  lcd_score: '',
  battery_health: '',
  ram_capacity: '',
  processor: '',
  processor_name: '',
  keyboard_score: '',
  price: '',
  description: '',
  images: '',
})

const clearFieldError = (field: string) => {
  formErrors[field] = ''
}

const showManualProcessor = ref(false)

const result = ref<EvaluationData | null>(null)
const loading = ref(false)
const pdfLoading = ref(false)
const resultSection = ref<HTMLElement | null>(null)
// const resultCard = ref<HTMLElement | null>(null)
const marketPriceDisplay = ref('')
const selectedCurrencyCode = ref('IDR')

const ramSearch = ref('')
const processorSearch = ref('')
const currencyQuery = ref('')
const openDropdown = ref<'ram' | 'processor' | 'currency' | null>(null)

const processors = ref<Processor[]>([])
const isDragOver = ref(false)
const fileInputRef = ref<HTMLInputElement | null>(null)

const imagePreviews = ref<{ file: File; url: string }[]>([])
const imageErrors = ref<string[]>([])

const ramOptions = RAM_OPTIONS
const defaultCurrency = DEFAULT_CURRENCY
const currencyOptions = CURRENCY_OPTIONS

const selectedCurrency = computed<CurrencyOption>(() => {
  return (
    currencyOptions.find((currency) => currency.code === selectedCurrencyCode.value) ??
    defaultCurrency
  )
})

const filterScoreOptions = (options: ScoreOption[], query: string) => {
  const normalizedQuery = query.toLowerCase().trim()
  if (!normalizedQuery) return options
  return options.filter((option) =>
    `${option.value} ${option.label}`.toLowerCase().includes(normalizedQuery),
  )
}

const filteredRamOptions = computed(() => filterScoreOptions(ramOptions, ramSearch.value))

const filteredProcessorOptions = computed(() => {
  const query = processorSearch.value.toLowerCase().trim()
  if (!query) return processors.value
  return processors.value.filter(
    (proc) =>
      proc.name.toLowerCase().includes(query) ||
      String(proc.benchmark_score).includes(query) ||
      proc.category.toLowerCase().includes(query),
  )
})

const filteredCurrencyOptions = computed(() => {
  const query = currencyQuery.value.toLowerCase().trim()
  if (!query) return currencyOptions
  return currencyOptions.filter((currency) => currencyLabel(currency).toLowerCase().includes(query))
})

const lcdRanges = [
  { range: '100', label: 'Sempurna' },
  { range: '80-99', label: 'Normal' },
  { range: '60-79', label: 'Minus ringan' },
  { range: '40-59', label: 'Rusak sedang' },
  { range: '10-39', label: 'Rusak parah' },
  { range: '0-9', label: 'Mati total' },
]

const batteryRanges = [
  { range: '85-100%', label: 'Sangat sehat' },
  { range: '70-84%', label: 'Normal' },
  { range: '50-69%', label: 'Cukup' },
  { range: '20-49%', label: 'Drop' },
  { range: '0-19%', label: 'Rusak' },
]

const keyboardRanges = [
  { range: '100', label: 'Sempurna' },
  { range: '80-99', label: 'Normal' },
  { range: '60-79', label: 'Kurang responsif' },
  { range: '30-59', label: 'Macet/ghosting' },
  { range: '0-29', label: 'Rusak parah' },
]

const getLcdGuidance = (score: number | null) => {
  if (score === null) return 'Seret slider untuk menentukan kondisi LCD...'
  if (score === 100) return 'Sempurna / Seperti Baru (Tidak ada dead pixel/garis/flicker)'
  if (score >= 80) return 'Lecet Pemakaian Wajar / Baret Halus (Fungsi normal)'
  if (score >= 60) return 'Shadow Tipis / White Spot Kecil (Minus kosmetik/layar berbayang tipis)'
  if (score >= 40) return 'Retak / Bergaris Tunggal (LCD mulai buruk)'
  if (score >= 10) return 'LCD Rusak Parah (Bercak hitam/ghost touch/flicker parah/blank putih)'
  return 'Mati Total (Blank hitam)'
}

const selectRam = (option: ScoreOption) => {
  form.ram_capacity = option.value
  ramSearch.value = option.label
  openDropdown.value = null
  clearFieldError('ram_capacity')
}

const selectProcessor = (proc: Processor) => {
  form.processor_id = proc.id
  form.processor_name = ''
  processorSearch.value = `${proc.name} (${proc.benchmark_score})`
  openDropdown.value = null
  clearFieldError('processor')
  clearFieldError('processor_name')
}

const deleteProcessorOption = async (proc: Processor) => {
  const result = await Swal.fire({
    title: 'Hapus Processor?',
    text: proc.name,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Hapus',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#ba1a1a',
    background: '#faf9fd',
    customClass: { popup: 'rounded-[24px]' },
  })

  if (!result.isConfirmed) return

  await deleteProcessor(proc.id)
  processors.value = processors.value.filter((item) => item.id !== proc.id)
  if (form.processor_id === proc.id) {
    form.processor_id = null
    processorSearch.value = ''
  }
}

const toggleManualProcessor = () => {
  showManualProcessor.value = !showManualProcessor.value
  if (showManualProcessor.value) {
    form.processor_id = null
    processorSearch.value = ''
    openDropdown.value = null
    form.processor_name = ''
    form.processor_input = 50
  }
}

const getKeyboardGuidance = (score: number | null) => {
  if (score === null) return 'Seret slider untuk menentukan kondisi keyboard...'
  if (score === 100) return 'Sempurna / Seperti Baru (Semua tombol empuk & responsif)'
  if (score >= 80) return 'Normal Pemakaian (Fungsi 100%, ada aus halus / tuts lepas membran normal)'
  if (score >= 60) return 'Kurang Responsif (1-2 tombol keras/mendam/mati jarang pakai)'
  if (score >= 30) return 'Tombol Vital Macet / Ghosting Ringan (Spasi/Enter macet atau mencet sendiri)'
  return 'Mati Total / Korslet (Rusak parah/tumpahan cairan)'
}

const getBatteryGuidance = (score: number | null) => {
  if (score === null) return 'Seret slider untuk menentukan kesehatan baterai...'
  if (score >= 85) return 'Sangat Sehat / Awet (Kapasitas di atas 85%)'
  if (score >= 70) return 'Kondisi Normal (Kapasitas 70% - 84%, awet wajar)'
  if (score >= 50) return 'Kondisi Cukup / Agak Boros (Kapasitas 50% - 69%, perlu dicas lebih sering)'
  if (score >= 20) return 'Baterai Drop / Kembung (Kapasitas 20% - 49%, harus dicolok charger terus)'
  return 'Rusak Parah / Mati (Baterai tidak terdeteksi atau mati total)'
}

const selectCurrency = (currency: CurrencyOption) => {
  selectedCurrencyCode.value = currency.code
  currencyQuery.value = ''
  openDropdown.value = null
  handleCurrencyChange()
}

const handleCurrencyBlur = (event: FocusEvent) => {
  const currentTarget = event.currentTarget as HTMLElement
  const nextTarget = event.relatedTarget as HTMLElement | null
  if (!nextTarget || !currentTarget.contains(nextTarget)) {
    closeDropdown()
  }
}

const closeDropdown = () => {
  window.setTimeout(() => {
    openDropdown.value = null
  }, 120)
}

const formatCurrencyValue = (value?: number | null) =>
  formatCurrency(value ?? null, selectedCurrency.value)

const syncMarketPriceDisplay = () => {
  if (form.price === null || form.price === undefined) {
    marketPriceDisplay.value = ''
    return
  }
  marketPriceDisplay.value = formatNumber(Number(form.price), selectedCurrency.value.locale)
}

const handleMarketPriceInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  const raw = target.value
  const cursor = target.selectionStart ?? raw.length

  const rawDigits = raw.replace(/[^0-9]/g, '')
  if (!rawDigits) {
    form.price = null
    marketPriceDisplay.value = ''
    return
  }

  const numericValue = Number(rawDigits)
  form.price = numericValue
  const formatted = formatNumber(numericValue, selectedCurrency.value.locale)
  marketPriceDisplay.value = formatted

  let digitCountBefore = 0
  for (let i = 0; i < cursor; i++) {
    if (/[0-9]/.test(raw.charAt(i))) digitCountBefore++
  }

  let newPos = 0
  let digitCount = 0
  for (let i = 0; i < formatted.length; i++) {
    if (/[0-9]/.test(formatted.charAt(i))) {
      digitCount++
      if (digitCount >= digitCountBefore) {
        newPos = i + 1
        break
      }
    }
  }
  if (newPos === 0) newPos = formatted.length

  requestAnimationFrame(() => {
    target.setSelectionRange(newPos, newPos)
  })
}

const handleMarketPriceBlur = () => {
  syncMarketPriceDisplay()
}

const handleCurrencyChange = () => {
  syncMarketPriceDisplay()
}

const triggerFileInput = () => {
  fileInputRef.value?.click()
}

const validateAndAddFiles = (files: FileList | File[]) => {
  imageErrors.value = []
  clearFieldError('images')
  const allowedTypes = ['image/jpeg', 'image/png']
  const maxSize = 2 * 1024 * 1024 // 2MB
  const maxFiles = 3

  const fileArray = Array.from(files)

  for (const file of fileArray) {
    if (!allowedTypes.includes(file.type)) {
      imageErrors.value.push(`Format ${file.name} tidak didukung. Gunakan JPG/JPEG/PNG.`)
      continue
    }
    if (file.size > maxSize) {
      imageErrors.value.push(`${file.name} melebihi 2MB.`)
      continue
    }
    if (imagePreviews.value.length >= maxFiles) {
      imageErrors.value.push(`Maksimal ${maxFiles} foto.`)
      break
    }

    const url = URL.createObjectURL(file)
    imagePreviews.value.push({ file, url })
    form.images = imagePreviews.value.map((p) => p.file)
  }
}

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files) {
    validateAndAddFiles(target.files)
    target.value = ''
  }
}

const handleDrop = (event: DragEvent) => {
  isDragOver.value = false
  if (event.dataTransfer?.files) {
    validateAndAddFiles(event.dataTransfer.files)
  }
}

const removeImage = (index: number) => {
  const preview = imagePreviews.value[index]
  if (!preview) return
  URL.revokeObjectURL(preview.url)
  imagePreviews.value.splice(index, 1)
  form.images = imagePreviews.value.map((p) => p.file)
}

const metricDistributions = computed(() => {
  if (!result.value) return []

  return METRIC_DEFINITIONS.map((definition: MetricDefinition) => {
    const value = result.value?.[definition.field as keyof EvaluationData]
    const numeric = Number(value)
    const safeNumeric = Number.isFinite(numeric) ? numeric : 0
    const widthValue =
      definition.widthMode === 'percent' ? Math.min(100, Math.max(0, safeNumeric)) : 100
    const width = `${widthValue}%`
    const suffix = definition.valueSuffix ?? ''
    const displayValue = Number.isFinite(numeric)
      ? definition.id === 'ram'
        ? `${numeric}${suffix}`
        : `${numeric}${suffix}`
      : '-'

    return {
      id: definition.id,
      label: definition.label,
      icon: definition.icon,
      barWidth: width,
      barColor: metricColor(numeric, definition.metricKey),
      valueLabel: displayValue,
    }
  })
})

const clearAllErrors = () => {
  Object.keys(formErrors).forEach((key) => {
    formErrors[key] = ''
  })
}

const validateForm = (): boolean => {
  clearAllErrors()
  let isValid = true

  if (!form.laptop_name.trim()) {
    formErrors.laptop_name = 'Nama perangkat/model laptop wajib diisi.'
    isValid = false
  }

  if (!form.customer_name.trim()) {
    formErrors.customer_name = 'Nama customer wajib diisi.'
    isValid = false
  }

  if (form.lcd_score === null) {
    formErrors.lcd_score = 'Kondisi LCD wajib ditentukan.'
    isValid = false
  }

  if (form.battery_health === null) {
    formErrors.battery_health = 'Kesehatan baterai wajib ditentukan.'
    isValid = false
  }

  if (form.ram_capacity === null) {
    formErrors.ram_capacity = 'Kapasitas RAM wajib dipilih.'
    isValid = false
  }

  if (showManualProcessor.value) {
    if (!form.processor_name.trim()) {
      formErrors.processor_name = 'Nama processor wajib diisi pada input manual.'
      isValid = false
    }
    if (!form.processor_input || form.processor_input <= 0) {
      formErrors.processor_name = 'Benchmark processor harus lebih dari 0.'
      isValid = false
    }
  } else {
    if (form.processor_id === null) {
      formErrors.processor = 'Pilih processor dari daftar, atau gunakan input manual.'
      isValid = false
    }
  }

  if (form.keyboard_score === null) {
    formErrors.keyboard_score = 'Fungsi keyboard wajib ditentukan.'
    isValid = false
  }

  if (form.price === null || form.price <= 0) {
    formErrors.price = 'Harga pasaran wajib diisi dengan nilai yang valid.'
    isValid = false
  }

  if (!isValid) {
    const firstErrorField = document.querySelector('.border-error')
    if (firstErrorField) {
      firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' })
      ;(firstErrorField as HTMLElement).focus()
    }
  }

  return isValid
}

const handleEstimation = async () => {
  if (!validateForm()) return

  loading.value = true
  try {
    const response = await evaluate({
      customer_name: form.customer_name.trim(),
      laptop_name: form.laptop_name,
      price: form.price!,
      lcd_score: form.lcd_score!,
      keyboard_score: form.keyboard_score!,
      ram_capacity: form.ram_capacity!,
      battery_health: form.battery_health!,
      ...(showManualProcessor.value
        ? { processor_name: form.processor_name.trim(), processor_input: form.processor_input }
        : { processor_id: form.processor_id! }
      ),
      images: form.images.length > 0 ? form.images : undefined,
      description: form.description || undefined,
      use_ai: form.use_ai,
    })

    result.value = response

    await nextTick()
    if (resultSection.value) {
      resultSection.value.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }

    const aiFailed = form.use_ai && response.ai_used === false
    const hasWarning = response.description_ignored || aiFailed

    if (hasWarning) {
      const warnings: string[] = []
      if (response.description_ignored) {
        warnings.push('Catatan tambahan tidak diikutsertakan karena tidak relevan dengan analisis laptop.')
      }
      if (aiFailed) {
        warnings.push('Fitur Analisis AI tidak dapat digunakan saat ini. Hasil analisis menggunakan metode estimasi standar.')
      }
      Swal.fire({
        title: 'Estimasi Berhasil!',
        html: `Perangkat dikategorikan: <strong>${response.status}</strong><br><br><span style="color:#b45309;font-size:13px;">${warnings.join('<br>')}</span>`,
        icon: 'success',
        timer: 4000,
        showConfirmButton: false,
        background: '#faf9fd',
        customClass: { popup: 'rounded-[24px]' },
      })
    } else {
      Swal.fire({
        title: 'Estimasi Berhasil!',
        text: `Perangkat dikategorikan: ${response.status}`,
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
        background: '#faf9fd',
        customClass: { popup: 'rounded-[24px]' },
      })
    }
  } catch (error) {
    console.error('Gagal mengambil data estimasi:', error)
    const apiError = error as ApiError
    if (apiError.errors) {
      let hasFieldError = false
      const fieldMapping: Record<string, string> = {
        customer_name: 'customer_name',
        laptop_name: 'laptop_name',
        lcd: 'lcd_score',
        battery: 'battery_health',
        ram: 'ram_capacity',
        processor_id: 'processor',
        processor_name: 'processor_name',
        processor_input: 'processor_name',
        keyboard: 'keyboard_score',
        market_price: 'price',
        description: 'description',
        'images.0': 'images',
      }
      for (const [backendField, messages] of Object.entries(apiError.errors)) {
        const frontendField = fieldMapping[backendField] || backendField
        if (frontendField in formErrors) {
          const msg = Array.isArray(messages) ? messages[0] : messages
          if (msg) {
            formErrors[frontendField] = msg
            hasFieldError = true
          }
        }
      }
      if (hasFieldError) {
        const firstErrorField = document.querySelector('.border-error')
        if (firstErrorField) {
          firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' })
          ;(firstErrorField as HTMLElement).focus()
        }
        Swal.fire({
          title: 'Data Tidak Valid',
          text: apiError.message,
          icon: 'warning',
          background: '#faf9fd',
          customClass: { popup: 'rounded-[24px]' },
          timer: 3000,
          showConfirmButton: false,
        })
        return
      }
    }
    if (apiError.status === 0) {
      Swal.fire({
        title: 'Koneksi Gagal',
        text: apiError.message,
        icon: 'error',
        background: '#faf9fd',
        customClass: { popup: 'rounded-[24px]' },
      })
    } else if (apiError.status === 413) {
      Swal.fire({
        title: 'File Terlalu Besar',
        text: apiError.message,
        icon: 'error',
        background: '#faf9fd',
        customClass: { popup: 'rounded-[24px]' },
      })
    } else if (apiError.status === 500) {
      Swal.fire({
        title: 'Kesalahan Server',
        text: apiError.message,
        icon: 'error',
        background: '#faf9fd',
        customClass: { popup: 'rounded-[24px]' },
      })
    } else {
      Swal.fire({
        title: 'Kesalahan',
        text: apiError.message,
        icon: 'error',
        background: '#faf9fd',
        customClass: { popup: 'rounded-[24px]' },
      })
    }
  } finally {
    loading.value = false
  }
}

const handleExportPDF = async () => {
  if (!result.value) return

  pdfLoading.value = true

  Swal.fire({
    title: 'Menyiapkan pratinjau...',
    html: 'Harap tunggu sebentar.',
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading(),
    background: '#faf9fd',
    customClass: { popup: 'rounded-[24px]' },
  })

  try {
    const canvas = await renderReportToCanvas(result.value)
    const dataUrl = canvas.toDataURL('image/png')

    Swal.close()

    const confirmed = await Swal.fire({
      title: 'Pratinjau Laporan',
      html: `<img src="${dataUrl}" style="width:100%;max-width:760px;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,0.08);display:block;" />`,
      showCancelButton: true,
      confirmButtonText: 'Download PDF',
      cancelButtonText: 'Tutup',
      confirmButtonColor: '#002045',
      cancelButtonColor: '#74777f',
      background: '#faf9fd',
      color: '#1a1c1e',
      width: '820px',
      customClass: {
        popup: 'rounded-[24px]',
        confirmButton: 'rounded-full px-lg py-md font-label-bold',
        cancelButton: 'rounded-full px-lg py-md font-label-bold',
      },
    })

    if (!confirmed.isConfirmed) return

    await saveCanvasAsPDF(canvas, result.value.laptop_name, result.value.id)
    Swal.fire({
      title: 'PDF Berhasil diunduh!',
      icon: 'success',
      timer: 2000,
      showConfirmButton: false,
      background: '#faf9fd',
      customClass: { popup: 'rounded-[24px]' },
    })
  } catch (error) {
    console.error('Gagal mengekspor PDF:', error)
    Swal.close()
    Swal.fire({
      title: 'Gagal Mengekspor PDF',
      text: error instanceof Error
        ? error.message
        : 'Terjadi kesalahan saat menghasilkan PDF. Silakan coba lagi.',
      icon: 'error',
      background: '#faf9fd',
      customClass: { popup: 'rounded-[24px]' },
    })
  } finally {
    pdfLoading.value = false
  }
}

onMounted(async () => {
  try {
    processors.value = await getProcessors()
  } catch (error) {
    const apiError = error as ApiError
    console.error('Gagal memuat data processor:', apiError.message)
    if (apiError.status === 0) {
      Swal.fire({
        title: 'Koneksi Gagal',
        text: 'Tidak dapat memuat data processor. Pastikan backend berjalan.',
        icon: 'error',
        background: '#faf9fd',
        customClass: { popup: 'rounded-[24px]' },
        timer: 4000,
        showConfirmButton: false,
      })
    } else {
      Swal.fire({
        title: 'Gagal Memuat',
        text: 'Data processor tidak dapat dimuat. Anda masih bisa menggunakan input manual.',
        icon: 'warning',
        background: '#faf9fd',
        customClass: { popup: 'rounded-[24px]' },
        timer: 5000,
        showConfirmButton: false,
      })
    }
  }
})
</script>
