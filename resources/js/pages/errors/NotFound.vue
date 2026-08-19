<template>
  <div class="page error-page">
    <div class="container-tight py-6">
      <div class="empty">
        <div class="empty-header">404</div>
        <p class="empty-title">Halaman Tidak Ditemukan</p>
        <p class="empty-subtitle text-secondary">
          Halaman yang Anda cari tidak ada atau telah dipindahkan. Periksa kembali URL Anda.
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
</template>

<script setup>
</script>
