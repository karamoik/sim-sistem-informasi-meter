import { ref, watch, onMounted } from 'vue'

const isDark = ref(false)

function apply(dark) {
    if (dark) {
        document.documentElement.setAttribute('data-bs-theme', 'dark')
    } else {
        document.documentElement.removeAttribute('data-bs-theme')
    }
}

export function useDarkMode() {
    onMounted(() => {
        const saved = localStorage.getItem('dark_mode')
        isDark.value = saved ? saved === 'true' : window.matchMedia('(prefers-color-scheme: dark)').matches
        apply(isDark.value)
    })

    watch(isDark, (val) => {
        apply(val)
        localStorage.setItem('dark_mode', val)
    })

    function toggle() {
        isDark.value = !isDark.value
    }

    return { isDark, toggle }
}
