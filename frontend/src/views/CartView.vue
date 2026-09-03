<template>
  <div class="container">
    <h2 class="page-title">YOUR CART</h2>

    <!-- Loading State -->
    <div v-if="loading" class="state-msg">
      <span class="spinner"></span>
      <p>Loading your cart...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="cartItems.length === 0" class="empty-cart">
      <p>Your cart is currently empty.</p>
      <router-link to="/" class="btn-bape mt-4">Explore Collection</router-link>
    </div>

    <!-- Cart Items & Summary -->
    <div v-else class="cart-layout">
      <!-- Item List Section -->
      <div class="cart-items">
        <div v-for="item in cartItems" :key="item.id" class="cart-item">
          <div class="item-details">
            <img :src="item.product?.image" :alt="item.product?.name" class="product-img" />
            <div class="item-info">
              <span class="category-badge">{{ item.product?.category?.name || 'Streetwear' }}</span>
              <h3 class="product-name">{{ item.product?.name }}</h3>
              <span class="unit-price">Rp {{ Number(item.product?.price).toLocaleString('id-ID') }}</span>
            </div>
          </div>

          <!-- Quantity Controls & Actions -->
          <div class="item-actions">
            <div class="qty-control">
              <button 
                @click="updateQuantity(item.id, item.quantity - 1)" 
                :disabled="item.quantity <= 1 || processingId === item.id"
                class="btn-qty"
              >-</button>
              <span class="qty-number">{{ item.quantity }}</span>
              <button 
                @click="updateQuantity(item.id, item.quantity + 1)" 
                :disabled="processingId === item.id"
                class="btn-qty"
              >+</button>
            </div>

            <span class="subtotal">
              Rp {{ Number(item.product?.price * item.quantity).toLocaleString('id-ID') }}
            </span>

            <button 
              @click="removeItem(item.id)" 
              :disabled="processingId === item.id"
              class="btn-remove" 
              title="Remove Item"
            >✕</button>
          </div>
        </div>
      </div>

      <!-- Order Summary Card -->
      <div class="cart-summary">
        <h3 class="summary-title">SUMMARY</h3>
        <div class="summary-row">
          <span>Items Total</span>
          <span>Rp {{ totalPrice.toLocaleString('id-ID') }}</span>
        </div>
        <div class="summary-row">
          <span>Shipping Fee</span>
          <span class="text-gold">FREE</span>
        </div>
        <hr class="divider" />
        <div class="summary-row total-row">
          <span>Total</span>
          <span class="total-price">Rp {{ totalPrice.toLocaleString('id-ID') }}</span>
        </div>

        <router-link to="/checkout" class="btn-bape btn-checkout">
          PROCEED TO CHECKOUT
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../services/api';

const cartItems = ref([]);
const loading = ref(true);
const processingId = ref(null);

const fetchCart = async () => {
  try {
    const response = await api.get('/cart');
    cartItems.value = response.data;
  } catch (error) {
    console.error('Error fetching cart:', error);
  } finally {
    loading.value = false;
  }
};

const updateQuantity = async (cartId, newQty) => {
  if (newQty < 1) return;
  processingId.value = cartId;

  try {
    await api.put(`/cart/${cartId}`, { quantity: newQty });
    await fetchCart();
  } catch (error) {
    alert(error.response?.data?.message || 'Gagal mengubah jumlah');
  } finally {
    processingId.value = null;
  }
};

const removeItem = async (cartId) => {
  if (!confirm('Hapus produk ini dari keranjang?')) return;
  processingId.value = cartId;

  try {
    await api.delete(`/cart/${cartId}`);
    await fetchCart();
  } catch (error) {
    alert(error.response?.data?.message || 'Gagal menghapus item');
  } finally {
    processingId.value = null;
  }
};

const totalPrice = computed(() => {
  return cartItems.value.reduce((sum, item) => {
    return sum + (Number(item.product?.price || 0) * item.quantity);
  }, 0);
});

onMounted(() => {
  fetchCart();
});
</script>

<style scoped>
.page-title {
  font-size: 28px;
  letter-spacing: 3px;
  margin-bottom: 30px;
  border-bottom: 1px solid var(--border-color);
  padding-bottom: 10px;
}

.state-msg, .empty-cart {
  text-align: center;
  padding: 60px 0;
  color: var(--text-muted);
}

.empty-cart p {
  font-size: 16px;
  margin-bottom: 20px;
}

.cart-layout {
  display: grid;
  grid-template-columns: 1fr;
  gap: 30px;
}

@media (min-width: 992px) {
  .cart-layout {
    grid-template-columns: 2fr 1fr;
  }
}

/* Cart Items */
.cart-item {
  background-color: var(--bg-card);
  border: 1px solid var(--border-color);
  padding: 20px;
  margin-bottom: 15px;
  display: flex;
  flex-direction: column;
  gap: 15px;
  justify-content: space-between;
  border-radius: 4px;
}

@media (min-width: 600px) {
  .cart-item {
    flex-direction: row;
    align-items: center;
  }
}

.item-details {
  display: flex;
  align-items: center;
  gap: 15px;
}

.product-img {
  width: 80px;
  height: 80px;
  object-fit: cover;
  background-color: var(--bg-secondary);
  border: 1px solid var(--border-color);
}

.category-badge {
  font-size: 10px;
  color: var(--accent-gold);
  letter-spacing: 1px;
  font-weight: 700;
  text-transform: uppercase;
}

.product-name {
  font-size: 16px;
  font-weight: 700;
  margin: 2px 0;
}

.unit-price {
  font-size: 12px;
  color: var(--text-muted);
}

.item-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
}

/* Quantity Control */
.qty-control {
  display: flex;
  align-items: center;
  border: 1px solid var(--border-color);
  background-color: var(--bg-secondary);
}

.btn-qty {
  background: none;
  border: none;
  color: var(--text-main);
  width: 30px;
  height: 30px;
  cursor: pointer;
  font-weight: bold;
}

.btn-qty:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.qty-number {
  padding: 0 10px;
  font-size: 13px;
  font-weight: bold;
}

.subtotal {
  font-weight: 700;
  font-size: 14px;
  min-width: 100px;
  text-align: right;
}

.btn-remove {
  background: none;
  border: none;
  color: #ff4d4d;
  font-size: 16px;
  cursor: pointer;
  padding: 5px;
  transition: opacity 0.2s;
}

.btn-remove:hover {
  opacity: 0.7;
}

/* Order Summary */
.cart-summary {
  background-color: var(--bg-card);
  border: 1px solid var(--border-color);
  padding: 25px;
  height: fit-content;
  border-radius: 4px;
}

.summary-title {
  font-size: 18px;
  letter-spacing: 2px;
  margin-bottom: 20px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  margin-bottom: 12px;
  color: var(--text-muted);
}

.divider {
  border: none;
  border-top: 1px solid var(--border-color);
  margin: 15px 0;
}

.total-row {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-main);
  margin-bottom: 20px;
}

.total-price, .text-gold {
  color: var(--accent-gold);
}

.btn-checkout {
  display: block;
  width: 100%;
  text-align: center;
  padding: 12px 0;
  margin-top: 10px;
}

.spinner {
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 2px solid var(--border-color);
  border-top-color: var(--accent-gold);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 10px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.mt-4 {
  margin-top: 16px;
}
</style>