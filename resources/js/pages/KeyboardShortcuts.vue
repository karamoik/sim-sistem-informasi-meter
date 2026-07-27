<template>
  <div class="container-xl" style="max-width:720px">
    <div class="mb-3">
      <h1 class="page-title mb-1">Pintasan Keyboard</h1>
      <p class="text-muted small">Pintasan keyboard untuk navigasi cepat</p>
    </div>

    <div class="card">
      <div class="card-body">
        <div v-for="(group, gi) in groups" :key="gi" class="mb-4">
          <h6 class="text-uppercase text-secondary fw-semibold small mb-2">{{ group.label }}</h6>
          <table class="table table-hover">
            <tbody>
              <tr v-for="shortcut in group.items" :key="shortcut.keys.join('+')">
                <td class="text-secondary">{{ shortcut.desc }}</td>
                <td class="text-end">
                  <kbd v-for="key in shortcut.keys" :key="key" class="me-1">{{ key }}</kbd>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="alert alert-info mt-4">
      <div class="d-flex align-items-start">
        <div class="me-2 fs-5">i</div>
        <div>
          <p class="fw-medium mb-1">Tips</p>
          <p class="mb-0">Pintasan keyboard hanya aktif saat tidak sedang mengetik di input field atau textarea.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const groups = [
    {
        label: 'Navigasi',
        items: [
            { keys: ['Ctrl', 'D'], desc: 'Buka Dashboard' },
            { keys: ['Ctrl', 'M'], desc: 'Buka KWH Meter' },
            { keys: ['Ctrl', 'L'], desc: 'Buka Linked Devices' },
            { keys: ['Ctrl', ','], desc: 'Buka Pengaturan' },
        ],
    },
    {
        label: 'Aksi',
        items: [
            { keys: ['Ctrl', 'K'], desc: 'Cari / Quick Search' },
            { keys: ['Ctrl', 'E'], desc: 'Ekspor Data' },
            { keys: ['Ctrl', '/'], desc: 'Tampilkan Pintasan Keyboard' },
            { keys: ['Esc'], desc: 'Tutup Modal / Batal' },
        ],
    },
    {
        label: 'Dark Mode',
        items: [
            { keys: ['Ctrl', 'Shift', 'D'], desc: 'Toggle Dark/Light Mode' },
        ],
    },
]
</script>
