<template>
  <div class="p-4 space-y-4">
    <h2 class="text-xl font-bold text-gray-800">Catalogue</h2>

    <!-- Sélection du grossiste -->
    <div v-if="!selectedWholesaler">
      <p class="text-gray-500 text-sm mb-3">Choisissez votre grossiste</p>
      <input
        v-model="search"
        type="search"
        placeholder="🔍 Rechercher un grossiste..."
        class="input-field mb-3"
        @input="searchWholesalers"
      />
      <div v-if="loadingW" class="text-center py-6 text-gray-400">Chargement...</div>
      <div v-else class="space-y-2">
        <button
          v-for="w in wholesalers"
          :key="w.id"
          @click="selectWholesaler(w)"
          class="card w-full text-left flex items-center gap-3"
        >
          <span class="text-3xl">🏭</span>
          <div>
            <p class="font-semibold text-gray-800">{{ w.store_name || w.name }}</p>
            <p class="text-xs text-gray-500">{{ w.address }} — {{ w.city }}</p>
          </div>
        </button>
      </div>
    </div>

    <!-- Catalogue du grossiste -->
    <div v-else>
      <!-- Header grossiste -->
      <div class="flex items-center gap-3 mb-4">
        <button @click="selectedWholesaler = null" class="text-gray-400 text-xl">←</button>
        <div>
          <p class="font-bold text-gray-800">{{ selectedWholesaler.store_name || selectedWholesaler.name }}</p>
          <p class="text-xs text-gray-500">{{ selectedWholesaler.address }}</p>
        </div>
      </div>

      <!-- Filtres catégories -->
      <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
        <button
          v-for="cat in categories"
          :key="cat"
          @click="activeCategory = cat"
          class="shrink-0 px-3 py-1.5 rounded-full text-sm font-medium transition-colors"
          :class="activeCategory === cat ? 'bg-green-700 text-white' : 'bg-gray-100 text-gray-600'"
        >
          {{ cat }}
        </button>
      </div>

      <!-- Recherche produit -->
      <input v-model="productSearch" type="search" placeholder="🔍 Chercher un produit..." class="input-field" />

      <!-- Liste produits -->
      <div v-if="loadingP" class="text-center py-8 text-gray-400">Chargement des produits...</div>
      <div v-else-if="filteredProducts.length === 0" class="text-center py-8 text-gray-400">
        Aucun produit disponible
      </div>
      <div v-else class="space-y-2 mt-3">
        <div v-for="product in filteredProducts" :key="product.id" class="card flex items-center gap-3">
          <img
            :src="product.image_url || '/placeholder.png'"
            :alt="product.name"
            class="w-16 h-16 rounded-xl object-cover bg-gray-100 shrink-0"
          />
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-800 truncate">{{ product.name }}</p>
            <p class="text-green-700 font-bold">{{ formatPrice(product.price) }} / {{ product.unit }}</p>
            <p class="text-xs" :class="product.stock_qty > 0 ? 'text-green-600' : 'text-red-500'">
              {{ product.stock_qty > 0 ? '✅ Disponible' : '❌ Rupture de stock' }}
            </p>
          </div>
          <div v-if="product.stock_qty > 0" class="flex items-center gap-1 shrink-0">
            <button @click="decreaseQty(product)" class="w-8 h-8 rounded-full bg-gray-100 font-bold text-gray-600 flex items-center justify-center">−</button>
            <span class="w-6 text-center font-semibold text-sm">{{ getQty(product.id) }}</span>
            <button @click="cartStore.addItem(product)" class="w-8 h-8 rounded-full bg-green-700 text-white font-bold flex items-center justify-center">+</button>
          </div>
        </div>
      </div>

      <!-- Bouton panier flottant -->
      <div v-if="cartStore.count > 0" class="fixed bottom-20 left-1/2 -translate-x-1/2 w-full max-w-md px-4">
        <NuxtLink to="/cart" class="btn-secondary flex items-center justify-between shadow-lg">
          <span>🛒 {{ cartStore.count }} article(s)</span>
          <span>{{ formatPrice(cartStore.total) }} →</span>
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useCartStore } from '~/stores/cart'

definePageMeta({ layout: 'default', middleware: 'auth' })

const { get } = useApi()
const cartStore = useCartStore()

const search = ref('')
const wholesalers = ref<any[]>([])
const selectedWholesaler = ref<any>(null)
const loadingW = ref(true)
const loadingP = ref(false)
const products = ref<any[]>([])
const activeCategory = ref('Tous')
const productSearch = ref('')

const categories = computed(() => ['Tous', ...new Set(products.value.map((p: any) => p.category))])

const filteredProducts = computed(() => {
  let list = products.value
  if (activeCategory.value !== 'Tous') list = list.filter(p => p.category === activeCategory.value)
  if (productSearch.value) list = list.filter(p => p.name.toLowerCase().includes(productSearch.value.toLowerCase()))
  return list
})

async function searchWholesalers() {
  loadingW.value = true
  try {
    const data = await get<any>('/wholesalers', { search: search.value })
    wholesalers.value = data.data
  } catch (_) {}
  loadingW.value = false
}

async function selectWholesaler(w: any) {
  selectedWholesaler.value = w
  loadingP.value = true
  try {
    const data = await get<any>('/products', { wholesaler_id: w.id })
    products.value = data.data
  } catch (_) {}
  loadingP.value = false
}

function getQty(productId: number) {
  return cartStore.items.find(i => i.product.id === productId)?.quantity || 0
}

function decreaseQty(product: any) {
  const qty = getQty(product.id)
  if (qty > 0) cartStore.updateQty(product.id, qty - 1)
}

const formatPrice = (n: string | number) =>
  new Intl.NumberFormat('fr-CG', { style: 'currency', currency: 'XAF', maximumFractionDigits: 0 }).format(Number(n))

onMounted(searchWholesalers)
</script>
