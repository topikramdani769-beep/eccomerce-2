<template>
  <div class="container">
    <h2 class="page-title">CHECKOUT</h2>

    <div v-if="loading" class="state-msg">
      <span class="spinner"></span>
      <p>Loading checkout details...</p>
    </div>

    <form v-else @submit.prevent="handleCheckout" class="checkout-layout">
      <!-- Shipping & Payment Form -->
      <div class="checkout-form">
        <div class="section-box">
          <h3 class="section-title">SHIPPING ADDRESS</h3>
          <div class="form-group mt-4">
            <label>Full Address</label>
            <textarea 
              v-model="address" 
              class="form-control" 
              rows="3" 
              placeholder="Jl. Sudirman No. 12, Jakarta Selatan, 12190"
              required
            ></textarea>
          </div>
        </div>

        <div class="section-box mt-6">
          <h3 class="section-title">PAYMENT METHOD</h3>
          <div class="payment-options mt-4">
            <label 
              v-for="method in paymentMethods" 
              :key="method.id" 
              :class="['payment-card', { active: selectedPayment === method.id }]"
            >
              <input type="radio" :value="method.id" v-model="selectedPayment" required />
              <div class="payment-info">
                <strong>{{ method.name }}</strong>
                <small>{{ method.account_number }} (a.n {{ method.account_holder }})</small>
              </div>
            </label>
          </div>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="order-summary">
        <h3 class="summary-title">ORDER SUMMARY</h3>
        
        <div class="summary-items">
          <div v-for="item in cartItems" :key="item.id" class="summary-item">
            <span class="item-name">{{ item.product?.name }} x {{ item.quantity }}</span>
            <span class="item-price">Rp {{ (item.product?.price * item.quantity).toLocaleString('id-ID') }}</span>
          </div>
        </div>

        <hr class="divider" />

        <div class="summary-row">
          <span>Shipping Fee</span>
          <span class="text-gold">FREE</span>
        </div>
        <div class="summary-row total-row">
          <span>Total Payment</span>
          <span class="total-price">Rp {{ totalPrice.toLocaleString('id-ID') }}</span>
        </div>

        <button type="submit" class="btn-bape btn-place-order" :disabled="submitting">
          <span v-if="!submitting">PLACE ORDER</span>
          <span v-else class="loader-container">
            <span class="spinner-sm"></span> PROCESSING...
          </span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../services/api';
import { useRouter } from 'vue-router';

const cartItems = ref([]);
const paymentMethods = ref([]);
const address = ref('');
const selectedPayment = ref(null);
const loading = ref(true);
const submitting = ref(false);
const router = useRouter();

const fetchData = async () => {
  try {
    const [cartRes, paymentRes] = await Promise.all([
      api.get('/cart'),
      api.get('/payment-methods')
    ]);
    cartItems.value = cartRes.data;
    paymentMethods.value = paymentRes.data;

    if (paymentMethods.value.length > 0) {
      selectedPayment.value = paymentMethods.value[0].id;
    }
  } catch (e) {
    console.error('Error loading checkout:', e);
  } finally {
    loading.value = false;
  }
};

const totalPrice = computed(() => {
  return cartItems.value.reduce((sum, item) => sum + (Number(item.product?.price || 0) * item.quantity), 0);
});

const handleCheckout = async () => {
  if (cartItems.value.length === 0) {
    return alert('Keranjang belanja kamu kosong!');
  }

  submitting.value = true;
  try {
    await api.post('/orders', {
      shipping_address: address.value,
      payment_method_id: selectedPayment.value
    });
    alert('Pesanan berhasil dibuat!');
    router.push('/orders');
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal membuat pesanan');
  } finally {
    submitting.value = false;
  }
};

onMounted(() => {
  fetchData();
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

.checkout-layout {
  display: grid;
  grid-template-columns: 1fr;
  gap: 30px;
}

@media (min-width: 992px) {
  .checkout-layout {
    grid-template-columns: 2fr 1fr;
  }
}

.section-box, .order-summary {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  padding: 25px;
  border-radius: 4px;
}

.section-title, .summary-title {
  font-size: 16px;
  letter-spacing: 2px;
}

.payment-card {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px;
  border: 1px solid var(--border-color);
  margin-top: 10px;
  cursor: pointer;
  background: var(--bg-secondary);
  transition: border-color 0.2s;
}

.payment-card.active {
  border-color: var(--accent-gold);
}

.payment-info small {
  display: block;
  color: var(--text-muted);
  font-size: 11px;
}

.summary-items {
  margin: 20px 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  margin-bottom: 10px;
  color: var(--text-muted);
}

.divider {
  border: none;
  border-top: 1px solid var(--border-color);
  margin: 15px 0;
}

.total-row {
  font-size: 16px;
  font-weight: bold;
  color: var(--text-main);
  margin-bottom: 20px;
}

.total-price, .text-gold {
  color: var(--accent-gold);
}

.btn-place-order {
  width: 100%;
  padding: 12px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.mt-4 { margin-top: 16px; }
.mt-6 { margin-top: 24px; }
.state-msg { text-align: center; padding: 50px 0; color: var(--text-muted); }
.spinner { display: inline-block; width: 20px; height: 20px; border: 2px solid var(--border-color); border-top-color: var(--accent-gold); border-radius: 50%; animation: spin 0.8s linear infinite; }
.spinner-sm { display: inline-block; width: 14px; height: 14px; border: 2px solid #000; border-top-color: transparent; border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>