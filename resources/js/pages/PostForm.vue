<template>
  <div class="page-body">
    <div class="container-xl" style="max-width: 640px;">
      <div class="mb-4">
        <router-link to="/" class="text-muted text-decoration-none small">
          &larr; Back to Posts
        </router-link>
        <h1 class="page-title mt-2">{{ isEdit ? 'Edit Post' : 'Create New Post' }}</h1>
        <p class="text-muted small mt-1">{{ isEdit ? 'Update the post details below' : 'Fill in the details to create a new post' }}</p>
      </div>

      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="error" class="alert alert-danger d-flex align-items-center mb-4" role="alert">
          <span class="me-2">!</span>
          <span>{{ error }}</span>
        </div>
      </Transition>

      <form @submit.prevent="submit" class="card">
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Title</label>
            <input
              v-model="form.title"
              type="text"
              required
              placeholder="Enter post title"
              class="form-control"
            />
          </div>

          <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea
              v-model="form.body"
              rows="8"
              required
              placeholder="Write your post content here..."
              class="form-control"
              style="resize: none;"
            ></textarea>
          </div>

          <div class="d-flex justify-content-end gap-2 pt-2">
            <router-link
              to="/"
              class="btn btn-outline-secondary"
            >
              Cancel
            </router-link>
            <button
              type="submit"
              :disabled="loading"
              class="btn btn-primary"
            >
              <span v-if="loading" class="spinner-border spinner-border-sm me-1" role="status"></span>
              {{ loading ? 'Saving...' : (isEdit ? 'Update Post' : 'Create Post') }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()
const loading = ref(false)
const error = ref('')

const isEdit = computed(() => !!route.params.id)

const form = reactive({
    title: '',
    body: '',
})

onMounted(async () => {
    if (isEdit.value) {
        try {
            const { data } = await api.get(`/posts/${route.params.id}`)
            form.title = data.data.title
            form.body = data.data.body
        } catch (e) {
            error.value = 'Failed to load post'
        }
    }
})

async function submit() {
    loading.value = true
    error.value = ''
    try {
        if (isEdit.value) {
            await api.put(`/posts/${route.params.id}`, form)
        } else {
            await api.post('/posts', form)
        }
        router.push({ name: 'dashboard' })
    } catch (e) {
        if (e.response?.status === 422) {
            const errors = e.response.data.errors
            error.value = Object.values(errors).flat().join(' ')
        } else {
            error.value = e.response?.data?.message || 'Failed to save post'
        }
    } finally {
        loading.value = false
    }
}
</script>
