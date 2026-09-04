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
          <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
          <input v-model="form.brand" type="text" class="input-field" placeholder="Contoh: Nike, Adidas">
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Harga Dasar</label>
          <input v-model="form.base_price" type="number" required class="input-field" placeholder="0">
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
          <select v-model="form.gender" class="input-field">
            <option value="pria">Pria</option>
            <option value="wanita">Wanita</option>
            <option value="unisex">Unisex</option>
          </select>
        </div>
        
        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
          <textarea v-model="form.description" rows="3" class="input-field" placeholder="Deskripsi produk"></textarea>
        </div>
        
        <div class="col-span-2">
          <label class="flex items-center space-x-2">
            <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
            <span class="text-sm text-gray-700">Produk Aktif</span>
          </label>
        </div>
      </div>

      <!-- Product Images -->
      <div class="mb-6">
        <h3 class="text-lg font-semibold mb-4">Gambar Produk</h3>
        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
          <input type="file" @change="handleImageUpload" multiple accept="image/*" class="hidden" id="imageInput">
          <label for="imageInput" class="cursor-pointer">
            <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-gray-600">Klik untuk upload gambar</p>
            <p class="text-sm text-gray-500">PNG, JPG, WEBP maks. 2MB</p>
          </label>
        </div>
        
        <div v-if="form.images.length > 0" class="flex gap-4 mt-4">
          <div v-for="(img, index) in form.images" :key="index" class="relative group">
            <img :src="img.url" class="w-20 h-20 object-cover rounded-lg" alt="Product image">
            <button @click="removeImage(index)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Variants -->
      <div class="mb-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">Varian Produk</h3>
          <button @click="addVariant" class="btn-outline px-3 py-1 text-sm">+ Tambah Varian</button>
        </div>
        
        <div v-if="form.variants.length === 0" class="text-center text-gray-500 py-6 border border-gray-200 rounded-lg">
          Belum ada varian. Tambahkan varian ukuran, warna, dan stok.
        </div>
        
        <div v-for="(variant, index) in form.variants" :key="index" class="border rounded-lg p-4 mb-3">
          <div class="flex justify-between items-center mb-3">
            <p class="font-medium">Varian {{ index + 1 }}</p>
            <button @click="removeVariant(index)" class="text-red-600 hover:text-red-700">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          
          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block text-sm text-gray-600 mb-1">Ukuran</label>
              <input v-model="variant.size" type="text" class="input-field" placeholder="M, L, XL">
            </div>
            <div>
              <label class="block text-sm text-gray-600 mb-1">Warna</label>
              <input v-model="variant.color" type="text" class="input-field" placeholder="Hitam, Putih">
            </div>
            <div>
              <label class="block text-sm text-gray-600 mb-1">Stok</label>
              <input v-model="variant.stock" type="number" class="input-field" placeholder="0">
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-end space-x-3">
        <router-link to="/admin/products" class="btn-outline px-4 py-2">Batal</router-link>
        <button type="submit" class="btn-primary px-4 py-2">
          {{ isEdit ? 'Update Produk' : 'Simpan Produk' }}
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

const form = ref({
  name: '',
  category_id: '',
  brand: '',
  base_price: '',
  description: '',
  gender: 'unisex',
  is_active: true,
  images: [],
  variants: []
})

const fetchCategories = async () => {
  try {
    const response = await api.get('/admin/categories')
    categories.value = response.data
  } catch (error) {
    console.error('Failed to fetch categories:', error)
  }
}

const fetchProduct = async () => {
  if (!isEdit.value) return
  
  try {
    const response = await api.get(`/admin/products/${route.params.id}`)
    const product = response.data
    
    form.value = {
      name: product.name,
      category_id: product.category_id,
      brand: product.brand,
      base_price: product.base_price,
      description: product.description,
      gender: product.gender,
      is_active: product.is_active,
      images: product.images || [],
      variants: product.variants || []
    }
  } catch (error) {
    console.error('Failed to fetch product:', error)
    alert('Gagal memuat data produk')
  }
}

const handleImageUpload = (event) => {
  const files = event.target.files
  
  for (const file of files) {
    const reader = new FileReader()
    reader.onload = (e) => {
      form.value.images.push({
        url: e.target.result,
        file
      })
    }
    reader.readAsDataURL(file)
  }
}

const removeImage = (index) => {
  form.value.images.splice(index, 1)
}

const addVariant = () => {
  form.value.variants.push({
    size: '',
    color: '',
    stock: 0
  })
}

const removeVariant = (index) => {
  form.value.variants.splice(index, 1)
}

const saveProduct = async () => {
  try {
    const productData = {
      name: form.value.name,
      category_id: form.value.category_id,
      brand: form.value.brand,
      base_price: form.value.base_price,
      description: form.value.description,
      gender: form.value.gender,
      is_active: form.value.is_active,
      variants: form.value.variants
    }
    
    if (isEdit.value) {
      await api.put(`/admin/products/${route.params.id}`, productData)
    } else {
      await api.post('/admin/products', productData)
    }
    
    router.push('/admin/products')
  } catch (error) {
    console.error('Failed to save product:', error)
    alert('Gagal menyimpan produk')
  }
}

onMounted(() => {
  fetchCategories()
  fetchProduct()
})
</script>