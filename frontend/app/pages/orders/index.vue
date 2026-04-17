<template>
  <div class="p-4 space-y-4">
    <h2 class="text-xl font-bold text-gray-800">📋 Commandes</h2>

    <!-- Filtres statut -->
    <div class="flex gap-2 overflow-x-auto pb-1">
      <button
        v-for="f in filters"
        :key="f.value"
        @click="activeFilter = f.value; loadOrders()"
        class="shrink-0 px-3 py-1.5 rounded-full text-xs font-semibold transition-colors"
        :class="activeFilter === f.value ? 'bg-green-700 text-white' : 'bg-gray-100 text-gray-600'"
      >
        {{ f.label }}
      </button>
    </div>

    <div v-if="loading" class="text-center py-8 text-gray-400">Chargement...</div>

    <div v-else-if="orders.length === 0" class="text-center py-12 text-gray-400">
      <p class="text-4xl mb-3">📭</p>
      <p>Aucune commande</p>
    </div>

    <div v-else class="space-y-3">
      <NuxtLink
        v-for="order in orders"
        :key="order.id"
        :to="`/orders/${order.id}`"
        class="card block"
      >
        <div class="flex items-start justify-between">
          <div>
            <p class="font-bold text-gray-800 text-sm">{{ order.reference }}</p>
            <p class="text-xs text-gray-500 mt-0.5">
              {{ authStore.isWholesaler ? order.retailer?.store_name || order.retailer?.name : order.wholesaler?.store_name || order.wholesaler?.name }}
            </p>
            <p class="text-xs text-gray-400 mt-0.5">{{ formatDate(order.created_at) }}</p>
          </div>
          <div class="text-right">
            <span :class="`badge-${order.status}`">{{ statusLabel(order.status) }}</span>
            <p class="text-sm font-bold text-green-700 mt-1">{{ formatPrice(order.total_amount) }}</p>
          </div>
        </div>
        <p class="text-xs text-gray-400 mt-2">{{ order.items?.length || 0 }} article(s)</p>
      </NuxtLink>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

definePageMeta({ layout: 'default', middleware: 'auth' })

const authStore = useAuthStore()
const { get } = useApi()

const orders = ref<any[]>([])
const loading = ref(true)
const activeFilter = ref('')

const filters = [
  { value: '', label: 'Toutes' },
  { value: 'pending', label: 'En attente' },
  { value: 'confirmed', label: 'Confirmées' },
  { value: 'delivering', label: 'En livraison' },
  { value: 'delivered', label: 'Livrées' },
]

async function loadOrders() {
  loading.value = true
  try {
    const params: any = {}
    if (activeFilter.value) params.status = activeFilter.value
    const data = await get<any>('/orders', params)
    orders.value = data.data
  } catch (_) {}
  loading.value = false
}

const statusLabel = (s: string) => ({
  pending: 'En attente', confirmed: 'Confirmée', preparing: 'En préparation',
  delivering: 'En livraison', delivered: 'Livrée', cancelled: 'Annulée',
}[s] || s)

const formatPrice = (n: any) =>
  new Intl.NumberFormat('fr-CG', { style: 'currency', currency: 'XAF', maximumFractionDigits: 0 }).format(Number(n))

const formatDate = (d: string) =>
  new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })

onMounted(loadOrders)
</script>
