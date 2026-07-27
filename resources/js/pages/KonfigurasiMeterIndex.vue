<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Konfigurasi Meter</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Mapping titik ukur dengan jenis KWH meter dan periode</p>
      </div>
      <button @click="openForm()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Tambah Konfigurasi
      </button>
      <button @click="toggleTrashed" :class="['inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl transition-colors', showTrashed ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 hover:bg-amber-200 dark:hover:bg-amber-900/50' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600']">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
        {{ showTrashed ? 'Kembali' : 'Data Terhapus' }}
      </button>
    </div>

    <div class="flex flex-col sm:flex-row gap-3">
      <div class="relative flex-1">
        <input v-model="search" type="text" placeholder="Cari periode, catatan..." class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" @input="fetchData" />
        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
      </div>
      <select v-model="filterStatus" class="text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" @change="fetchData">
        <option value="">Semua Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
        <option value="expired">Expired</option>
      </select>
    </div>

    <div v-if="!showTrashed" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
              <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Titik Ukur</th>
              <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Gardu Induk</th>
              <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Jenis Meter</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Periode</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Tanggal</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Status</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Dokumen</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="k in konfigurasiList" :key="k.id" class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
              <td class="px-6 py-3.5">
                <p class="font-medium text-gray-900 dark:text-white">{{ k.titik_ukur?.name }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 font-mono">{{ k.titik_ukur?.code }}</p>
              </td>
              <td class="px-6 py-3.5 text-gray-600 dark:text-gray-400">{{ k.titik_ukur?.gardu_induk?.name }}</td>
              <td class="px-6 py-3.5">
                <p class="font-medium text-gray-900 dark:text-white">{{ k.jenis_kwh_meter?.name }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500">{{ k.jenis_kwh_meter?.phase === 'single' ? '1 Phase' : '3 Phase' }} {{ k.jenis_kwh_meter?.max_capacity ? '- ' + Number(k.jenis_kwh_meter.max_capacity).toLocaleString('id-ID') + ' VA' : '' }}</p>
              </td>
              <td class="px-6 py-3.5 text-center">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">{{ k.period }}</span>
              </td>
              <td class="px-6 py-3.5 text-center text-xs text-gray-500 dark:text-gray-400">
                <div>{{ formatDate(k.start_date) }}</div>
                <div class="text-gray-400 dark:text-gray-500">s/d {{ k.end_date ? formatDate(k.end_date) : 'Sekarang' }}</div>
              </td>
              <td class="px-6 py-3.5 text-center">
                <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold', statusClass(k.status)]">
                  <span :class="['w-1.5 h-1.5 rounded-full', statusDotClass(k.status)]"></span>
                  {{ k.status }}
                </span>
              </td>
              <td class="px-6 py-3.5 text-center">
                <a v-if="k.document_path" :href="getDocumentUrl(k)" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                  PDF
                </a>
                <span v-else class="text-xs text-gray-400 dark:text-gray-500">-</span>
              </td>
              <td class="px-6 py-3.5 text-center">
                <div class="flex items-center justify-center gap-2">
                  <button @click="openForm(k)" class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors" title="Edit">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                  </button>
                  <button @click="confirmDelete(k)" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Hapus">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!konfigurasiList.length">
              <td colspan="8" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">Tidak ada data konfigurasi meter</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-else class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="px-6 py-3 bg-amber-50 dark:bg-amber-900/20 border-b border-amber-200 dark:border-amber-800">
        <p class="text-sm font-medium text-amber-700 dark:text-amber-400">Data yang sudah dihapus dapat dipulihkan kembali</p>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
              <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Periode</th>
              <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Titik Ukur</th>
              <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Jenis Meter</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Status</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Dihapus</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="k in trashedList" :key="k.id" class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors opacity-70">
              <td class="px-6 py-3.5 font-medium text-gray-900 dark:text-white">{{ k.period }}</td>
              <td class="px-6 py-3.5 text-gray-600 dark:text-gray-400">{{ k.titik_ukur?.name }}</td>
              <td class="px-6 py-3.5 text-gray-600 dark:text-gray-400">{{ k.jenis_kwh_meter?.name }}</td>
              <td class="px-6 py-3.5 text-center">
                <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold', statusClass(k.status)]">
                  <span :class="['w-1.5 h-1.5 rounded-full', statusDotClass(k.status)]"></span>
                  {{ k.status }}
                </span>
              </td>
              <td class="px-6 py-3.5 text-center text-xs text-gray-500 dark:text-gray-400">{{ formatDateTime(k.deleted_at) }}</td>
              <td class="px-6 py-3.5 text-center">
                <div class="flex items-center justify-center gap-2">
                  <button @click="restoreItem(k)" class="p-1.5 rounded-lg text-gray-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors" title="Pulihkan">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/></svg>
                  </button>
                  <button @click="forceDeleteItem(k)" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Hapus Permanent">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!trashedList.length">
              <td colspan="6" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">Tidak ada data terhapus</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showForm" class="fixed inset-0 z-[10000] flex items-start sm:items-center justify-center p-4 pt-20">
        <div class="absolute inset-0 bg-black/50" @click="showForm = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">{{ editingId ? 'Edit Konfigurasi' : 'Tambah Konfigurasi' }}</h3>
          <EditingIndicator
            :visible="isEditingByOthers"
            :message="warningMessage"
            :editors="otherEditors"
          />
          <form @submit.prevent="saveForm" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Titik Ukur</label>
              <select v-model="form.titik_ukur_id" required class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Pilih Titik Ukur</option>
                <option v-for="tu in allTitikUkurs" :key="tu.id" :value="tu.id">{{ tu.name }} ({{ tu.gardu_induk?.name }})</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jenis KWH Meter</label>
              <select v-model="form.jenis_kwh_meter_id" required class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Pilih Jenis Meter</option>
                <option v-for="j in allJenisMeters" :key="j.id" :value="j.id">{{ j.name }} ({{ j.phase === 'single' ? '1 Ph' : '3 Ph' }})</option>
              </select>
            </div>
            <div class="relative">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Periode</label>
              <input v-model="form.period" type="text" required placeholder="Contoh: 2026-Q3"
                class="w-full text-sm border rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:border-transparent transition-colors"
                :class="getFieldEditors('period').length ? 'border-amber-400 dark:border-amber-600 focus:ring-amber-500' : 'border-gray-200 dark:border-gray-600 focus:ring-blue-500'"
                @focus="broadcastFieldEditing('period')" @blur="broadcastFieldEditing(null)" />
              <FieldEditorBadge v-if="getFieldEditors('period').length" :editors="getFieldEditors('period')" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div class="relative">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Mulai</label>
                <input v-model="form.start_date" type="date" required
                  class="w-full text-sm border rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:border-transparent transition-colors"
                  :class="getFieldEditors('start_date').length ? 'border-amber-400 dark:border-amber-600 focus:ring-amber-500' : 'border-gray-200 dark:border-gray-600 focus:ring-blue-500'"
                  @focus="broadcastFieldEditing('start_date')" @blur="broadcastFieldEditing(null)" />
                <FieldEditorBadge v-if="getFieldEditors('start_date').length" :editors="getFieldEditors('start_date')" />
              </div>
              <div class="relative">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Akhir</label>
                <input v-model="form.end_date" type="date"
                  class="w-full text-sm border rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:border-transparent transition-colors"
                  :class="getFieldEditors('end_date').length ? 'border-amber-400 dark:border-amber-600 focus:ring-amber-500' : 'border-gray-200 dark:border-gray-600 focus:ring-blue-500'"
                  @focus="broadcastFieldEditing('end_date')" @blur="broadcastFieldEditing(null)" />
                <FieldEditorBadge v-if="getFieldEditors('end_date').length" :editors="getFieldEditors('end_date')" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
              <select v-model="form.status" class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="expired">Expired</option>
              </select>
            </div>
            <div class="relative">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan</label>
              <textarea v-model="form.notes" rows="2"
                class="w-full text-sm border rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:border-transparent resize-none transition-colors"
                :class="getFieldEditors('notes').length ? 'border-amber-400 dark:border-amber-600 focus:ring-amber-500' : 'border-gray-200 dark:border-gray-600 focus:ring-blue-500'"
                @focus="broadcastFieldEditing('notes')" @blur="broadcastFieldEditing(null)"></textarea>
              <FieldEditorBadge v-if="getFieldEditors('notes').length" :editors="getFieldEditors('notes')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dokumen PDF (opsional, max 2MB)</label>
              <div
                class="relative border-2 border-dashed rounded-xl p-4 text-center transition-colors"
                :class="documentDragOver ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : documentError ? 'border-red-400 bg-red-50 dark:bg-red-900/10' : 'border-gray-300 dark:border-gray-600 hover:border-blue-400 dark:hover:border-blue-500'"
                @dragover.prevent="documentDragOver = true"
                @dragleave="documentDragOver = false"
                @drop.prevent="handleDocumentDrop"
              >
                <input type="file" ref="documentInput" accept=".pdf" class="hidden" @change="handleDocumentSelect" />
                <div v-if="documentPreview" class="space-y-2">
                  <div class="flex items-center justify-center gap-2">
                    <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/></svg>
                    <div class="text-left">
                      <p class="text-sm font-medium text-gray-900 dark:text-white">{{ documentPreview.name }}</p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatFileSize(documentPreview.size) }}</p>
                    </div>
                  </div>
                  <button type="button" @click="removeDocument" class="text-xs text-red-600 dark:text-red-400 hover:underline">Hapus file</button>
                </div>
                <div v-else class="space-y-1">
                  <svg class="w-8 h-8 mx-auto text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12H9.75m0-3h6m-6 6h6m-5.25-9H17.25a2.25 2.25 0 012.25 2.25v1.5a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 14.25v-1.5A2.25 2.25 0 015.25 10.5h4.5"/></svg>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Drag & drop atau <button type="button" @click="documentInput.click()" class="text-blue-600 dark:text-blue-400 hover:underline">pilih file</button></p>
                  <p class="text-xs text-gray-400 dark:text-gray-500">Hanya file PDF, maks. 2MB</p>
                </div>
              </div>
              <p v-if="documentError" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ documentError }}</p>
            </div>
            <div class="flex gap-3 pt-2">
              <button type="button" @click="closeForm" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition-colors">Batal</button>
              <button type="submit" :disabled="saving" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors disabled:opacity-50">{{ saving ? 'Menyimpan...' : 'Simpan' }}</button>
            </div>
          </form>
          <p v-if="formError" class="mt-3 text-sm text-red-600 dark:text-red-400">{{ formError }}</p>
        </div>
      </div>
    </Teleport>

    <ConfirmDialog ref="confirmDialogRef" />
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import api from '@/services/api'
import { useEditing } from '@/composables/useEditing'
import EditingIndicator from '@/components/EditingIndicator.vue'
import FieldEditorBadge from '@/components/FieldEditorBadge.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import { initEcho, leaveChannel } from '@/echo'

