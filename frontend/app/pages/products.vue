<template>
  <div class="pb-24">
    <!-- Header -->
    <div class="bg-white border-b px-4 py-4 flex items-center justify-between sticky top-0 z-10">
      <h1 class="text-lg font-bold text-gray-800">Mes produits</h1>
      <button @click="openModal(null)" class="btn-primary text-sm px-3 py-2 flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Ajouter
      </button>
    </div>

    <!-- Chargement -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="w-8 h-8 border-4 border-green-700 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <!-- Liste vide -->
    <div v-else-if="products.length === 0" class="text-center py-16 px-4">
      <div class="text-5xl mb-4">📦</div>
      <p class="text-gray-500 mb-4">Vous n'avez pas encore de produits.</p>
      <button @click="openModal(null)" class="btn-primary">Ajouter un produit</button>
    </div>

    <!-- Liste des produits -->
    <div v-else class="p-4 space-y-3">
      <div
        v-for="product in products"
        :key="product.id"
        class="card flex items-center gap-3"
      >
        <!-- Image ou placeholder -->
        <div class="w-16 h-16 rounded-lg bg-gray-100 flex-shrink-0 overflow-hidden">
          <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="w-full h-full object-cover" />
          <div v-else class="w-full h-full flex items-center justify-center text-2xl">🛒</div>
        </div>

        <!-- Infos -->
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2">
            <p class="font-semibold text-gray-800 truncate">{{ product.name }}</p>
            <span
              :class="product.is_available ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
              class="text-xs px-2 py-0.5 rounded-full flex-shrink-0"
            >
              {{ product.is_available ? 'Actif' : 'Inactif' }}
            </span>
          </div>
          <p class="text-xs text-gray-400 truncate">{{ product.category }}</p>
          <div class="flex items-center gap-3 mt-1">
            <span class="text-green-700 font-bold text-sm">{{ formatPrice(product.price) }} FCFA</span>
            <span class="text-xs text-gray-400">Unité: {{ product.unit }}</span>
            <span v-if="product.stock !== null" class="text-xs text-gray-400">Stock: {{ product.stock }}</span>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col gap-1 flex-shrink-0">
          <button @click="openModal(product)" class="text-blue-600 hover:bg-blue-50 p-1.5 rounded">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
          </button>
          <button @click="toggleAvailability(product)" class="text-orange-500 hover:bg-orange-50 p-1.5 rounded">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="product.is_available" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </button>
          <button @click="confirmDelete(product)" class="text-red-500 hover:bg-red-50 p-1.5 rounded">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Ajouter / Modifier -->
    <Transition name="modal">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="closeModal"></div>
        <div class="relative bg-white w-full max-w-lg rounded-t-2xl sm:rounded-2xl p-5 max-h-[90vh] overflow-y-auto">
          <h2 class="text-lg font-bold mb-4">{{ editingProduct ? 'Modifier le produit' : 'Nouveau produit' }}</h2>

          <form @submit.prevent="saveProduct" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nom du produit *</label>
              <input v-model="form.name" type="text" class="input-field" placeholder="Ex: Riz parfumé 25kg" required />
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Prix (FCFA) *</label>
                <input v-model.number="form.price" type="number" min="0" class="input-field" placeholder="15000" required />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unité *</label>
                <select v-model="form.unit" class="input-field" required>
                  <option value="sac">Sac</option>
                  <option value="carton">Carton</option>
                  <option value="bouteille">Bouteille</option>
                  <option value="kg">Kg</option>
                  <option value="litre">Litre</option>
                  <option value="pièce">Pièce</option>
                  <option value="lot">Lot</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                <select v-model="form.category" class="input-field">
                  <option value="">-- Choisir --</option>
                  <option value="Alimentation">Alimentation</option>
                  <option value="Boissons">Boissons</option>
                  <option value="Hygiène">Hygiène</option>
                  <option value="Nettoyage">Nettoyage</option>
                  <option value="Épices">Épices</option>
                  <option value="Cosmétiques">Cosmétiques</option>
                  <option value="Électronique">Électronique</option>
                  <option value="Autre">Autre</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Stock disponible</label>
                <input v-model.number="form.stock" type="number" min="0" class="input-field" placeholder="50" />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea v-model="form.description" class="input-field" rows="2" placeholder="Description du produit..."></textarea>
            </div>

            <div class="flex items-center gap-3">
              <button
                type="button"
                @click="form.is_available = !form.is_available"
                :class="form.is_available ? 'bg-green-700' : 'bg-gray-300'"
                class="relative w-12 h-6 rounded-full transition-colors duration-200 flex-shrink-0"
              >
                <span
                  :class="form.is_available ? 'translate-x-6' : 'translate-x-1'"
                  class="absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200 block"
                ></span>
              </button>
              <span class="text-sm text-gray-700">{{ form.is_available ? 'Disponible à la vente' : 'Non disponible' }}</span>
            </div>

            <div v-if="formError" class="text-sm text-red-600 bg-red-50 px-3 py-2 rounded-lg">{{ formError }}</div>

            <div class="flex gap-3 pt-2">
              <button type="button" @click="closeModal" class="btn-outline flex-1">Annuler</button>
              <button type="submit" :disabled="saving" class="btn-primary flex-1">
                {{ saving ? 'Enregistrement...' : (editingProduct ? 'Modifier' : 'Ajouter') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- Confirmation suppression -->
    <Transition name="modal">
      <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/50" @click="deleteTarget = null"></div>
        <div class="relative bg-white rounded-2xl p-5 w-full max-w-sm">
          <h3 class="font-bold text-gray-800 mb-2">Supprimer ce produit ?</h3>
          <p class="text-sm text-gray-500 mb-4">« {{ deleteTarget?.name }} » sera définitivement supprimé.</p>
          <div class="flex gap-3">
            <button @click="deleteTarget = null" class="btn-outline flex-1">Annuler</button>
            <button @click="deleteProduct" :disabled="saving" class="flex-1 bg-red-600 text-white rounded-xl py-2.5 font-semibold">
              {{ saving ? '...' : 'Supprimer' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'auth', layout: 'default' })

interface Product {
  id: number
  name: string
  description?: string
  price: number
  unit: string
  category?: string
  stock?: number | null
  image_url?: string | null
  is_available: boolean
}

const { get, post, put, del } = useApi()
const auth = useAuthStore()

const products = ref<Product[]>([])
const loading = ref(true)
const showModal = ref(false)
const saving = ref(false)
const formError = ref('')
const editingProduct = ref<Product | null>(null)
const deleteTarget = ref<Product | null>(null)

const emptyForm = () => ({
  name: '',
  description: '',
  price: 0,
  unit: 'sac',
  category: '',
  stock: null as number | null,
  is_available: true,
})

const form = ref(emptyForm())

async function loadProducts() {
  loading.value = true
  try {
    const data = await get<{ data: Product[] }>('/products')
    products.value = data.data ?? (data as any)
  } catch {
    products.value = []
  } finally {
    loading.value = false
  }
}

function openModal(product: Product | null) {
  editingProduct.value = product
  formError.value = ''
  if (product) {
    form.value = {
      name: product.name,
      description: product.description ?? '',
      price: product.price,
      unit: product.unit,
      category: product.category ?? '',
      stock: product.stock ?? null,
      is_available: product.is_available,
    }
  } else {
    form.value = emptyForm()
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingProduct.value = null
}

async function saveProduct() {
  saving.value = true
  formError.value = ''
  try {
    const payload = {
      name: form.value.name,
      description: form.value.description || undefined,
      price: form.value.price,
      unit: form.value.unit,
      category: form.value.category || undefined,
      stock: form.value.stock,
      is_available: form.value.is_available,
    }
    if (editingProduct.value) {
      const updated = await put<Product>(`/products/${editingProduct.value.id}`, payload)
      const idx = products.value.findIndex(p => p.id === editingProduct.value!.id)
      if (idx !== -1) products.value[idx] = updated
    } else {
      const created = await post<Product>('/products', payload)
      products.value.unshift(created)
    }
    closeModal()
  } catch (e: any) {
    formError.value = e?.data?.message ?? 'Une erreur est survenue'
  } finally {
    saving.value = false
  }
}

async function toggleAvailability(product: Product) {
  try {
    const updated = await put<Product>(`/products/${product.id}`, {
      is_available: !product.is_available,
    })
    const idx = products.value.findIndex(p => p.id === product.id)
    if (idx !== -1) products.value[idx] = updated
  } catch { /* silent */ }
}

function confirmDelete(product: Product) {
  deleteTarget.value = product
}

async function deleteProduct() {
  if (!deleteTarget.value) return
  saving.value = true
  try {
    await del(`/products/${deleteTarget.value.id}`)
    products.value = products.value.filter(p => p.id !== deleteTarget.value!.id)
    deleteTarget.value = null
  } catch (e: any) {
    formError.value = e?.data?.message ?? 'Erreur lors de la suppression'
  } finally {
    saving.value = false
  }
}

function formatPrice(val: number) {
  return new Intl.NumberFormat('fr-FR').format(val)
}

onMounted(() => {
  if (!auth.isWholesaler) navigateTo('/dashboard')
  else loadProducts()
})
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
