<template>
  <div class="checkout-page">
    <div class="container">
      <h2 class="page-title">CHECKOUT</h2>

      <div v-if="loading" class="state-msg">
        <span class="spinner"></span>
        <p>Loading checkout details...</p>
      </div>

      <form v-else @submit.prevent="handleCheckout" class="checkout-layout">
        <!-- Shipping Address Form -->
        <div class="checkout-form">
          <div class="section-box">
            <h3 class="section-title">SHIPPING ADDRESS</h3>
            <div class="form-group mt-4">
              <label>FULL ADDRESS</label>
              <textarea 
                v-model="address" 
                class="form-control" 
                rows="3" 
                placeholder="Jl. Sudirman No. 12, Jakarta Selatan, 12190"
                required
              ></textarea>
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
            <span class="text-red">FREE</span>
          </div>
          <div class="summary-row total-row">
            <span>Total Payment</span>
            <span class="total-price">Rp {{ totalPrice.toLocaleString('id-ID') }}</span>
          </div>

          <button type="submit" class="btn-submit btn-place-order" :disabled="submitting">
            <span v-if="!submitting">PAY WITH MIDTRANS</span>
            <span v-else class="loader-container">
              <span class="spinner-sm"></span> PROCESSING...
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../services/api';
import { useRouter } from 'vue-router';

const cartItems = ref([]);
const address = ref('');
const loading = ref(true);
const submitting = ref(false);
const router = useRouter();

// 1. Load Midtrans Snap Script dynamically or ensure it's in index.html
const loadMidtransScript = () => {
  return new Promise((resolve) => {
    if (window.snap) {
      return resolve(true);
    }
    const clientKey = "GANTI_DENGAN_MIDTRANS_CLIENT_KEY_ANDA"; // Atau ambil dari backend/env
    const script = document.createElement('script');
    script.src = "https://app.sandbox.midtrans.com/snap/snap.js"; // Ubah ke app.midtrans.com untuk production
    script.setAttribute('data-client-key', clientKey);
    script.onload = () => resolve(true);
    document.body.appendChild(script);
  });
};

const fetchData = async () => {
  loading.value = true;
  try {
    const cartRes = await api.get('/cart');
    const rawCartData = cartRes.data;
    let extractedCart = [];

    if (Array.isArray(rawCartData)) {
      extractedCart = rawCartData;
    } else if (Array.isArray(rawCartData?.data)) {
      extractedCart = rawCartData.data;
    } else if (Array.isArray(rawCartData?.cart_items)) {
      extractedCart = rawCartData.cart_items;
    } else if (Array.isArray(rawCartData?.items)) {
      extractedCart = rawCartData.items;
    }

    cartItems.value = extractedCart;
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
    // 2. Request Snap Token ke Backend Anda yang sudah terhubung Midtrans
    const response = await api.post('/orders', {
      shipping_address: address.value,
      address: address.value
    });

    // Asumsi backend mengembalikan token snap (misal: response.data.snap_token atau response.data.token)
    const snapToken = response.data.snap_token || response.data.token;

    if (!snapToken) {
      throw new Error('Snap token tidak ditemukan dari server.');
    }

    // 3. Panggil Midtrans Snap Popup
    window.snap.pay(snapToken, {
      onSuccess: function (result) {
        alert("Pembayaran berhasil!");
        console.log(result);
        router.push('/orders');
      },
      onPending: function (result) {
        alert("Menunggu pembayaran selesai.");
        console.log(result);
        router.push('/orders');
      },
      onError: function (result) {
        alert("Pembayaran gagal!");
        console.log(result);
      },
      onClose: function () {
        alert('Anda menutup popup pembayaran sebelum menyelesaikannya.');
      }
    });

  } catch (e) {
    alert(e.response?.data?.message || e.message || 'Gagal membuat pesanan');
  } finally {
    submitting.value = false;
  }
};

onMounted(async () => {
  await loadMidtransScript();
  fetchData();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800;900&display=swap');

.checkout-page {
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
  background: #ffffff;
  border: 1.5px solid #e0e0e0;
  padding: 28px;
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}

.section-title, .summary-title {
  font-size: 16px;
  font-weight: 900;
  letter-spacing: 1.5px;
  color: #111111;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 1.5px;
  color: #555555;
}

.form-control {
  width: 100%;
  padding: 12px 14px;
  background: #f8f9fa;
  border: 1.5px solid #dcdcdc;
  border-radius: 4px;
  font-family: inherit;
  font-size: 13px;
  font-weight: 600;
  color: #111111;
  box-sizing: border-box;
  transition: all 0.2s ease;
  resize: vertical;
}

.form-control:focus {
  outline: none;
  background: #ffffff;
  border-color: #e62129;
  box-shadow: 0 0 0 3px rgba(230, 33, 41, 0.1);
}

.empty-payment-msg {
  padding: 16px;
  background: #fff0f0;
  border: 1px solid #ffcdd2;
  border-radius: 4px;
  color: #d32f2f;
  font-size: 13px;
  font-weight: 600;
}

.payment-card {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 16px;
  border: 1.5px solid #e0e0e0;
  border-radius: 4px;
  margin-top: 10px;
  cursor: pointer;
  background: #f8f9fa;
  transition: all 0.2s ease;
}

.payment-card:hover {
  background: #ffffff;
}

.payment-card.active {
  border-color: #e62129;
  background: #ffffff;
  box-shadow: 0 0 0 1px #e62129;
}

.payment-card input[type="radio"] {
  accent-color: #e62129;
}

.payment-info strong {
  display: block;
  font-size: 13px;
  font-weight: 800;
  color: #111111;
}

.payment-info small {
  display: block;
  color: #666666;
  font-size: 11px;
  font-weight: 600;
  margin-top: 2px;
}

.summary-items {
  margin: 20px 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  font-weight: 600;
}

.item-name {
  color: #333333;
}

.item-price {
  font-weight: 800;
  color: #111111;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 12px;
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

.btn-submit {
  width: 100%;
  height: 48px;
  background: #111111;
  color: #ffffff;
  border: none;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 900;
  letter-spacing: 1.5px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  justify-content: center;
  align-items: center;
}

.btn-submit:hover:not(:disabled) {
  background: #e62129;
  color: #ffffff;
}

.btn-submit:disabled {
  background: #cccccc;
  color: #777777;
  cursor: not-allowed;
}

.loader-container {
  display: flex;
  align-items: center;
  gap: 8px;
}

.mt-4 { margin-top: 16px; }
.mt-6 { margin-top: 24px; }

.state-msg {
  text-align: center;
  padding: 80px 20px;
  background: #ffffff;
  border: 1.5px solid #e0e0e0;
  border-radius: 6px;
  color: #555555;
  font-weight: 700;
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

.spinner-sm {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid #ffffff;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>