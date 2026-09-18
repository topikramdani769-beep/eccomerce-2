<template>
  <div class="cart-page">
    <h1 class="page-title">YOUR CART</h1>

    <!-- Tampilan jika keranjang kosong -->
    <div v-if="!cartStore.items || cartStore.items.length === 0" class="empty-cart">
      <p>KERANJANG BELANJA KAMU MASIH KOSONG.</p>
      <router-link to="/" class="btn-shop">BELANJA SEKARANG</router-link>
    </div>

    <!-- Tampilan jika keranjang ada isinya -->
    <div v-else class="cart-wrapper">
      <div class="cart-items-list">
        <div 
          v-for="item in cartStore.items" 
          :key="item.id" 
          class="cart-item-card"
        >
          <img :src="getImageUrl(item.product?.image || item.image)" :alt="item.product?.name || item.name" class="item-img" />
          
          <div class="item-info">
            <h3 class="item-title">{{ item.product?.name || item.name }}</h3>
            <span class="item-size" v-if="item.size">SIZE: {{ item.size }}</span>
            <span class="item-price">Rp {{ Number(item.product?.price || item.price).toLocaleString('id-ID') }}</span>
          </div>

          <div class="qty-control">
            <button @click="decreaseQty(item)" class="btn-qty">-</button>
            <span class="qty-num">{{ item.quantity }}</span>
            <button @click="increaseQty(item)" class="btn-qty">+</button>
          </div>

          <div class="item-total">
            Rp {{ (Number(item.product?.price || item.price) * item.quantity).toLocaleString('id-ID') }}
          </div>

          <button @click="removeItem(item.id)" class="btn-remove" title="Hapus Item">&times;</button>
        </div>
      </div>

      <!-- Ringkasan Total & Checkout -->
      <div class="cart-summary-box">
        <h3 class="summary-title">SUMMARY</h3>
        <div class="summary-row">
          <span>Total Item</span>
          <span>{{ cartStore.totalItems }} pcs</span>
        </div>
        <div class="summary-row total-row">
          <span>Total Price</span>
          <span>Rp {{ totalPrice.toLocaleString('id-ID') }}</span>
        </div>
        <button @click="handleCheckout" class="btn-checkout">PROCEED TO CHECKOUT</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useCartStore } from '../stores/cart';
import { useRouter } from 'vue-router';

const cartStore = useCartStore();
const router = useRouter();

// Ambil data keranjang dari backend saat halaman dimuat
onMounted(() => {
  if (typeof cartStore.fetchCart === 'function') {
    cartStore.fetchCart();
  }
});

const getImageUrl = (imagePath) => {
  if (!imagePath) return 'https://via.placeholder.com/150';
  if (imagePath.startsWith('http')) return imagePath;
  return `http://localhost:8000/storage/${imagePath.replace(/^\//, '')}`;
};

const totalPrice = computed(() => {
  if (!cartStore.items) return 0;
  return cartStore.items.reduce((sum, item) => {
    const price = Number(item.product?.price || item.price || 0);
    return sum + (price * item.quantity);
  }, 0);
});

const increaseQty = async (item) => {
  const newQty = item.quantity + 1;
  if (typeof cartStore.updateQuantity === 'function') {
    await cartStore.updateQuantity(item.id, newQty);
  } else {
    item.quantity = newQty;
  }
};

const decreaseQty = async (item) => {
  if (item.quantity > 1) {
    const newQty = item.quantity - 1;
    if (typeof cartStore.updateQuantity === 'function') {
      await cartStore.updateQuantity(item.id, newQty);
    } else {
      item.quantity = newQty;
    }
  } else {
    removeItem(item.id);
  }
};

const removeItem = async (id) => {
  if (typeof cartStore.removeItem === 'function') {
    await cartStore.removeItem(id);
  } else {
    cartStore.items = cartStore.items.filter(i => i.id !== id);
  }
};

const handleCheckout = () => {
  router.push('/checkout');
};
</script>

<style scoped>
/* Styling tetap sama seperti sebelumnya */
.cart-page {
  min-height: 80vh;
  padding: 40px 20px;
  background-color: #0d0d0d;
  color: #ffffff;
  max-width: 1200px;
  margin: 0 auto;
}
.page-title {
  font-size: 2.5rem;
  font-weight: 900;
  letter-spacing: 2px;
  margin-bottom: 30px;
  border-bottom: 2px solid #222;
  padding-bottom: 15px;
}
.empty-cart {
  text-align: center;
  padding: 80px 20px;
}
.empty-cart p {
  font-size: 1.2rem;
  color: #888;
  margin-bottom: 20px;
}
.btn-shop {
  display: inline-block;
  background: #ffffff;
  color: #000000;
  padding: 12px 30px;
  font-weight: bold;
  text-decoration: none;
  letter-spacing: 1px;
}
.cart-wrapper {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 30px;
}
@media (max-width: 900px) {
  .cart-wrapper {
    grid-template-columns: 1fr;
  }
}
.cart-items-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}
.cart-item-card {
  display: flex;
  align-items: center;
  gap: 20px;
  background: #141414;
  border: 1px solid #222;
  padding: 15px 20px;
}
.item-img {
  width: 90px;
  height: 90px;
  object-fit: cover;
  background: #000;
  border: 1px solid #333;
}
.item-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 5px;
}
.item-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0;
}
.item-size {
  font-size: 0.85rem;
  color: #e63946;
  font-weight: bold;
}
.item-price {
  font-size: 0.95rem;
  color: #aaa;
}
.qty-control {
  display: flex;
  align-items: center;
  border: 1px solid #333;
  background: #000;
}
.btn-qty {
  background: transparent;
  border: none;
  color: #fff;
  width: 32px;
  height: 32px;
  font-size: 1.1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}
.btn-qty:hover {
  background: #222;
}
.qty-num {
  padding: 0 12px;
  font-weight: bold;
  font-size: 0.95rem;
  color: #fff;
}
.item-total {
  font-size: 1.1rem;
  font-weight: bold;
  color: #ffffff;
  min-width: 120px;
  text-align: right;
}
.btn-remove {
  background: transparent;
  border: none;
  color: #666;
  font-size: 1.8rem;
  cursor: pointer;
  padding: 0 10px;
  line-height: 1;
}
.btn-remove:hover {
  color: #ff4d4d;
}
.cart-summary-box {
  background: #141414;
  border: 1px solid #222;
  padding: 25px;
  height: fit-content;
}
.summary-title {
  font-size: 1.3rem;
  font-weight: bold;
  margin-bottom: 20px;
  border-bottom: 1px solid #333;
  padding-bottom: 10px;
}
.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  color: #aaa;
}
.total-row {
  color: #fff;
  font-size: 1.2rem;
  font-weight: bold;
  border-top: 1px solid #333;
  padding-top: 15px;
  margin-top: 15px;
}
.btn-checkout {
  width: 100%;
  background: #ffffff;
  color: #000000;
  border: none;
  padding: 15px;
  font-weight: 900;
  letter-spacing: 1px;
  cursor: pointer;
  margin-top: 20px;
  transition: all 0.2s;
}
.btn-checkout:hover {
  background: #e63946;
  color: #ffffff;
}
</style>