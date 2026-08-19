import { createRouter, createWebHistory } from 'vue-router'

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('@/pages/Login.vue'),
        meta: { guest: true },
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('@/pages/Register.vue'),
        meta: { guest: true },
    },
    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: () => import('@/pages/ForgotPassword.vue'),
        meta: { guest: true },
    },
    {
        path: '/reset-password',
        name: 'reset-password',
        component: () => import('@/pages/ResetPassword.vue'),
        meta: { guest: true },
    },
    {
        path: '/2fa/verify',
        name: '2fa.verify',
        component: () => import('@/pages/TwoFactorVerify.vue'),
        meta: { requiresTwoFactorToken: true },
    },
    {
        path: '/2fa/setup',
        name: '2fa.setup',
        component: () => import('@/pages/TwoFactorSetup.vue'),
        meta: { auth: true },
    },
    {
        path: '/',
        name: 'dashboard',
        component: () => import('@/pages/Dashboard.vue'),
        meta: { auth: true },
    },
    {
        path: '/monitoring/load-profiles',
        name: 'load-profiles',
        component: () => import('@/pages/LoadProfileMonitoring.vue'),
        meta: { auth: true },
    },
    {
        path: '/monitoring/load-profile-availability',
        name: 'load-profile-availability',
        component: () => import('@/pages/LoadProfileAvailability.vue'),
        meta: { auth: true },
    },
    {
        path: '/gardu-induks',
        name: 'gardu-induks',
        component: () => import('@/pages/GarduIndukIndex.vue'),
        meta: { auth: true, roles: ['admin', 'operator'] },
    },
    {
        path: '/titik-ukurs',
        name: 'titik-ukurs',
        component: () => import('@/pages/TitikUkurIndex.vue'),
        meta: { auth: true, roles: ['admin', 'operator'] },
    },
    {
        path: '/jenis-kwh-meters',
        name: 'jenis-kwh-meters',
        component: () => import('@/pages/JenisKwhMeterIndex.vue'),
        meta: { auth: true, roles: ['admin', 'operator'] },
    },
    {
        path: '/konfigurasi-meters',
        name: 'konfigurasi-meters',
        component: () => import('@/pages/KonfigurasiMeterIndex.vue'),
        meta: { auth: true, roles: ['admin', 'operator'] },
    },
    {
        path: '/kwh-meters',
        name: 'kwh-meters',
        component: () => import('@/pages/KwhMeterIndex.vue'),
        meta: { auth: true, roles: ['admin', 'operator'] },
    },
    {
        path: '/users',
        name: 'users',
        component: () => import('@/pages/UserManagement.vue'),
        meta: { auth: true, roles: ['admin'] },
    },
    {
        path: '/linked-devices',
        name: 'linked-devices',
        component: () => import('@/pages/LinkedDevices.vue'),
        meta: { auth: true },
    },
    {
        path: '/profile',
        name: 'profile',
        component: () => import('@/pages/Profile.vue'),
        meta: { auth: true },
    },
    {
        path: '/change-password',
        name: 'change-password',
        component: () => import('@/pages/ChangePassword.vue'),
        meta: { auth: true },
    },
    {
        path: '/activity-log',
        name: 'activity-log',
        component: () => import('@/pages/ActivityLog.vue'),
        meta: { auth: true },
    },
    {
        path: '/notification-prefs',
        name: 'notification-prefs',
        component: () => import('@/pages/NotificationPrefs.vue'),
        meta: { auth: true },
    },
    {
        path: '/appearance',
        name: 'appearance',
        component: () => import('@/pages/AppearanceSettings.vue'),
        meta: { auth: true },
    },
    {
        path: '/keyboard-shortcuts',
        name: 'keyboard-shortcuts',
        component: () => import('@/pages/KeyboardShortcuts.vue'),
        meta: { auth: true },
    },
    {
        path: '/403',
        name: 'forbidden',
        component: () => import('@/pages/errors/Forbidden.vue'),
        meta: { auth: true },
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('@/pages/errors/NotFound.vue'),
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('token')
    const twoFactorToken = localStorage.getItem('two_factor_token')

    if (to.meta.requiresTwoFactorToken && twoFactorToken) {
        return next()
    }

    if (to.meta.requiresTwoFactorToken && !twoFactorToken) {
        return next({ name: 'login' })
    }

    if (to.meta.auth && !token) {
        return next({ name: 'login' })
    }

    if (to.meta.guest && token) {
        return next({ name: 'dashboard' })
    }

    if (to.meta.roles) {
        try {
            const user = JSON.parse(localStorage.getItem('user'))
            if (!user || !to.meta.roles.includes(user.role)) {
                return next({ name: 'forbidden' })
            }
        } catch {
            return next({ name: 'login' })
        }
    }

    next()
})

export default router
