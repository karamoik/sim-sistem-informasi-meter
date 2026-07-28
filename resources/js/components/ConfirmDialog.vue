<template>
  <Teleport to="body">
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="visible" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" @keydown.escape="cancel">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="visible" class="modal-content">
              <div class="modal-body p-4">
                <div class="d-flex align-items-start gap-3">
                  <div :class="['d-flex align-items-center justify-content-center rounded-3 flex-shrink-0', iconBg]" style="width:2.75rem;height:2.75rem;">
                    <svg v-if="type === 'danger'" class="icon text-danger" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                      <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                      <path d="M12 9v4" /><path d="M10.916 17.925l-.062 .032a1 1 0 0 0 .555 .866l.392 .16c.368 .146 .753 .208 1.138 .182a2 2 0 0 0 1.263 -.606l.2 -.286" /><path d="M12 9v-4" /><circle cx="12" cy="17" r="1" fill="currentColor" />
                    </svg>
                    <svg v-else-if="type === 'warning'" class="icon text-warning" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                      <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                      <path d="M12 9v4" /><path d="M10.916 17.925l-.062 .032a1 1 0 0 0 .555 .866l.392 .16c.368 .146 .753 .208 1.138 .182a2 2 0 0 0 1.263 -.606l.2 -.286" /><path d="M12 9v-4" /><circle cx="12" cy="17" r="1" fill="currentColor" />
                    </svg>
                    <svg v-else class="icon text-primary" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                      <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                      <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 8l0 4" /><path d="M12 16l.01 0" />
                    </svg>
                  </div>
                  <div class="min-w-0 flex-fill">
                    <h3 class="fw-semibold mb-1">{{ title }}</h3>
                    <p class="text-secondary small mb-0">{{ message }}</p>
                  </div>
                </div>
              </div>
              <div class="modal-footer border-top pt-3">
                <button @click="cancel" class="btn btn-outline-secondary">{{ cancelText }}</button>
                <button @click="confirm" :class="['btn', confirmBtnClass]">{{ confirmText }}</button>
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const visible = ref(false)
const title = ref('')
const message = ref('')
const type = ref('danger')
const confirmText = ref('Konfirmasi')
const cancelText = ref('Batal')
let resolvePromise = null

const typeConfig = {
  danger: {
    iconBg: 'bg-danger bg-opacity-10',
    confirmBtn: 'btn-danger',
  },
  warning: {
    iconBg: 'bg-warning bg-opacity-10',
    confirmBtn: 'btn-warning',
  },
  info: {
    iconBg: 'bg-primary bg-opacity-10',
    confirmBtn: 'btn-primary',
  },
}

const iconBg = computed(() => typeConfig[type.value]?.iconBg || typeConfig.danger.iconBg)
const confirmBtnClass = computed(() => typeConfig[type.value]?.confirmBtn || typeConfig.danger.confirmBtn)

function handleKeydown(e) {
  if (e.key === 'Escape' && visible.value) cancel()
}

onMounted(() => document.addEventListener('keydown', handleKeydown))
onUnmounted(() => document.removeEventListener('keydown', handleKeydown))

function confirm() {
  visible.value = false
  resolvePromise?.(true)
}

function cancel() {
  visible.value = false
  resolvePromise?.(false)
}

function show(opts = {}) {
  title.value = opts.title || 'Konfirmasi'
  message.value = opts.message || ''
  type.value = opts.type || 'danger'
  confirmText.value = opts.confirmText || 'Konfirmasi'
  cancelText.value = opts.cancelText || 'Batal'
  visible.value = true
  return new Promise((resolve) => { resolvePromise = resolve })
}

defineExpose({ show })
</script>
