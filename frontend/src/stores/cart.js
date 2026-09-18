import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '../services/api';

export const useCartStore = defineStore('cart', () => {
  const items = ref([]);
  const toastMessage = ref('');
  const showToast = ref(false);
  let toastTimer = null;

  const totalItems = computed(() => {
    return items.value.reduce((total, item) => {
      const qty = Number(item.quantity) || 1;
      return total + qty;
    }, 0);
  });

  // Ambil data keranjang dari database backend
  async function fetchCart() {
    try {
      const response = await api.get('/cart');
      items.value = response.data?.data || response.data || [];
    } catch (error) {
      console.error('Gagal memuat keranjang:', error);
      items.value = [];
    }
  }

  // Tambah produk ke keranjang via API backend (Notif muncul spontan)
  async function addToCart(product) {
    if (!product || !product.id) return;

    // 1. Tampilkan notif secara instan detik itu juga
    triggerToast(`"${product.name}" ditambahkan ke keranjang!`);

    try {
      await api.post('/cart', {
        product_id: product.id,
        quantity: 1,
        size: product.selectedSize || product.size || null
      });

      // 2. Sinkronisasi data keranjang terbaru di latar belakang
      await fetchCart();
    } catch (error) {
      console.error('Gagal menambah ke keranjang:', error);
      triggerToast('Gagal menambahkan produk ke keranjang.');
    }
  }

  // Update kuantitas item di keranjang via API
  async function updateQuantity(cartId, quantity) {
    try {
      await api.put(`/cart/${cartId}`, { quantity });
      await fetchCart();
    } catch (error) {
      console.error('Gagal update kuantitas:', error);
    }
  }

  // Hapus item dari keranjang via API
  async function removeItem(cartId) {
    try {
      await api.delete(`/cart/${cartId}`);
      await fetchCart();
      triggerToast('Item dihapus dari keranjang.');
    } catch (error) {
      console.error('Gagal menghapus item:', error);
    }
  }

  function triggerToast(msg) {
    toastMessage.value = msg;
    showToast.value = true;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
      showToast.value = false;
    }, 3000);
  }

  function clearCart() {
    items.value = [];
  }

  async function processCheckout(paymentMethodId) {
    try {
      const response = await api.post('/checkout', {
        items: items.value,
        payment_method_id: paymentMethodId
      });

      clearCart();
      triggerToast('Pesanan berhasil dibuat!');
      return response.data;
    } catch (error) {
      console.error('Checkout error:', error);
      throw error;
    }
  }

  // Panggil fetchCart saat store pertama kali diinisialisasi jika user login
  if (localStorage.getItem('auth_token') || localStorage.getItem('token')) {
    fetchCart();
  }

  return { 
    items, 
    totalItems, 
    showToast, 
    toastMessage, 
    fetchCart,
    addToCart, 
    updateQuantity,
    removeItem,
    processCheckout, 
    triggerToast, 
    clearCart 
  };
});