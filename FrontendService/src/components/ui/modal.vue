<template>
  <div v-if="open" class="modal" role="presentation" @click.self="close">
    <div class="modal__panel" role="dialog" :aria-modal="true" :aria-labelledby="titleId">
      <header v-if="title || $slots.header" class="modal__header">
        <slot name="header">
          <h2 :id="titleId" class="modal__title">{{ title }}</h2>
        </slot>
        <button class="modal__close" type="button" aria-label="Tutup" @click="close">
          <span class="material-symbols-outlined">close</span>
        </button>
      </header>
      <div class="modal__body">
        <slot />
      </div>
      <footer v-if="$slots.footer" class="modal__footer">
        <slot name="footer" />
      </footer>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

defineOptions({ name: 'BaseModal' })

const props = withDefaults(
  defineProps<{
    open: boolean
    title?: string
    id?: string
  }>(),
  {
    title: '',
    id: '',
  },
)

const emit = defineEmits<{
  close: []
}>()

const titleId = computed(() => props.id || `modal-${Math.random().toString(36).slice(2)}`)

const close = () => emit('close')
</script>
