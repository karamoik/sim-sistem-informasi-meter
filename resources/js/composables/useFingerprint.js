import { ref } from 'vue'
import FingerprintJS from '@fingerprintjs/fingerprintjs'

const fp = ref(null)
const fingerprintId = ref(null)
const deviceInfo = ref(null)

function detectBrowser() {
    const ua = navigator.userAgent
    if (ua.includes('Edg/')) return 'Edge'
    if (ua.includes('OPR/') || ua.includes('Opera')) return 'Opera'
    if (ua.includes('Chrome') && !ua.includes('Edg/')) return 'Chrome'
    if (ua.includes('Firefox')) return 'Firefox'
    if (ua.includes('Safari') && !ua.includes('Chrome')) return 'Safari'
    return 'Unknown'
}

function detectOS() {
    const ua = navigator.userAgent
    if (ua.includes('Windows')) return 'Windows'
    if (ua.includes('Mac OS')) return 'macOS'
    if (ua.includes('Linux') && !ua.includes('Android')) return 'Linux'
    if (ua.includes('Android')) return 'Android'
    if (ua.includes('iPhone') || ua.includes('iPad')) return 'iOS'
    return 'Unknown'
}

function detectDeviceType() {
    const ua = navigator.userAgent
    if (/tablet|ipad/i.test(ua)) return 'Tablet'
    if (/mobile|phone|android|iphone/i.test(ua)) return 'Mobile'
    return 'Desktop'
}

function detectWebGL() {
    try {
        const canvas = document.createElement('canvas')
        const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl')
        if (!gl) return { webgl_vendor: null, webgl_renderer: null }
        const info = gl.getExtension('WEBGL_debug_renderer_info')
        if (!info) return { webgl_vendor: null, webgl_renderer: null }
        return {
            webgl_vendor: gl.getParameter(info.UNMASKED_VENDOR_WEBGL),
            webgl_renderer: gl.getParameter(info.UNMASKED_RENDERER_WEBGL),
        }
    } catch {
        return { webgl_vendor: null, webgl_renderer: null }
    }
}

export function useFingerprint() {
    const loading = ref(false)

    async function getFingerprint() {
        if (fingerprintId.value && deviceInfo.value) {
            return { fingerprintId: fingerprintId.value, deviceInfo: deviceInfo.value }
        }

        loading.value = true
        try {
            if (!fp.value) {
                fp.value = await FingerprintJS.load()
            }
            const result = await fp.value.get()
            fingerprintId.value = result.visitorId

            deviceInfo.value = {
                os: detectOS(),
                browser: detectBrowser(),
                device_type: detectDeviceType(),
                screen: `${screen.width}x${screen.height}`,
                language: navigator.language,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                timezone_offset: new Date().getTimezoneOffset(),
                platform: navigator.platform || null,
                hardware_concurrency: navigator.hardwareConcurrency ?? null,
                device_memory: navigator.deviceMemory ?? null,
                max_touch_points: navigator.maxTouchPoints ?? 0,
                color_depth: screen.colorDepth ?? null,
                pixel_ratio: window.devicePixelRatio ?? null,
                screen_orientation: screen.orientation?.type ?? null,
                user_agent: navigator.userAgent,
                ...detectWebGL(),
            }

            return { fingerprintId: fingerprintId.value, deviceInfo: deviceInfo.value }
        } finally {
            loading.value = false
        }
    }

    return { fingerprintId, deviceInfo, loading, getFingerprint }
}