import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

let echo = null

export function initEcho() {
    if (echo) return echo

    try {
        echo = new Echo({
            broadcaster: 'reverb',
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST,
            wsPort: import.meta.env.VITE_REVERB_PORT,
            wssPort: import.meta.env.VITE_REVERB_PORT,
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME === 'https'),
            enabledTransports: ['ws', 'wss'],
        })

        return echo
    } catch (e) {
        console.warn('Echo init failed:', e)
        return null
    }
}

export function getEcho() {
    return echo
}

export function leaveChannel(channel) {
    if (echo) {
        echo.leave(channel)
    }
}
