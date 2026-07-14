<template>
  <div ref="root" class="search-dropdown relative" @keydown.esc.prevent="closeMenu">
    <label v-if="label" :for="inputId" class="search-dropdown__label">{{ label }}</label>
    <div class="relative">
      <input
        :id="inputId"
        ref="inputRef"
        v-model="query"
        :disabled="disabled"
        :placeholder="placeholder"
        :required="required"
        :aria-expanded="isOpen"
        :aria-controls="listboxId"
        aria-autocomplete="list"
        aria-haspopup="listbox"
        role="combobox"
        class="search-dropdown__input"
        type="text"
        @focus="openMenu"
        @input="handleInput"
        @keydown.down.prevent="moveActive(1)"
        @keydown.up.prevent="moveActive(-1)"
        @keydown.enter.prevent="selectActive"
      />
      <span class="material-symbols-outlined search-dropdown__icon">expand_more</span>
    </div>
    <div v-if="isOpen" :id="listboxId" class="search-dropdown__menu" role="listbox">
      <button
        v-for="(option, index) in filteredOptions"
        :key="option.value"
        :aria-selected="option.value === modelValue"
        class="search-dropdown__option"
        :class="
          index === activeIndex
            ? 'search-dropdown__option--active'
            : option.value === modelValue
              ? 'search-dropdown__option--selected'
              : ''
        "
        type="button"
        role="option"
        @mousedown.prevent="selectOption(option)"
      >
        <span class="search-dropdown__option-label">{{ option.label }}</span>
        <span v-if="option.description" class="search-dropdown__option-description">{{
          option.description
        }}</span>
      </button>
      <p v-if="filteredOptions.length === 0" class="search-dropdown__empty">
        Tidak ada pilihan yang cocok.
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'

type SearchDropdownOption = {
  value: string | number
  label: string
  description?: string
}

const props = withDefaults(
  defineProps<{
    modelValue: string | number | null
    options: SearchDropdownOption[]
    placeholder?: string
    label?: string
    disabled?: boolean
    required?: boolean
    id?: string
  }>(),
  {
    placeholder: 'Cari pilihan...',
    label: '',
    disabled: false,
    required: false,
    id: '',
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: string | number | null]
  select: [option: SearchDropdownOption | null]
}>()

const root = ref<HTMLElement | null>(null)
const inputRef = ref<HTMLInputElement | null>(null)
const query = ref('')
const isOpen = ref(false)
const activeIndex = ref(0)
const listboxId = `search-dropdown-${Math.random().toString(36).slice(2)}`
const inputId = computed(() => props.id || listboxId)

const selectedOption = computed(
  () => props.options.find((option) => option.value === props.modelValue) ?? null,
)

const filteredOptions = computed(() => {
  const term = query.value.toLowerCase().trim()
  if (!term) return props.options
  return props.options.filter((option) => {
    const haystack = `${option.label} ${option.description ?? ''}`.toLowerCase()
    return haystack.includes(term)
  })
})

const openMenu = () => {
  if (props.disabled) return
  isOpen.value = true
  activeIndex.value = Math.min(activeIndex.value, Math.max(filteredOptions.value.length - 1, 0))
}

const closeMenu = () => {
  isOpen.value = false
  query.value = selectedOption.value?.label ?? ''
}

const handleInput = () => {
  emit('update:modelValue', null)
  emit('select', null)
  isOpen.value = true
  activeIndex.value = 0
}

const selectOption = (option: SearchDropdownOption) => {
  emit('update:modelValue', option.value)
  emit('select', option)
  query.value = option.label
  isOpen.value = false
}

const moveActive = (direction: 1 | -1) => {
  if (!isOpen.value) openMenu()
  const total = filteredOptions.value.length
  if (!total) return
  activeIndex.value = (activeIndex.value + direction + total) % total
}

const selectActive = () => {
  const option = filteredOptions.value[activeIndex.value]
  if (option) selectOption(option)
}

const handleClickOutside = (event: MouseEvent) => {
  if (!root.value?.contains(event.target as Node)) closeMenu()
}

watch(
  () => props.modelValue,
  () => {
    query.value = selectedOption.value?.label ?? ''
  },
  { immediate: true },
)

watch(
  () => props.options,
  () => {
    if (selectedOption.value) query.value = selectedOption.value.label
  },
)

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleClickOutside)
})
</script>