const confirmDialogRef = ref(null)
const konfigurasiList = ref([])
const allTitikUkurs = ref([])
const allJenisMeters = ref([])
const search = ref('')
const filterStatus = ref('')
const showTrashed = ref(false)
const trashedList = ref([])
const showForm = ref(false)
const editingId = ref(null)
const editingRecordId = ref(null)
const { isEditingByOthers, warningMessage, otherEditors, otherFieldChanges, startEditing, stopEditing, broadcastFieldChange, broadcastFieldEditing, getFieldEditors, applyRemoteChanges } = useEditing('konfigurasi_meter', editingRecordId)
const saving = ref(false)
const formError = ref('')
const documentInput = ref(null)
const documentPreview = ref(null)
const documentDragOver = ref(false)
const documentError = ref('')
const existingDocumentPath = ref(null)

const form = ref({ titik_ukur_id: '', jenis_kwh_meter_id: '', period: '', start_date: '', end_date: '', status: 'active', notes: '' })

const lastBroadcastValues = ref({})
let formWatcher = null

function startFormWatch() {
  stopFormWatch()
  lastBroadcastValues.value = {}
  for (const [k, v] of Object.entries(form.value)) {
    lastBroadcastValues.value[k] = JSON.parse(JSON.stringify(v))
  }
  formWatcher = watch(
    form,
    () => {
      if (!editingId.value) return
      for (const [field, val] of Object.entries(form.value)) {
        const strNew = JSON.stringify(val)
        const strOld = JSON.stringify(lastBroadcastValues.value[field])
        if (strNew !== strOld) {
          lastBroadcastValues.value[field] = JSON.parse(strNew)
          broadcastFieldChange(field, val)
        }
      }
    },
    { deep: true, flush: 'sync' }
  )
}

