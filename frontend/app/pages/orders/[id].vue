<template>
  <div class="p-4 space-y-4">
    <!-- Header -->
    <div class="flex items-center gap-3">
      <button @click="$router.back()" class="text-gray-400 text-xl">←</button>
      <h2 class="text-lg font-bold text-gray-800">Détail commande</h2>
    </div>

    <div v-if="loading" class="text-center py-12 text-gray-400">Chargement...</div>

    <template v-else-if="order">
      <!-- Référence & statut -->
      <div class="card flex justify-between items-center">
        <div>
          <p class="font-bold text-gray-800">{{ order.reference }}</p>
          <p class="text-xs text-gray-400">{{ formatDate(order.created_at) }}</p>
        </div>
        <span :class="`badge-${order.status}`">{{ statusLabel(order.status) }}</span>
      </div>

      <!-- Infos interlocuteur -->
      <div class="card">
        <p class="text-xs text-gray-500 mb-1">{{ authStore.isWholesaler ? 'Détaillant' : 'Grossiste' }}</p>
        <p class="font-semibold text-gray-800">
          {{ authStore.isWholesaler ? order.retailer?.store_name || order.retailer?.name : order.wholesaler?.store_name || order.wholesaler?.name }}
        </p>
        <p class="text-sm text-gray-500">
          📞 {{ authStore.isWholesaler ? order.retailer?.phone : order.wholesaler?.phone }}
        </p>
      </div>

      <!-- Articles -->
      <div>
        <h3 class="font-bold text-gray-700 mb-2">Articles</h3>
        <div class="space-y-2">
          <div v-for="item in order.items" :key="item.id" class="card flex justify-between items-center">
            <div>
              <p class="font-semibold text-sm text-gray-800">{{ item.product?.name }}</p>
              <p class="text-xs text-gray-500">{{ item.quantity }} × {{ formatPrice(item.unit_price) }}</p>
            </div>
            <p class="font-bold text-green-700">{{ formatPrice(item.subtotal) }}</p>
          </div>
        </div>
      </div>

      <!-- Total -->
      <div class="card flex justify-between">
        <span class="font-bold">Total</span>
        <span class="font-bold text-green-700 text-lg">{{ formatPrice(order.total_amount) }}</span>
      </div>

      <!-- Note -->
      <div v-if="order.note" class="card bg-yellow-50 border-yellow-200">
        <p class="text-xs text-yellow-700 font-semibold mb-1">Note :</p>
        <p class="text-sm text-yellow-800">{{ order.note }}</p>
      </div>

      <!-- Actions grossiste -->
      <div v-if="authStore.isWholesaler" class="space-y-2">
        <button v-if="order.status === 'pending'" @click="updateStatus('confirmed')" class="btn-primary">
          ✅ Confirmer la commande
        </button>
        <button v-if="order.status === 'confirmed'" @click="updateStatus('preparing')" class="btn-primary">
          📦 Marquer en préparation
        </button>
        <button v-if="order.status === 'preparing'" @click="updateStatus('delivering')" class="btn-secondary">
          🚚 Marquer en livraison
        </button>
        <button v-if="order.status === 'delivering'" @click="updateStatus('delivered')" class="btn-primary">
          ✅ Marquer comme livrée
        </button>
        <button
          v-if="['pending', 'confirmed'].includes(order.status)"
          @click="updateStatus('cancelled')"
          class="btn-outline text-red-500 border-red-300"
        >
          ❌ Annuler la commande
        </button>
      </div>

      <!-- Annulation détaillant -->
      <div v-if="authStore.isRetailer && order.status === 'pending'">
        <button @click="cancelOrder" class="btn-outline text-red-500 border-red-300">
          ❌ Annuler ma commande
        </button>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

definePageMeta({ layout: 'default', middleware: 'auth' })

const route = useRoute()
const authStore = useAuthStore()
const { get, patch, del } = useApi()

const order = ref<any>(null)
const loading = ref(true)

async function loadOrder() {
  loading.value = true
  try {
    order.value = await get<any>(`/orders/${route.params.id}`)
  } catch (_) {}
  loading.value = false
}

async function updateStatus(status: string) {
  try {
    await patch(`/orders/${order.value.id}`, { status })
    order.value.status = status
  } catch (_) {}
}

async function cancelOrder() {
  try {
    await del(`/orders/${order.value.id}`)
    order.value.status = 'cancelled'
  } catch (_) {}
}

const statusLabel = (s: string) => ({
  pending: 'En attente', confirmed: 'Confirmée', preparing: 'En préparation',
  delivering: 'En livraison', delivered: 'Livrée', cancelled: 'Annulée',
}[s] || s)

const formatPrice = (n: any) =>
  new Intl.NumberFormat('fr-CG', { style: 'currency', currency: 'XAF', maximumFractionDigits: 0 }).format(Number(n))

const formatDate = (d: string) =>
  new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })

onMounted(loadOrder)
</script>
