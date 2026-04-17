import { defineStore } from 'pinia'

interface Product {
  id: number
  wholesaler_id: number
  name: string
  category: string
  price: string
  unit: string
  stock_qty: number
  image_url?: string
  description?: string
  is_active: boolean
}

export const useCartStore = defineStore('cart', () => {
  const items = ref<{ product: Product; quantity: number }[]>([])
  const wholesalerId = ref<number | null>(null)

  const total = computed(() =>
    items.value.reduce((sum, item) => sum + parseFloat(item.product.price) * item.quantity, 0)
  )
  const count = computed(() => items.value.reduce((n, i) => n + i.quantity, 0))

  function addItem(product: Product, qty = 1) {
    if (wholesalerId.value && wholesalerId.value !== product.wholesaler_id) {
      if (!confirm('Changer de grossiste vide le panier actuel. Continuer ?')) return
      items.value = []
    }
    wholesalerId.value = product.wholesaler_id
    const existing = items.value.find(i => i.product.id === product.id)
    if (existing) {
      existing.quantity += qty
    } else {
      items.value.push({ product, quantity: qty })
    }
  }

  function removeItem(productId: number) {
    items.value = items.value.filter(i => i.product.id !== productId)
    if (items.value.length === 0) wholesalerId.value = null
  }

  function updateQty(productId: number, qty: number) {
    const item = items.value.find(i => i.product.id === productId)
    if (item) {
      if (qty <= 0) removeItem(productId)
      else item.quantity = qty
    }
  }

  function clear() {
    items.value = []
    wholesalerId.value = null
  }

  return { items, wholesalerId, total, count, addItem, removeItem, updateQty, clear }
})
