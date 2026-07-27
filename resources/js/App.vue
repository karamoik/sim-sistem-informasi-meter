<template>
  <div v-if="!token">
    <router-view />
  </div>
  <div v-else class="page">
    <!-- Sidebar -->
    <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
      <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
          <router-link to="/" class="d-flex align-items-center gap-2 text-decoration-none">
            <div class="d-flex align-items-center justify-content-center" style="width:32px;height:32px;background:linear-gradient(135deg,#4263eb,#7c3aed);border-radius:8px;">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-bolt" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="white" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 3l0 7l6 0l-8 11l0 -7l-6 0l8 -11" /></svg>
            </div>
            <span class="d-none d-md-block fw-bold text-white">Monitoring</span>
          </router-link>
        </h1>
        <div class="collapse navbar-collapse" id="sidebar-menu">
          <ul class="navbar-nav pt-lg-3">
            <!-- Application -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#navbar-application" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-layout-dashboard" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4h6v6h-6z" /><path d="M14 4h6v6h-6z" /><path d="M4 14h6v6h-6z" /><path d="M17 14h3v6h-3z" /><path d="M14 14h6v6h-6z" /></svg>
                </span>
                <span class="nav-link-title">Application</span>
              </a>
              <div class="dropdown-menu" id="navbar-application">
                <router-link to="/" class="dropdown-item">
                  <span class="nav-link-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-layout-dashboard" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4h6v6h-6z" /><path d="M14 4h6v6h-6z" /><path d="M4 14h6v6h-6z" /><path d="M17 14h3v6h-3z" /><path d="M14 14h6v6h-6z" /></svg>
                  </span>
                  Dashboard
                </router-link>
                <router-link v-if="canWrite" to="/kwh-meters" class="dropdown-item">
                  <span class="nav-link-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-meter" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 12a8 8 0 0 1 16 0" /><path d="M12 12l3.5 -2.5" /><circle cx="12" cy="12" r="1" /></svg>
                  </span>
                  KWH Meter
                </router-link>
                <router-link to="/export-data" class="dropdown-item">
                  <span class="nav-link-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-download" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                  </span>
                  Ekspor Data
                </router-link>
              </div>
            </li>

            <!-- Master Data -->
            <li v-if="canWrite" class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#navbar-master" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-database" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><ellipse cx="12" cy="5" rx="9" ry="3" /><path d="M3 5v14a9 3 0 0 0 18 0v-14" /><path d="M3 12a9 3 0 0 0 18 0" /></svg>
                </span>
                <span class="nav-link-title">Master Data</span>
              </a>
              <div class="dropdown-menu" id="navbar-master">
                <router-link to="/gardu-induks" class="dropdown-item">
                  <span class="nav-link-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-building" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21h18" /><path d="M9 8h1" /><path d="M9 12h1" /><path d="M9 16h1" /><path d="M14 8h1" /><path d="M14 12h1" /><path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16" /></svg>
                  </span>
                  Gardu Induk
                </router-link>
                <router-link to="/titik-ukurs" class="dropdown-item">
                  <span class="nav-link-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-map-pin" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /><path d="M12.732 3.993a13 13 0 0 0 -3.503 2.062" /><path d="M17.087 19.683a13 13 0 0 0 3.49 -2.045" /><path d="M12 3c-4.97 0 -9 4.03 -9 9c0 5.33 4 10 9 10c5 0 9 -4.67 9 -10c0 -4.97 -4.03 -9 -9 -9z" /><path d="M12 3v9" /></svg>
                  </span>
                  Titik Ukur
                </router-link>
                <router-link to="/jenis-kwh-meters" class="dropdown-item">
                  <span class="nav-link-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-tag" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11.175 19.125a1.917 1.917 0 0 1 -2.024 -2.025l6.5 -6.5a2 2 0 0 1 2.35 -.35l3.125 3.125a2 2 0 0 1 -.35 2.35l-6.5 6.5a1.917 1.917 0 0 1 -2.025 -.35l-3.125 -3.125" /><circle cx="6.5" cy="6.5" r="1.5" /></svg>
                  </span>
                  Jenis KWH Meter
                </router-link>
                <router-link to="/konfigurasi-meters" class="dropdown-item">
                  <span class="nav-link-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-settings" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" /><circle cx="12" cy="12" r="3" /></svg>
                  </span>
                  Konfigurasi Meter
                </router-link>
              </div>
            </li>

            <!-- Management -->
            <li v-if="isAdmin" class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#navbar-mgmt" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-users" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                </span>
                <span class="nav-link-title">Management</span>
              </a>
              <div class="dropdown-menu" id="navbar-mgmt">
                <router-link to="/users" class="dropdown-item">
                  <span class="nav-link-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user-cog" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M12 11a4 4 0 1 0 0 .01" /><path d="M19.001 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M19.001 15.5v1.5" /><path d="M19.001 21v1.5" /><path d="M22.032 17.25l-1.219 .759" /><path d="M17.252 20.032l-.759 -1.219" /><path d="M22.032 22.75l-1.219 -.759" /><path d="M17.252 19.218l-.759 1.219" /></svg>
                  </span>
                  User Management
                </router-link>
              </div>
            </li>

            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#navbar-posts" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-article" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 5m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" /><path d="M7 7h10" /><path d="M7 13h10" /><path d="M7 17h4" /></svg>
                </span>
                <span class="nav-link-title">Posts</span>
              </a>
              <div class="dropdown-menu" id="navbar-posts">
                <router-link to="/posts" class="dropdown-item">
                  <span class="nav-link-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-list" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l6 0" /><path d="M9 12l6 0" /><path d="M9 18l6 0" /><path d="M5 6m0 1a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M5 12m0 1a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M5 18m0 1a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /></svg>
                  </span>
                  Semua Posts
                </router-link>
                <router-link to="/posts/create" class="dropdown-item">
                  <span class="nav-link-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                  </span>
                  Buat Post
                </router-link>
              </div>
            </li>

            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#navbar-account" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                </span>
                <span class="nav-link-title">Account</span>
              </a>
              <div class="dropdown-menu" id="navbar-account">
                <router-link to="/profile" class="dropdown-item">
                  <span class="nav-link-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                  </span>
                  Profil
                </router-link>
                <router-link to="/change-password" class="dropdown-item">
                  <span class="nav-link-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-lock" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z" /><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" /><path d="M8 11v-4a4 4 0 1 1 8 0v4" /></svg>
                  </span>
                  Ganti Password
                </router-link>
                <router-link to="/linked-devices" class="dropdown-item">
                  <span class="nav-link-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-desktop" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 5a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1v-10z" /><path d="M7 20h10" /><path d="M10 20v2" /><path d="M14 20v2" /></svg>
                  </span>
                  Linked Devices
                </router-link>
                <router-link to="/activity-log" class="dropdown-item">
                  <span class="nav-link-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-clock" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 7l0 5" /><path d="M10 14l4 -2" /></svg>
                  </span>
                  Riwayat Aktivitas
                </router-link>
                <div class="dropdown-divider"></div>
                <span class="dropdown-header text-uppercase text-muted" style="font-size:.65rem;letter-spacing:.05em">System</span>
                <router-link to="/notification-prefs" class="dropdown-item">
                  <span class="nav-link-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-bell" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-12a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" /><path d="M9 17v1a3 3 0 0 0 6 0v-1" /></svg>
                  </span>
                  Notifikasi
                </router-link>
                <router-link to="/appearance" class="dropdown-item">
                  <span class="nav-link-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-palette" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 21a9 9 0 0 1 0 -18c3.5 0 5.5 2 6 5l-2.5 1c-1.5 0 -3 -.5 -3.5 -1.5c-.5 1 -1.5 1.5 -3 1.5c-2 0 -3 -1.5 -3 -3.5c0 -3 2 -5 6 -5" /></svg>
                  </span>
                  Tampilan
                </router-link>
                <router-link to="/2fa/setup" class="dropdown-item">
                  <span class="nav-link-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-shield" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4v6c0 5.25 -3.75 9.75 -8 11c-4.25 -1.25 -8 -5.75 -8 -11v-6l8 -4z" /></svg>
                  </span>
                  Keamanan (2FA)
                </router-link>
                <router-link to="/keyboard-shortcuts" class="dropdown-item">
                  <span class="nav-link-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-keyboard" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M2 5h20a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-20a1 1 0 0 1 -1 -1v-10a1 1 0 0 1 1 -1z" /><path d="M6 8h.01" /><path d="M10 8h.01" /><path d="M14 8h.01" /><path d="M18 8h.01" /><path d="M8 12h.01" /><path d="M12 12h.01" /><path d="M16 12h.01" /><path d="M7 16h10" /></svg>
                  </span>
                  Pintasan Keyboard
                </router-link>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </aside>

    <div class="page-wrapper">
      <!-- Navbar -->
      <header class="navbar-expand-md">
        <div class="collapse navbar-collapse" id="navbar-menu">
          <div class="navbar">
            <div class="container-xl">
              <div class="navbar-autodark d-none-navbar-horizontal pe-0">
              </div>
              <div class="navbar-brand-wrapper d-none-navbar-horizontal pe-0 pe-md-3">
              </div>
              <ul class="navbar-nav">
                <li class="nav-item">
                  <a class="nav-link" href="#" @click.prevent="toggle" :title="isDark ? 'Light mode' : 'Dark mode'">
                    <svg v-if="isDark" xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7" /><path d="M14 10l2 -2m0 4l2 2m-4 -6l-2 -2" /></svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 3l0 18" /><path d="M12 3a9 9 0 0 0 0 18" /></svg>
                  </a>
                </li>
              </ul>
              <div class="navbar-nav flex-row order-md-last">
                <div class="nav-item dropdown">
                  <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                    <span class="avatar avatar-sm rounded-circle" :style="{ background: 'linear-gradient(135deg,#4263eb,#7c3aed)' }">
                      {{ (userName || 'U')[0].toUpperCase() }}
                    </span>
                    <div class="d-none d-xl-block ps-2">
                      <div>{{ userName }}</div>
                      <div class="mt-1 small text-muted">{{ userRole }}</div>
                    </div>
                  </a>
                  <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <div class="dropdown-header">
                      <strong>{{ userName }}</strong>
                      <div class="small text-muted">{{ userEmail }}</div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <router-link to="/profile" class="dropdown-item">
                      <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                      Profil
                    </router-link>
                    <router-link to="/change-password" class="dropdown-item">
                      <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-lock" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z" /><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" /><path d="M8 11v-4a4 4 0 1 1 8 0v4" /></svg>
                      Ganti Password
                    </router-link>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item" @click.prevent="logout">
                      <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-logout" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" /><path d="M9 12h12l-3 -3" /><path d="M18 15l3 -3" /></svg>
                      Logout
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Page content -->
      <div class="page-body">
        <div class="container-xl">
          <router-view />
        </div>
      </div>

      <!-- Footer -->
      <footer class="footer footer-transparent d-print-none">
        <div class="container-xl">
          <div class="row text-center align-items-center flex-row-reverse">
            <div class="col-lg-auto ms-lg-auto">
              <ul class="list-inline list-inline-dots mb-0">
                <li class="list-inline-item">
                  Monitoring Dashboard &copy; {{ new Date().getFullYear() }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/services/api'
import { useDarkMode } from '@/composables/useDarkMode'

const router = useRouter()
const route = useRoute()
const { isDark, toggle } = useDarkMode()

const token = ref(localStorage.getItem('token'))
const userName = ref('')
const userEmail = ref('')
const userRole = ref('')

const isAdmin = computed(() => userRole.value === 'admin')
const canWrite = computed(() => ['admin', 'operator'].includes(userRole.value))

function loadUser() {
    token.value = localStorage.getItem('token')
    try {
        const u = JSON.parse(localStorage.getItem('user'))
        userName.value = u?.name || ''
        userEmail.value = u?.email || ''
        userRole.value = u?.role || ''
    } catch {
        userName.value = ''
        userEmail.value = ''
        userRole.value = ''
    }
}

onMounted(() => {
    loadUser()
})
watch(() => route.fullPath, loadUser)
router.afterEach(loadUser)

async function logout() {
    try { await api.post('/auth/logout') } catch {}
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    token.value = ''
    userName.value = ''
    userRole.value = ''
    router.push({ name: 'login' })
}
</script>
