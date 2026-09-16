<template>
  <div class="cart-page">
    <div class="container">
      <h2 class="page-title">YOUR CART</h2>

      <!-- Loading State -->
      <div v-if="loading" class="state-msg">
        <span class="spinner"></span>
        <p>LOADING YOUR CART...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="cartItems.length === 0" class="empty-cart">
        <p>YOUR CART IS CURRENTLY EMPTY.</p>
        <router-link to="/" class="btn-shop mt-4">EXPLORE COLLECTION</router-link>
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
            <span class="text-red">FREE</span>
          </div>
          <hr class="divider" />
          <div class="summary-row total-row">
            <span>Total</span>
            <span class="total-price">Rp {{ totalPrice.toLocaleString('id-ID') }}</span>
          </div>

          <router-link to="/checkout" class="btn-checkout">
            PROCEED TO CHECKOUT
          </router-link>
        </div>
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
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800;900&display=swap');

.cart-page {
  font-family: 'Montserrat', sans-serif;
  background-color: #f4f5f7;
  min-height: calc(100vh - 70px);
  padding: 40px 20px;
  color: #111111;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
}

.page-title {
  font-size: 26px;
  font-weight: 900;
  letter-spacing: 2px;
  margin-bottom: 30px;
  border-bottom: 2px solid #111111;
  padding-bottom: 12px;
  color: #111111;
}

.state-msg, .empty-cart {
  text-align: center;
  padding: 80px 20px;
  background: #ffffff;
  border: 1.5px solid #e0e0e0;
  border-radius: 6px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
}

.empty-cart p {
  font-size: 14px;
  font-weight: 800;
  letter-spacing: 1.5px;
  color: #555555;
  margin-bottom: 24px;
}

.btn-shop {
  display: inline-block;
  background: #111111;
  color: #ffffff;
  padding: 12px 28px;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 1.5px;
  text-decoration: none;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.btn-shop:hover {
  background: #e62129;
  color: #ffffff;
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

.cart-item {
  background-color: #ffffff;
  border: 1.5px solid #e0e0e0;
  padding: 20px;
  margin-bottom: 16px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  justify-content: space-between;
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
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
  gap: 16px;
}

.product-img {
  width: 84px;
  height: 84px;
  object-fit: cover;
  background-color: #f8f9fa;
  border: 1px solid #eee;
  border-radius: 4px;
}

.category-badge {
  font-size: 10px;
  color: #ffffff;
  background-color: #e62129;
  letter-spacing: 1.5px;
  font-weight: 800;
  padding: 2px 6px;
  text-transform: uppercase;
  border-radius: 2px;
  display: inline-block;
}

.product-name {
  font-size: 15px;
  font-weight: 800;
  margin: 6px 0 2px 0;
  color: #111111;
}

.unit-price {
  font-size: 13px;
  color: #666666;
  font-weight: 600;
}

.item-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
}

.qty-control {
  display: flex;
  align-items: center;
  background-color: #f8f9fa;
  border: 1.5px solid #dcdcdc;
  border-radius: 4px;
  padding: 2px;
}

.btn-qty {
  background: none;
  border: none;
  color: #111111;
  width: 32px;
  height: 32px;
  cursor: pointer;
  font-weight: 800;
  font-size: 14px;
}

.btn-qty:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.qty-number {
  padding: 0 10px;
  font-weight: 800;
  font-size: 13px;
}

.subtotal {
  font-weight: 800;
  font-size: 15px;
  min-width: 110px;
  text-align: right;
  color: #111111;
}

.btn-remove {
  background: none;
  border: none;
  color: #e62129;
  font-size: 16px;
  cursor: pointer;
  padding: 6px;
  transition: opacity 0.2s;
}

.btn-remove:hover {
  opacity: 0.6;
}

.cart-summary {
  background-color: #ffffff;
  border: 1.5px solid #e0e0e0;
  padding: 28px;
  height: fit-content;
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}

.summary-title {
  font-size: 18px;
  font-weight: 900;
  letter-spacing: 1.5px;
  margin-bottom: 20px;
  color: #111111;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 14px;
  color: #555555;
}

.divider {
  border: none;
  border-top: 1.5px solid #eeeeee;
  margin: 18px 0;
}

.total-row {
  font-size: 16px;
  font-weight: 900;
  color: #111111;
  margin-bottom: 24px;
}

.total-price {
  color: #111111;
  font-weight: 900;
}

.text-red {
  color: #e62129;
  font-weight: 800;
}

.btn-checkout {
  display: block;
  width: 100%;
  text-align: center;
  background: #111111;
  color: #ffffff;
  padding: 14px 0;
  font-size: 12px;
  font-weight: 900;
  letter-spacing: 1.5px;
  text-decoration: none;
  border-radius: 4px;
  transition: background 0.2s ease;
  box-sizing: border-box;
}

.btn-checkout:hover {
  background: #e62129;
  color: #ffffff;
}

.spinner {
  display: inline-block;
  width: 24px;
  height: 24px;
  border: 3px solid rgba(0, 0, 0, 0.1);
  border-top-color: #e62129;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 12px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.mt-4 {
  margin-top: 16px;
}
</style>