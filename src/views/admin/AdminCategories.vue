<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const categories = ref([])
const isLoading = ref(true)
const errorMessage = ref('')

// Modal state
const showModal = ref(false)
const isEditing = ref(false)
const isSaving = ref(false)
const form = ref({ id: null, name: '' })

const fetchCategories = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const response = await api.get('/categories')
    categories.value = response.data.data || response.data
  } catch (error) {
    console.error('Failed to fetch categories:', error)
    errorMessage.value = 'Gagal memuat kategori.'
  } finally {
    isLoading.value = false
  }
}

const openAddModal = () => {
  isEditing.value = false
  form.value = { id: null, name: '' }
  showModal.value = true
}

const openEditModal = (category) => {
  isEditing.value = true
  form.value = { id: category.id, name: category.name }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const saveCategory = async () => {
  if (!form.value.name.trim()) return
  isSaving.value = true
  try {
    if (isEditing.value) {
      await api.put(`/categories/${form.value.id}`, { name: form.value.name })
    } else {
      await api.post('/categories', { name: form.value.name })
    }
    await fetchCategories()
    closeModal()
  } catch (error) {
    console.error('Failed to save category:', error)
    errorMessage.value = 'Gagal menyimpan kategori.'
  } finally {
    isSaving.value = false
  }
}

const deleteCategory = async (category) => {
  if (!confirm(`Hapus kategori "${category.name}"?`)) return
  try {
    await api.delete(`/categories/${category.id}`)
    categories.value = categories.value.filter(c => c.id !== category.id)
  } catch (error) {
    console.error('Failed to delete category:', error)
    alert('Gagal menghapus kategori. Mungkin masih dipakai produk lain.')
  }
}

onMounted(() => {
  fetchCategories()
})
</script>

<template>
  <div class="admin-categories">
    <div class="page-header">
      <div>
        <h1>Kategori</h1>
        <p class="text-muted">Kelola kategori produk toko</p>
      </div>
      <button class="btn btn-primary" @click="openAddModal">
        + Tambah Kategori
      </button>
    </div>

    <div class="glass-card table-card">
      <div v-if="isLoading" class="state-message">Memuat kategori...</div>
      <div v-else-if="errorMessage" class="state-message error">{{ errorMessage }}</div>
      <div v-else-if="categories.length === 0" class="state-message">
        Belum ada kategori. Tambahkan kategori pertama kamu.
      </div>

      <table v-else class="data-table">
        <thead>
          <tr>
            <th>Nama Kategori</th>
            <th>Jumlah Produk</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cat in categories" :key="cat.id">
            <td>{{ cat.name }}</td>
            <td>{{ cat.products_count ?? '-' }}</td>
            <td class="text-right">
              <button class="btn-icon" @click="openEditModal(cat)" title="Edit">✏️</button>
              <button class="btn-icon danger" @click="deleteCategory(cat)" title="Hapus">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Add/Edit -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-box glass-card">
        <h3>{{ isEditing ? 'Edit Kategori' : 'Tambah Kategori' }}</h3>

        <form @submit.prevent="saveCategory">
          <div class="form-group">
            <label>Nama Kategori</label>
            <input
              v-model="form.name"
              type="text"
              class="form-input"
              placeholder="Contoh: Kaos, Jaket, Celana"
              required
            />
          </div>

          <div class="modal-actions">
            <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
            <button type="submit" class="btn btn-primary" :disabled="isSaving">
              {{ isSaving ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
}

.page-header h1 {
  margin: 0 0 0.25rem 0;
  font-size: 1.5rem;
}

.text-muted {
  color: var(--text-muted);
  margin: 0;
}

.table-card {
  padding: 0;
  overflow: hidden;
}

.state-message {
  padding: 3rem 1.5rem;
  text-align: center;
  color: var(--text-muted);
}

.state-message.error {
  color: #dc2626;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 0.9rem 1.5rem;
  text-align: left;
  border-bottom: 1px solid var(--border-color);
}

.data-table th {
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  color: var(--text-muted);
  font-weight: 600;
}

.text-right {
  text-align: right;
}

.btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1rem;
  padding: 0.35rem 0.5rem;
  border-radius: 6px;
  transition: var(--transition);
}

.btn-icon:hover {
  background-color: rgba(99, 102, 241, 0.1);
}

.btn-icon.danger:hover {
  background-color: rgba(220, 38, 38, 0.1);
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}

.modal-box {
  width: 100%;
  max-width: 420px;
  padding: 1.75rem;
}

.modal-box h3 {
  margin: 0 0 1.25rem 0;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.4rem;
  font-weight: 500;
  font-size: 0.9rem;
}

.form-input {
  width: 100%;
  padding: 0.6rem 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 0.95rem;
}

.form-input:focus {
  outline: none;
  border-color: var(--primary-color);
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}
</style>