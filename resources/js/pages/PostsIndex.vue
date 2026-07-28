<template>
  <div>
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
      <div>
        <h2 class="page-title">Posts</h2>
        <p class="text-muted">Manage your content here</p>
      </div>
      <router-link
        to="/posts/create"
        class="btn btn-primary"
      >
        + New Post
      </router-link>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="d-flex justify-content-center py-5">
      <div class="spinner-border text-blue" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="posts.length === 0" class="card text-center py-5">
      <div class="card-body">
        <p class="text-secondary mb-2" style="font-size: 3rem;">&#128196;</p>
        <h3 class="card-title">No posts yet</h3>
        <p class="text-secondary mb-3">Get started by creating your first post.</p>
        <router-link
          to="/posts/create"
          class="btn btn-primary d-inline-flex align-items-center gap-2"
        >
          + Create Post
        </router-link>
      </div>
    </div>

    <!-- Post List -->
    <div v-else class="d-flex flex-column gap-3">
      <div
        v-for="post in posts"
        :key="post.id"
        class="card"
      >
        <div class="card-body">
          <div class="d-flex flex-column flex-sm-row justify-content-between gap-3">
            <div class="flex-fill min-width-0">
              <h2 class="card-title">{{ post.title }}</h2>
              <div class="d-flex align-items-center gap-2 mb-2">
                <div class="avatar avatar-sm bg-blue text-white d-flex align-items-center justify-content-center rounded-circle">
                  <span class="small">{{ (post.user?.name || 'U')[0].toUpperCase() }}</span>
                </div>
                <span class="text-secondary small">{{ post.user?.name }}</span>
                <span class="text-secondary">&middot;</span>
                <span class="text-secondary small">{{ formatDate(post.created_at) }}</span>
              </div>
              <p class="text-secondary small mb-0">{{ post.body }}</p>
            </div>
            <div class="d-flex flex-sm-column gap-2 ms-sm-3">
              <router-link
                :to="`/posts/${post.id}/edit`"
                class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1"
              >
                Edit
              </router-link>
              <button
                @click="remove(post.id)"
                class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="d-flex justify-content-center align-items-center gap-1 mt-3" v-if="lastPage > 1">
        <button
          @click="fetchPosts(currentPage - 1)"
          :disabled="currentPage <= 1"
          class="page-item page-link"
        >
          &lsaquo;
        </button>
        <button
          v-for="page in lastPage"
          :key="page"
          @click="fetchPosts(page)"
          :class="[
            'page-item page-link',
            page === currentPage ? 'active' : ''
          ]"
        >
          {{ page }}
        </button>
        <button
          @click="fetchPosts(currentPage + 1)"
          :disabled="currentPage >= lastPage"
          class="page-item page-link"
        >
          &rsaquo;
        </button>
      </div>
    </div>

    <ConfirmDialog ref="confirmDialogRef" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import ConfirmDialog from '@/components/ConfirmDialog.vue'

const confirmDialogRef = ref(null)
const posts = ref([])
const loading = ref(true)
const currentPage = ref(1)
const lastPage = ref(1)

async function fetchPosts(page = 1) {
    loading.value = true
    try {
        const { data } = await api.get(`/posts?page=${page}`)
        posts.value = data.data.data
        currentPage.value = data.data.current_page
        lastPage.value = data.data.last_page
    } catch (e) {
        console.error('Failed to fetch posts', e)
    } finally {
        loading.value = false
    }
}

async function remove(id) {
    const ok = await confirmDialogRef.value.show({
        title: 'Hapus Post',
        message: 'Are you sure you want to delete this post?',
        type: 'danger',
        confirmText: 'Hapus',
    })
    if (!ok) return
    try {
        await api.delete(`/posts/${id}`)
        await fetchPosts(currentPage.value)
    } catch (e) {
        console.error('Failed to delete post', e)
    }
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    })
}

onMounted(() => fetchPosts())
</script>