function stopFormWatch() {
  if (formWatcher) {
    formWatcher()
    formWatcher = null
  }
}

watch(otherFieldChanges, () => {
  if (showForm.value) {
    applyRemoteChanges(form)
  }
})

async function fetchRefs() {
  try {
    const [tu, jm] = await Promise.all([api.get('/titik-ukurs'), api.get('/jenis-kwh-meters')])
    allTitikUkurs.value = tu.data.data || tu.data
    allJenisMeters.value = jm.data.data || jm.data
  } catch (e) { console.error(e) }
}

async function fetchData() {
  try {
    const params = {}
    if (search.value) params.search = search.value
    if (filterStatus.value) params.status = filterStatus.value
    const { data: res } = await api.get('/konfigurasi-meters', { params })
    konfigurasiList.value = res.data
  } catch (e) { console.error(e) }
}

async function fetchTrashed() {
  try {
    const { data: res } = await api.get('/konfigurasi-meters/trashed')
    trashedList.value = res.data
  } catch (e) { console.error(e) }
}

function toggleTrashed() {
  showTrashed.value = !showTrashed.value
  if (showTrashed.value) fetchTrashed()
  else fetchData()
}

async function restoreItem(item) {
  const ok = await confirmDialogRef.value.show({
    title: 'Pulihkan Data',
    message: `Pulihkan konfigurasi "${item.period}"?`,
    type: 'warning',
    confirmText: 'Pulihkan',
  })
  if (!ok) return
  try {
    await api.post(`/konfigurasi-meters/${item.id}/restore`)
    fetchTrashed()
    fetchData()
  } catch (e) { alert(e.response?.data?.message || 'Gagal memulihkan data') }
}

