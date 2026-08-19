import { ref } from 'vue'

const show = ref(false)
const message = ref('')
const type = ref('success')
let timer = null

function toast(msg, toastType = 'success', duration = 4000) {
    message.value = msg
    type.value = toastType
    show.value = true
    clearTimeout(timer)
    timer = setTimeout(() => { show.value = false }, duration)
}

export function useGlobalToast() {
    return { show, message, type, toast }
}