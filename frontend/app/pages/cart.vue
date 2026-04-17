<template>
  <div class="p-4 space-y-4">
    <h2 class="text-xl font-bold text-gray-800">🛒 Mon panier</h2>

    <div v-if="cartStore.items.length === 0" class="text-center py-16 text-gray-400">
      <p class="text-5xl mb-4">🛒</p>
      <p>Votre panier est vide</p>
      <NuxtLink to="/catalog" class="btn-primary block mt-6">Parcourir le catalogue</NuxtLink>
    </div>

    <div v-else class="space-y-3">
      <!-- Items -->
      <div v-for="item in cartStore.items" :key="item.product.id" class="card flex items-center gap-3">
        <img
          :src="item.product.image_url || '/placeholder.png'"
          :alt="item.product.name"
          class="w-14 h-14 rounded-xl object-cover bg-gray-100 shrink-0"
        />
        <div class="flex-1 min-w-0">
          <p class="font-semibold text-gray-800 truncate">{{ item.product.name }}</p>
          <p class="text-green-700 text-sm">{{ formatPrice(item.product.price) }} / {{ item.product.unit }}</p>
          <p class="text-xs text-gray-500">Sous-total : {{ formatPrice(Number(item.product.price) * item.quantity) }}</p>
        </div>
        <div class="flex items-center gap-1 shrink-0">
          <button @click="cartStore.updateQty(item.product.id, item.quantity - 1)" class="w-8 h-8 rounded-full bg-gray-100 font-bold">−</button>
          <span class="w-6 text-center font-semibold">{{ item.quantity }}</span>
          <button @click="cartStore.updateQty(item.product.id, item.quantity + 1)" class="w-8 h-8 rounded-full bg-green-700 text-white font-bold">+</button>
        </div>
      </div>

      <!-- Note -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Note pour le grossiste (optionnel)</label>
        <textarea v-model="note" rows="2" placeholder="Ex: Livrer mardi matin..." class="input-field resize-none"></textarea>
      </div>

      <!-- Total -->
      <div class="card flex justify-between items-center">
        <span class="font-bold text-gray-700">Total</span>
        <span class="text-xl font-bold text-green-700">{{ formatPrice(cartStore.total) }}</span>
      </div>

      <p v-if="error" class="text-red-600 text-sm bg-red-50 p-3 rounded-lg">{{ error }}</p>
      <p v-if="success" class="text-green-700 text-sm bg-green-50 p-3 rounded-lg">✅ {{ success }}</p>

      <!-- Valider -->
      <button @click="placeOrder" class="btn-primary" :disabled="loading">
        {{ loading ? 'Envoi en cours...' : '✅ Valider la commande' }}
      </button>
      <button @click="cartStore.clear" class="btn-outline">Vider le panier</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useCartStore } from '~/stores/cart'

definePageMeta({ layout: 'default', middleware: 'auth' })

const cartStore = useCartStore()
const { post } = useApi()
const router = useRouter()

const note = ref('')
const loading = ref(false)
const error = ref('')
const success = ref('')

async function placeOrder() {
  if (!cartStore.wholesalerId) return
  loading.value = true
  error.value = ''
  try {
    await post('/orders', {
      wholesaler_id: cartStore.wholesalerId,
      note: note.value,
      items: cartStore.items.map(i => ({ product_id: i.product.id, quantity: i.quantity })),
    })
    success.value = 'Commande envoyée avec succès !'
    cartStore.clear()
    setTimeout(() => router.push('/orders'), 1500)
  } catch (e: any) {
    error.value = e?.data?.message || 'Erreur lors de l\'envoi de la commande.'
  } finally {
    loading.value = false
  }
}

const formatPrice = (n: number) =>
  new Intl.NumberFormat('fr-CG', { style: 'currency', currency: 'XAF', maximumFractionDigits: 0 }).format(n)
</script>