async function forceDeleteItem(item) {
  const ok = await confirmDialogRef.value.show({
    title: 'Hapus Permanen',
    message: `Hapus permanent konfigurasi "${item.period}"? Tindakan ini tidak dapat dibatalkan.`,
    type: 'danger',
    confirmText: 'Hapus Permanen',
  })
  if (!ok) return
  try {
    await api.delete(`/konfigurasi-meters/${item.id}/force-delete`)
    fetchTrashed()
  } catch (e) { alert(e.response?.data?.message || 'Gagal menghapus data') }
}

function formatDate(d) {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

function formatDateTime(d) {
  if (!d) return '-'
  return new Date(d).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function statusClass(s) {
  return { active: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400', inactive: 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400', expired: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' }[s] || ''
}

function statusDotClass(s) {
  return { active: 'bg-green-500', inactive: 'bg-gray-400', expired: 'bg-red-500' }[s] || ''
}

function openForm(item = null) {
  formError.value = ''
  documentPreview.value = null
  documentError.value = ''
  existingDocumentPath.value = null
  if (item) {
    editingId.value = item.id
    form.value = { titik_ukur_id: item.titik_ukur_id, jenis_kwh_meter_id: item.jenis_kwh_meter_id, period: item.period, start_date: item.start_date?.split('T')[0] || '', end_date: item.end_date?.split('T')[0] || '', status: item.status, notes: item.notes || '' }
    if (item.document_path) {
      existingDocumentPath.value = item.document_path
      documentPreview.value = { name: item.document_path.split('/').pop(), size: 0, file: null }
    }
  } else {
    editingId.value = null
    form.value = { titik_ukur_id: '', jenis_kwh_meter_id: '', period: '', start_date: '', end_date: '', status: 'active', notes: '' }
  }
  showForm.value = true
  if (editingId.value) {
    editingRecordId.value = editingId.value
    startEditing()
    startFormWatch()
  }
}

async function saveForm() {
  saving.value = true; formError.value = ''
  try {
    const hasDocument = documentPreview.value?.file
    if (hasDocument || editingId.value) {
      const formData = new FormData()
      for (const [key, val] of Object.entries(form.value)) {
        if (val !== '' && val !== null && val !== undefined) formData.append(key, val)
      }
      if (hasDocument) formData.append('document', documentPreview.value.file)

      if (editingId.value) {
        formData.append('_method', 'PUT')
        await api.post(`/konfigurasi-meters/${editingId.value}`, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
      } else {
        await api.post('/konfigurasi-meters', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
      }
    } else {
      if (editingId.value) await api.put(`/konfigurasi-meters/${editingId.value}`, form.value)
      else await api.post('/konfigurasi-meters', form.value)
    }
    showForm.value = false
    stopFormWatch()
    stopEditing()
    editingRecordId.value = null
    await fetchData()
  } catch (e) { formError.value = e.response?.data?.message || e.response?.data?.errors?.document?.[0] || 'Terjadi kesalahan' } finally { saving.value = false }
}

function closeForm() {
  stopFormWatch()
  stopEditing()
  showForm.value = false
  editingRecordId.value = null
}

async function confirmDelete(item) {
  const ok = await confirmDialogRef.value.show({
    title: 'Hapus Konfigurasi',
    message: `Hapus konfigurasi "${item.period}"?`,
    type: 'danger',
    confirmText: 'Hapus',
  })
  if (!ok) return
  try { await api.delete(`/konfigurasi-meters/${item.id}`); await fetchData() } catch (e) { console.error(e) }
}

function handleDocumentSelect(e) {
  const file = e.target.files[0]
  if (file) validateAndSetDocument(file)
}

function handleDocumentDrop(e) {
  documentDragOver.value = false
  const file = e.dataTransfer.files[0]
  if (file) validateAndSetDocument(file)
}

function validateAndSetDocument(file) {
  documentError.value = ''
  if (file.type !== 'application/pdf') {
    documentError.value = 'Hanya file PDF yang diperbolehkan'
    return
  }
  if (file.size > 2 * 1024 * 1024) {
    documentError.value = 'Ukuran file maksimal 2MB'
    return
  }
  documentPreview.value = { name: file.name, size: file.size, file }
}

function removeDocument() {
  documentPreview.value = null
  documentError.value = ''
  existingDocumentPath.value = null
  if (documentInput.value) documentInput.value.value = ''
}

function formatFileSize(bytes) {
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}

function getDocumentUrl(item) {
  const token = localStorage.getItem('token')
  return `/api/konfigurasi-meters/${item.id}/document?token=${token}`
}

function listenForCrudUpdates() {
  try {
    const echo = initEcho()
    if (!echo) return
    echo.channel('crud.konfigurasi_meter').listen('.updated', () => {
      fetchData()
    })
  } catch (e) {
    console.warn('Crud update listener failed:', e)
  }
}

onMounted(() => { fetchRefs(); fetchData(); listenForCrudUpdates() })

onBeforeUnmount(() => {
  stopFormWatch()
  leaveChannel('crud.konfigurasi_meter')
})
</script>
