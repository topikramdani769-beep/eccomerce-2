<template>
  <div class="delarache-catalog">
    <!-- Hero Banner dengan Background Video Lokal -->
    <section class="hero-banner">
      <video 
        ref="heroVideo"
        autoplay 
        loop 
        muted 
        playsinline 
        preload="auto"
        class="hero-video"
        @loadedmetadata="playVideo"
      >
        <!-- Menggunakan File Video Lokal dari Folder public/ -->
        <source src="/hero-video.mp4" type="video/mp4" />
        Browser Anda tidak mendukung pemutaran video.
      </video>
      
      <div class="hero-overlay">
        <span class="tag-red">EDISI TERBATAS 2026</span>
        <h1 class="main-title">DE LARACHE<br>SIGNATURE</h1>
        <button class="btn-buy-now">BELI SEKARANG</button>
      </div>
    </section>

    <!-- State Loading -->
    <div v-if="loading" class="state-msg">
      <div class="spinner"></div>
      <p>LOADING CATALOG...</p>
    </div>

    <!-- Product Grid -->
    <div v-else class="catalog-section">
      <h2 class="section-title">
        {{ currentSearchQuery ? `HASIL PENCARIAN: "${currentSearchQuery.toUpperCase()}"` : 'KOLEKSI UTAMA' }}
      </h2>

      <div v-if="filteredProducts.length > 0" class="product-grid">
        <div 
          v-for="product in filteredProducts" 
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
              <span class="category-badge">{{ product.category?.name || 'PARFUM' }}</span>
            </div>
            
            <h3 class="product-title">{{ product.name }}</h3>
            
            <div class="card-footer">
              <div class="price-stock-info">
                <span class="price-value">Rp {{ Number(product.price).toLocaleString('id-ID') }}</span>
                <span class="stock-info" :class="{ 'low-stock': product.stock > 0 && product.stock <= 5 }">
                  Stok: {{ product.stock ?? 0 }}
                </span>
              </div>
              
              <button 
                @click.stop="addToCart(product.id)" 
                class="btn-cart"
                :disabled="product.stock <= 0"
              >
                {{ product.stock > 0 ? '+ CART' : 'SOLD OUT' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pesan Jika Hasil Pencarian Kosong -->
      <div v-else class="no-products-msg">
        <p>PRODUK TIDAK DITEMUKAN</p>
      </div>
    </div>

    <!-- Modal Detail Produk -->
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
                <span class="category-badge modal-badge">{{ selectedProduct.category?.name || 'PARFUM' }}</span>
                <h2 class="modal-title">{{ selectedProduct.name }}</h2>
                <div class="modal-price">Rp {{ Number(selectedProduct.price).toLocaleString('id-ID') }}</div>

                <div class="stock-status">
                  <span class="stock-dot" :class="{ 'out': selectedProduct.stock <= 0, 'low': selectedProduct.stock > 0 && selectedProduct.stock <= 5 }"></span>
                  <span v-if="selectedProduct.stock > 5">Tersedia {{ selectedProduct.stock }} unit</span>
                  <span v-else-if="selectedProduct.stock > 0" class="text-warning">Sisa {{ selectedProduct.stock }} unit!</span>
                  <span v-else class="text-danger">Stok Habis</span>
                </div>

                <div class="size-selector">
                  <label>UKURAN (ML)</label>
                  <select v-model="selectedSize">
                    <option value="30ml">30ml</option>
                    <option value="50ml">50ml</option>
                    <option value="100ml">100ml</option>
                  </select>
                </div>

                <div class="description-box">
                  <h4>DESKRIPSI</h4>
                  <p>{{ selectedProduct.description || 'Wewangian eksklusif De Larache Signature dengan perpaduan aroma bold dan tahan lama.' }}</p>
                </div>
              </div>

              <button 
                @click="addToCart(selectedProduct.id)" 
                class="btn-add-bag"
                :disabled="selectedProduct.stock <= 0"
              >
                {{ selectedProduct.stock > 0 ? 'TAMBAHKAN KE KERANJANG' : 'STOK HABIS' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';
import { useRouter, useRoute } from 'vue-router';

const products = ref([]);
const loading = ref(true);
const selectedProduct = ref(null);
const selectedSize = ref('50ml');
const heroVideo = ref(null);

const authStore = useAuthStore();
const router = useRouter();
const route = useRoute();

// Ambil kata kunci pencarian dari URL
const currentSearchQuery = computed(() => route.query.search || '');

// Filter produk berdasarkan input pencarian
const filteredProducts = computed(() => {
  if (!currentSearchQuery.value) return products.value;
  const query = currentSearchQuery.value.toLowerCase();
  return products.value.filter(product => 
    product.name.toLowerCase().includes(query) ||
    (product.description && product.description.toLowerCase().includes(query))
  );
});

// Fungsi memutar video otomatis & memastikan muted
const playVideo = () => {
  if (heroVideo.value) {
    heroVideo.value.muted = true;
    heroVideo.value.play().catch((err) => {
      console.warn('Autoplay video terhalang kebijakan browser:', err);
    });
  }
};

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
  playVideo();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800;900&display=swap');

.delarache-catalog {
  font-family: 'Montserrat', sans-serif;
  background-color: #0d0d0d;
  min-height: 100vh;
  color: #ffffff;
}

/* Hero Section */
.hero-banner {
  position: relative;
  width: 100%;
  height: 80vh;
  overflow: hidden;
  display: flex;
  align-items: flex-end;
}

.hero-video {
  position: absolute;
  top: 50%;
  left: 50%;
  min-width: 100%;
  min-height: 100%;
  width: auto;
  height: auto;
  z-index: 1;
  transform: translate(-50%, -50%);
  object-fit: cover;
}

.hero-overlay {
  position: relative;
  z-index: 2;
  padding: 60px 40px;
}

.tag-red {
  background-color: #e62129;
  color: #fff;
  font-size: 10px;
  font-weight: 800;
  padding: 5px 10px;
  letter-spacing: 1.5px;
  display: inline-block;
  margin-bottom: 12px;
}

.main-title {
  font-size: 48px;
  font-weight: 900;
  line-height: 1;
  letter-spacing: 2px;
  margin-bottom: 20px;
  color: #ffffff;
  text-shadow: 2px 2px 0px #e62129, -2px -2px 0px #00ffff;
}

.btn-buy-now {
  background-color: #ffffff;
  color: #000000;
  border: none;
  padding: 12px 28px;
  font-size: 12px;
  font-weight: 900;
  letter-spacing: 1px;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-buy-now:hover {
  background-color: #e62129;
  color: #ffffff;
}

/* Catalog Grid */
.catalog-section {
  max-width: 1200px;
  margin: 0 auto;
  padding: 50px 20px;
}

.section-title {
  font-size: 20px;
  font-weight: 900;
  letter-spacing: 2px;
  margin-bottom: 30px;
  border-left: 4px solid #e62129;
  padding-left: 12px;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 24px;
}

.product-card {
  background: #141414;
  border: 1px solid #222;
  border-radius: 4px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  cursor: pointer;
  transition: transform 0.3s;
}

.product-card:hover {
  transform: translateY(-5px);
  border-color: #444;
}

.image-wrapper {
  position: relative;
  width: 100%;
  height: 250px;
  background: #1a1a1a;
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
  background: #e62129;
  color: #fff;
  font-size: 9px;
  font-weight: 800;
  padding: 4px 8px;
}

.card-body {
  padding-top: 12px;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  justify-content: space-between;
}

.category-badge {
  color: #e62129;
  font-size: 9px;
  font-weight: 800;
  letter-spacing: 1px;
}

.product-title {
  font-size: 14px;
  font-weight: 800;
  margin: 8px 0;
  color: #fff;
  letter-spacing: 0.5px;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-top: 12px;
}

.price-stock-info {
  display: flex;
  flex-direction: column;
}

.price-value {
  font-size: 14px;
  font-weight: 800;
  color: #fff;
}

.stock-info {
  font-size: 10px;
  color: #888;
  margin-top: 2px;
}

.stock-info.low-stock {
  color: #f59e0b;
}

.btn-cart {
  background: #ffffff;
  color: #000000;
  border: none;
  padding: 8px 14px;
  font-size: 10px;
  font-weight: 900;
  cursor: pointer;
}

.btn-cart:hover:not(:disabled) {
  background: #e62129;
  color: #fff;
}

.btn-cart:disabled {
  background: #444;
  color: #888;
  cursor: not-allowed;
}

.no-products-msg {
  text-align: center;
  padding: 40px 0;
  font-weight: 800;
  letter-spacing: 2px;
  color: #888888;
}

/* Modal Styling */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.85);
  backdrop-filter: blur(5px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999;
}

.modal-content {
  background: #141414;
  border: 1px solid #333;
  width: 90%;
  max-width: 650px;
  padding: 24px;
  position: relative;
  color: #fff;
}

.close-btn {
  position: absolute;
  top: 12px;
  right: 16px;
  border: none;
  background: transparent;
  font-size: 24px;
  cursor: pointer;
  color: #fff;
}

.modal-body {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

@media (max-width: 640px) {
  .modal-body {
    grid-template-columns: 1fr;
  }
}

.modal-image-wrapper {
  background: #1a1a1a;
  height: 280px;
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
  margin: 6px 0;
}

.modal-price {
  font-size: 16px;
  font-weight: 800;
  margin-bottom: 10px;
  color: #e62129;
}

.stock-status {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  margin-bottom: 15px;
}

.stock-dot {
  width: 8px;
  height: 8px;
  background-color: #10b981;
  border-radius: 50%;
}

.stock-dot.low { background-color: #f59e0b; }
.stock-dot.out { background-color: #ef4444; }

.text-warning { color: #f59e0b; }
.text-danger { color: #ef4444; }

.size-selector {
  margin-bottom: 15px;
}

.size-selector label {
  display: block;
  font-size: 10px;
  font-weight: 800;
  margin-bottom: 4px;
  color: #aaa;
}

.size-selector select {
  width: 100%;
  padding: 8px;
  background: #222;
  border: 1px solid #444;
  color: #fff;
  font-weight: 600;
}

.description-box h4 {
  font-size: 10px;
  font-weight: 800;
  margin-bottom: 4px;
  color: #aaa;
}

.description-box p {
  font-size: 11px;
  color: #ccc;
  line-height: 1.4;
}

.btn-add-bag {
  background: #ffffff;
  color: #000000;
  border: none;
  width: 100%;
  padding: 12px;
  font-weight: 900;
  font-size: 11px;
  letter-spacing: 1px;
  cursor: pointer;
  margin-top: 15px;
}

.btn-add-bag:hover:not(:disabled) {
  background: #e62129;
  color: #ffffff;
}

.btn-add-bag:disabled {
  background: #333;
  color: #666;
  cursor: not-allowed;
}

/* Loading Spinner */
.state-msg {
  text-align: center;
  padding: 60px 0;
  color: #fff;
}

.spinner {
  width: 28px;
  height: 28px;
  border: 3px solid #333;
  border-top-color: #e62129;
  border-radius: 50%;
  margin: 0 auto 12px auto;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>