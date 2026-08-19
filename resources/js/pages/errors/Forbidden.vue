<template>
  <div class="container-xl py-4">
    <div class="card">
      <div class="card-body py-6">
        <div class="empty">
          <div class="empty-header text-danger">403</div>
          <p class="empty-title">Akses Ditolak</p>
          <p class="empty-subtitle text-secondary">
            Anda tidak memiliki izin untuk mengakses halaman ini. Hubungi administrator jika Anda merasa ini adalah kesalahan.
          </p>
          <div class="empty-action">
            <router-link to="/" class="btn btn-primary">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M13 18l6 -6" /><path d="M13 6l6 6" /></svg>
              Kembali ke Dashboard
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
</script>
