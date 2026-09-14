<template>
  <div class="max-w-4xl">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h2 class="text-2xl font-bold">{{ isEdit ? 'Edit Produk' : 'Tambah Produk' }}</h2>
        <p class="text-gray-500 mt-1">{{ isEdit ? 'Perbarui informasi produk' : 'Lengkapi informasi produk baru' }}</p>
      </div>
      <router-link to="/admin/products" class="btn-outline">Kembali</router-link>
    </div>

    <form @submit.prevent="saveProduct" class="bg-white rounded-xl shadow-sm p-6">
      <!-- Basic Info -->
      <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk</label>
          <input v-model="form.name" type="text" required class="input-field" placeholder="Masukkan nama produk">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
          <select v-model="form.category_id" required class="input-field">
            <option value="">Pilih Kategori</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Harga</label>
          <input v-model.number="form.price" type="number" min="0" required class="input-field" placeholder="0">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Stok</label>
          <input v-model.number="form.stock" type="number" min="0" required class="input-field" placeholder="0">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">URL Gambar</label>
          <input v-model="form.image" type="text" class="input-field" placeholder="https://...">
        </div>

        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
          <textarea v-model="form.description" rows="4" class="input-field" placeholder="Deskripsi produk..."></textarea>
        </div>
      </div>

      <!-- Image Preview -->
      <div v-if="form.image" class="mb-6">
        <p class="text-sm font-medium text-gray-700 mb-2">Preview Gambar</p>
        <img :src="form.image" alt="Preview" class="w-32 h-32 object-cover rounded-lg border">
      </div>

      <p v-if="errorMessage" class="text-sm text-red-600 mb-4">{{ errorMessage }}</p>

      <!-- Actions -->
      <div class="flex justify-end gap-3 pt-4 border-t">
        <router-link to="/admin/products" class="btn-outline">Batal</router-link>
        <button type="submit" :disabled="isSaving" class="btn-primary disabled:opacity-50 disabled:cursor-not-allowed">
          {{ isSaving ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Tambah Produk') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../services/api'

const route = useRoute()
const router = useRouter()

const isEdit = computed(() => !!route.params.id)

const categories = ref([])
const isSaving = ref(false)
const errorMessage = ref('')

const form = ref({
  name: '',
  category_id: '',
  price: 0,
  stock: 0,
  image: '',
  description: '',
})

const fetchCategories = async () => {
  try {
    const response = await api.get('/categories')
    categories.value = response.data.data || response.data
  } catch (error) {
    console.error('Failed to fetch categories:', error)
  }
}

const fetchProduct = async () => {
  try {
    const response = await api.get(`/admin/products/${route.params.id}`)
    const data = response.data.data || response.data

    form.value = {
      name: data.name || '',
      category_id: data.category_id || '',
      price: data.base_price ?? data.price ?? 0,
      stock: data.stock ?? 0,
      image: data.image || '',
      description: data.description || '',
    }
  } catch (error) {
    console.error('Failed to fetch product:', error)
    errorMessage.value = 'Gagal memuat data produk.'
  }
}

const saveProduct = async () => {
  errorMessage.value = ''
  isSaving.value = true

  try {
    if (isEdit.value) {
      await api.put(`/admin/products/${route.params.id}`, form.value)
    } else {
      await api.post('/admin/products', form.value)
    }
    router.push('/admin/products')
  } catch (error) {
    console.error('Failed to save product:', error)
    errorMessage.value = error?.response?.data?.message || 'Gagal menyimpan produk.'
  } finally {
    isSaving.value = false
  }
}

onMounted(() => {
  fetchCategories()
  if (isEdit.value) {
    fetchProduct()
  }
})
</script> 