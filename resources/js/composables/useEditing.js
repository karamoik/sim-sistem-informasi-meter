import { ref, onUnmounted } from 'vue'
import api from '@/services/api'
import { initEcho, getEcho } from '@/echo'

export function useEditing(model, recordId) {
    const otherEditors = ref([])
    const isEditingByOthers = ref(false)
    const warningMessage = ref('')
    const otherFieldChanges = ref({})
    const otherEditingFields = ref({})
    const suppressBroadcast = ref(false)
    let heartbeatTimer = null
    let echoChannel = null
    let fieldChangeTimers = {}
    let fieldEditingTimer = null

    async function startEditing() {
        if (!recordId.value) return

        try {
            const { data: res } = await api.post('/editing/start', {
                model,
                record_id: recordId.value,
            })

            if (res.data?.editing) {
                isEditingByOthers.value = true
                otherEditors.value = res.data.editors || []
                warningMessage.value = res.data.message || 'Data sedang diedit oleh user lain'
                listenForChanges()
                return false
            }

            isEditingByOthers.value = false
            otherEditors.value = []
            warningMessage.value = ''
            startHeartbeat()
            listenForChanges()
            return true
        } catch (e) {
            console.error('[editing] Failed to start editing session:', e)
            return true
        }
    }

    async function stopEditing() {
        if (!recordId.value) return

        stopHeartbeat()
        clearTimers()

        try {
            await api.post('/editing/stop', {
                model,
                record_id: recordId.value,
            })
        } catch (e) {
            console.error('[editing] Failed to stop editing session:', e)
        }

        leaveEditingChannel()
        isEditingByOthers.value = false
        otherEditors.value = []
        warningMessage.value = ''
        otherFieldChanges.value = {}
        otherEditingFields.value = {}
    }

    function broadcastFieldChange(field, value) {
        if (suppressBroadcast.value) return
        if (fieldChangeTimers[field]) clearTimeout(fieldChangeTimers[field])
        fieldChangeTimers[field] = setTimeout(async () => {
            delete fieldChangeTimers[field]
            if (!recordId.value) return
            try {
                await api.post('/editing/field-change', {
                    model,
                    record_id: recordId.value,
                    field,
                    value,
                })
            } catch (e) {
                console.error('[editing] field-change API failed:', e)
            }
        }, 500)
    }

    function broadcastFieldEditing(field) {
        clearTimeout(fieldEditingTimer)
        fieldEditingTimer = setTimeout(async () => {
            if (!recordId.value) return
            try {
                await api.post('/editing/field-editing', {
                    model,
                    record_id: recordId.value,
                    field,
                })
            } catch (e) {
                console.error('[editing] field-editing failed:', e)
            }
        }, 100)
    }

    function startHeartbeat() {
        stopHeartbeat()
        heartbeatTimer = setInterval(async () => {
            if (!recordId.value) return
            try {
                await api.post('/editing/heartbeat', {
                    model,
                    record_id: recordId.value,
                })
            } catch (e) {
                console.error('[editing] Heartbeat failed:', e)
            }
        }, 30000)
    }

    function stopHeartbeat() {
        if (heartbeatTimer) {
            clearInterval(heartbeatTimer)
            heartbeatTimer = null
        }
    }

    function clearTimers() {
        for (const t of Object.values(fieldChangeTimers)) clearTimeout(t)
        fieldChangeTimers = {}
        if (fieldEditingTimer) {
            clearTimeout(fieldEditingTimer)
            fieldEditingTimer = null
        }
    }

    function listenForChanges() {
        leaveEditingChannel()

        try {
            const echo = initEcho()
            if (!echo) {
                console.warn('[editing] Echo init failed, cannot listen')
                return
            }

            const channelName = `editing.${model}.${recordId.value}`

            echoChannel = echo.channel(channelName)
                .listen('.started', (event) => {
                    if (event.user_id !== getCurrentUserId()) {
                        isEditingByOthers.value = true
                        if (!otherEditors.value.find(e => e.user_id === event.user_id)) {
                            otherEditors.value.push({
                                user_id: event.user_id,
                                user_name: event.user_name,
                            })
                        }
                        warningMessage.value = `${event.user_name} sedang mengedit data ini`
                    }
                })
                .listen('.stopped', (event) => {
                    otherEditors.value = otherEditors.value.filter(e => e.user_id !== event.user_id)
                    const updated = { ...otherEditingFields.value }
                    delete updated[event.user_id]
                    otherEditingFields.value = updated
                    if (otherEditors.value.length === 0) {
                        isEditingByOthers.value = false
                        warningMessage.value = ''
                        otherEditingFields.value = {}
                    } else {
                        warningMessage.value = otherEditors.value.map(e => e.user_name).join(', ') + ' sedang mengedit data ini'
                    }
                })
                .listen('.field.changed', (event) => {
                    if (event.user_id !== getCurrentUserId()) {
                        otherFieldChanges.value = {
                            ...otherFieldChanges.value,
                            [event.field]: { value: event.value, user_name: event.user_name, timestamp: event.timestamp }
                        }
                    }
                })
                .listen('.field.editing', (event) => {
                    if (event.user_id !== getCurrentUserId()) {
                        if (event.field) {
                            otherEditingFields.value = {
                                ...otherEditingFields.value,
                                [event.user_id]: {
                                    field: event.field,
                                    user_name: event.user_name,
                                }
                            }
                        } else {
                            const updated = { ...otherEditingFields.value }
                            delete updated[event.user_id]
                            otherEditingFields.value = updated
                        }
                    }
                })
        } catch (e) {
            console.warn('[editing] Editing channel listener failed:', e)
        }
    }

    function leaveEditingChannel() {
        if (echoChannel) {
            const echoInstance = getEcho()
            if (echoInstance) {
                echoInstance.leave(`editing.${model}.${recordId.value}`)
            }
            echoChannel = null
        }
    }

    function getCurrentUserId() {
        try {
            const user = JSON.parse(localStorage.getItem('user'))
            return user?.id
        } catch {
            return null
        }
    }

    function getFieldEditors(fieldName) {
        const editors = []
        for (const [userId, data] of Object.entries(otherEditingFields.value)) {
            if (data.field === fieldName) {
                editors.push(data.user_name)
            }
        }
        return editors
    }

    function applyRemoteChanges(formRef) {
        const changes = otherFieldChanges.value
        if (!formRef.value || Object.keys(changes).length === 0) return

        suppressBroadcast.value = true
        for (const [field, data] of Object.entries(changes)) {
            if (formRef.value[field] !== undefined) {
                formRef.value[field] = data.value
            }
        }
        suppressBroadcast.value = false
        otherFieldChanges.value = {}
    }

    onUnmounted(() => {
        stopEditing()
    })

    return {
        otherEditors,
        isEditingByOthers,
        warningMessage,
        otherFieldChanges,
        otherEditingFields,
        suppressBroadcast,
        startEditing,
        stopEditing,
        broadcastFieldChange,
        broadcastFieldEditing,
        getFieldEditors,
        applyRemoteChanges,
    }
}
