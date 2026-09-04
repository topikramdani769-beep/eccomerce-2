<template>
  <div class="bape-catalog">
    <!-- Hero Banner Minimalis -->
    <header class="hero-banner">
      <span class="sub-heading">HERO BANNER</span>
      <h1 class="main-title">NEW ARRIVALS</h1>
      <p class="tagline">OFFICIAL BAPE STREETWEAR COLLECTION</p>
      <div class="brand-badge">
        <span>🦍</span>
      </div>
    </header>

    <!-- State Loading -->
    <div v-if="loading" class="state-msg">
      <div class="spinner"></div>
      <p>Loading Catalog...</p>
    </div>

    <!-- Product Grid -->
    <div v-else class="product-grid">
      <div 
        v-for="product in products" 
        :key="product.id" 
        class="product-card"
        @click="openModal(product)"
      >
        <div class="image-wrapper">
          <img :src="product.image" :alt="product.name" />
          <span v-if="product.stock <= 0" class="out-stock-badge">OUT OF STOCK</span>
        </div>
        
        <div class="card-body">
          <div class="badge-wrapper">
            <span class="category-badge">{{ product.category?.name || 'Streetwear' }}</span>
          </div>
          
          <h3 class="product-title">{{ product.name }}</h3>
          
          <div class="card-footer">
            <div class="price-stock-info">
              <span class="price-label">Price</span>
              <span class="price-value">Rp {{ Number(product.price).toLocaleString('id-ID') }}</span>
              <span class="stock-info" :class="{ 'low-stock': product.stock > 0 && product.stock <= 5 }">
                Stok: {{ product.stock ?? 0 }} pcs
              </span>
            </div>
            
            <button 
              @click.stop="addToCart(product.id)" 
              class="btn-cart"
              :disabled="product.stock <= 0"
            >
              {{ product.stock > 0 ? '+ Cart' : 'Sold Out' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Detail Produk Style Modern Luxury -->
    <Transition name="fade">
      <div v-if="selectedProduct" class="modal-overlay" @click.self="closeModal">
        <div class="modal-content">
          <button class="close-btn" @click="closeModal">&times;</button>
          
          <div class="modal-body">
            <div class="modal-image-wrapper">
              <img :src="selectedProduct.image" :alt="selectedProduct.name" />
            </div>
            
            <div class="modal-info">
              <div>
                <span class="category-badge modal-badge">{{ selectedProduct.category?.name || 'Streetwear' }}</span>
                <h2 class="modal-title">{{ selectedProduct.name }}</h2>
                <div class="modal-price">Rp {{ Number(selectedProduct.price).toLocaleString('id-ID') }}</div>

                <!-- Informasi Stok di Modal -->
                <div class="stock-status">
                  <span class="stock-dot" :class="{ 'out': selectedProduct.stock <= 0, 'low': selectedProduct.stock > 0 && selectedProduct.stock <= 5 }"></span>
                  <span v-if="selectedProduct.stock > 5">Tersedia {{ selectedProduct.stock }} unit</span>
                  <span v-else-if="selectedProduct.stock > 0" class="text-warning">Sisa {{ selectedProduct.stock }} unit lagi!</span>
                  <span v-else class="text-danger">Stok Habis</span>
                </div>

                <!-- Size Selector Option -->
                <div class="size-selector">
                  <label>Size</label>
                  <select v-model="selectedSize">
                    <option value="S">S</option>
                    <option value="M">M</option>
                    <option value="L">L</option>
                    <option value="XL">XL</option>
                  </select>
                </div>

                <div class="description-box">
                  <h4>Description</h4>
                  <p>{{ selectedProduct.description || 'A Bathing Ape streetwear collection with premium materials and signature design aesthetic.' }}</p>
                </div>
              </div>

              <button 
                @click="addToCart(selectedProduct.id)" 
                class="btn-add-bag"
                :disabled="selectedProduct.stock <= 0"
              >
                {{ selectedProduct.stock > 0 ? 'ADD TO BAG' : 'OUT OF STOCK' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const products = ref([]);
const loading = ref(true);
const selectedProduct = ref(null);
const selectedSize = ref('M');
const authStore = useAuthStore();
const router = useRouter();

const fetchProducts = async () => {
  try {
    const response = await api.get('/products');
    products.value = response.data;
  } catch (error) {
    console.error('Error loading products:', error);
  } finally {
    loading.value = false;
  }
};

const openModal = (product) => {
  selectedProduct.value = product;
};

const closeModal = () => {
  selectedProduct.value = null;
};

const addToCart = async (productId) => {
  if (!authStore.isAuthenticated) {
    alert('Silakan login terlebih dahulu!');
    return router.push('/login');
  }

  try {
    await api.post('/cart', { 
      product_id: productId, 
      quantity: 1,
      size: selectedSize.value 
    });
    alert('Produk berhasil ditambahkan ke keranjang!');
  } catch (error) {
    alert(error.response?.data?.message || 'Gagal menambahkan ke keranjang');
  }
};

onMounted(() => {
  fetchProducts();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.bape-catalog {
  font-family: 'Plus Jakarta Sans', sans-serif;
  background-color: #e2e1dc;
  min-height: 100vh;
  padding: 40px 20px;
  color: #111111;
}

/* Hero Banner */
.hero-banner {
  text-align: center;
  margin-bottom: 50px;
}

.sub-heading {
  font-size: 11px;
  letter-spacing: 3px;
  font-weight: 700;
  color: #666;
  text-transform: uppercase;
}

.main-title {
  font-size: 42px;
  font-weight: 900;
  letter-spacing: 2px;
  margin: 8px 0;
}

.tagline {
  font-size: 12px;
  letter-spacing: 3px;
  color: #555;
  font-weight: 600;
}

.brand-badge {
  margin-top: 15px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  background: #2b2a28;
  border-radius: 50%;
  font-size: 18px;
}

/* Grid Layout */
.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 24px;
  max-width: 1200px;
  margin: 0 auto;
}

/* Card Styling */
.product-card {
  background: #f4f3ef;
  border-radius: 12px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  box-shadow: 0 4px 12px rgba(0,0,0,0.03);
}

.product-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 24px rgba(0,0,0,0.08);
}

.image-wrapper {
  position: relative;
  width: 100%;
  height: 240px;
  background: #e7e6e0;
  border-radius: 8px;
  overflow: hidden;
}

.image-wrapper img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.out-stock-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  background: rgba(0,0,0,0.7);
  color: #fff;
  font-size: 9px;
  font-weight: 700;
  padding: 4px 8px;
  border-radius: 4px;
}

.card-body {
  padding-top: 12px;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  justify-content: space-between;
}

.category-badge {
  background: #c8b282;
  color: #ffffff;
  font-size: 9px;
  font-weight: 800;
  padding: 4px 10px;
  border-radius: 12px;
  letter-spacing: 1px;
  display: inline-block;
}

.product-title {
  font-size: 15px;
  font-weight: 800;
  margin: 10px 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #111;
  line-height: 1.3;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-top: 10px;
}

.price-stock-info {
  display: flex;
  flex-direction: column;
}

.price-label {
  font-size: 10px;
  color: #888;
}

.price-value {
  font-size: 14px;
  font-weight: 800;
  color: #111;
}

.stock-info {
  font-size: 10px;
  color: #666;
  margin-top: 2px;
  font-weight: 600;
}

.stock-info.low-stock {
  color: #d97706;
}

.btn-cart {
  background: #111111;
  color: #ffffff;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-cart:hover:not(:disabled) {
  background: #333333;
}

.btn-cart:disabled {
  background: #aaa;
  cursor: not-allowed;
}

/* Modal Overlay & Card */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(8px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999;
}

.modal-content {
  background: #ffffff;
  border-radius: 16px;
  width: 90%;
  max-width: 680px;
  padding: 24px;
  position: relative;
  box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.close-btn {
  position: absolute;
  top: 16px;
  right: 20px;
  border: none;
  background: transparent;
  font-size: 24px;
  cursor: pointer;
  color: #666;
}

.modal-body {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

@media (max-width: 640px) {
  .modal-body {
    grid-template-columns: 1fr;
  }
}

.modal-image-wrapper {
  background: #f4f3ef;
  border-radius: 12px;
  overflow: hidden;
  height: 300px;
}

.modal-image-wrapper img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.modal-info {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.modal-title {
  font-size: 18px;
  font-weight: 900;
  margin: 8px 0;
  text-transform: uppercase;
}

.modal-price {
  font-size: 16px;
  font-weight: 800;
  margin-bottom: 8px;
}

.stock-status {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 600;
  margin-bottom: 15px;
}

.stock-dot {
  width: 8px;
  height: 8px;
  background-color: #10b981;
  border-radius: 50%;
}

.stock-dot.low {
  background-color: #f59e0b;
}

.stock-dot.out {
  background-color: #ef4444;
}

.text-warning { color: #d97706; }
.text-danger { color: #ef4444; }

.size-selector {
  margin-bottom: 15px;
}

.size-selector label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  margin-bottom: 4px;
  color: #444;
}

.size-selector select {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  background: #f8fafc;
  font-weight: 600;
  outline: none;
}

.description-box h4 {
  font-size: 11px;
  font-weight: 700;
  margin-bottom: 4px;
  color: #444;
}

.description-box p {
  font-size: 11px;
  color: #666;
  line-height: 1.5;
}

.btn-add-bag {
  background: #c8b282;
  color: #ffffff;
  border: none;
  width: 100%;
  padding: 12px;
  border-radius: 8px;
  font-weight: 800;
  font-size: 12px;
  letter-spacing: 1px;
  cursor: pointer;
  margin-top: 15px;
  transition: background 0.2s;
}

.btn-add-bag:hover:not(:disabled) {
  background: #b59f6f;
}

.btn-add-bag:disabled {
  background: #ccc;
  cursor: not-allowed;
}

/* Animations */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Spinner */
.state-msg {
  text-align: center;
  padding: 60px 0;
  color: #666;
}
.spinner {
  width: 28px;
  height: 28px;
  border: 3px solid #ccc;
  border-top-color: #111;
  border-radius: 50%;
  margin: 0 auto 12px auto;
  animation: spin 0.8s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>