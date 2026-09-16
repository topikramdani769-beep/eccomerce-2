<template>
  <div class="delarache-catalog">
    <!-- Hero Banner dengan Background Video Lokal -->
    <section class="hero-banner">
      <video 
        ref="heroVideo"
        autoplay 
        loop 
        :muted="isMuted" 
        playsinline 
        preload="auto"
        class="hero-video"
        @loadedmetadata="playVideo"
      >
        <source src="/hero-video.mp4" type="video/mp4" />
        Browser Anda tidak mendukung pemutaran video.
      </video>
      
      <div class="hero-overlay">
        <span class="tag-red">EDISI TERBATAS 2026</span>
        <h1 class="main-title">DE LARACHE<br>SIGNATURE</h1>
        <button class="btn-buy-now">BELI SEKARANG</button>

        <!-- Tombol Toggle Suara Video -->
        <button 
          @click="toggleMute" 
          class="btn-sound-toggle" 
          :aria-label="isMuted ? 'Unmute Video' : 'Mute Video'"
        >
          <svg v-if="isMuted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="sound-icon">
            <path d="M11 5L6 9H2v6h4l5 4V5z"></path>
            <line x1="23" y1="9" x2="17" y2="15"></line>
            <line x1="17" y1="9" x2="23" y2="15"></line>
          </svg>
          <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="sound-icon">
            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
            <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
          </svg>
          <span>{{ isMuted ? 'SOUND OFF' : 'SOUND ON' }}</span>
        </button>
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
            <img :src="getImageUrl(product.image)" :alt="product.name" />
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

      <div v-else class="no-products-msg">
        <p>PRODUK TIDAK DITEMUKAN</p>
      </div>
    </div>

    <!-- Modal Detail Produk -->
    <Transition name="fade">
      <div v-if="selectedProduct" class="modal-overlay" @click.self="closeModal">
        <div class="modal-content-bape">
          <button class="close-btn" @click="closeModal">&times;</button>
          
          <div class="modal-body-bape">
            <!-- Sisi Kiri: Gambar Utama -->
            <div class="main-image-container">
              <img :src="activeImage || getImageUrl(selectedProduct.image)" :alt="selectedProduct.name" />
            </div>
            
            <!-- Sisi Kanan: Informasi Produk -->
            <div class="product-info-container">
              <h1 class="bape-title">{{ selectedProduct.name }}</h1>
              <div class="bape-price">
                Rp {{ Number(selectedProduct.price).toLocaleString('id-ID') }},00
              </div>

              <!-- Color / Variant Thumbnails Galeri -->
              <div v-if="productImages.length > 0" class="color-thumbnails">
                <div 
                  v-for="(img, idx) in productImages" 
                  :key="idx"
                  class="thumb-box"
                  :class="{ 'active': activeImage === img }"
                  @click="activeImage = img"
                >
                  <img :src="img" alt="Variant thumbnail" />
                </div>
              </div>

              <!-- Size Selector Box Grid -->
              <div class="size-section">
                <label class="size-label">SIZE</label>
                <div class="size-grid">
                  <button 
                    v-for="sizeOption in availableSizes" 
                    :key="sizeOption"
                    class="size-box"
                    :class="{ 'active': selectedSize === sizeOption }"
                    @click="selectedSize = sizeOption"
                  >
                    {{ sizeOption }}
                  </button>
                </div>
              </div>

              <!-- Main Action Button -->
              <button 
                @click="addToCart(selectedProduct.id)" 
                class="btn-select-size"
                :disabled="selectedProduct.stock <= 0"
              >
                {{ selectedProduct.stock > 0 ? (selectedSize ? `TAMBAH KE KERANJANG (${selectedSize})` : 'PILIH UKURAN') : 'STOK HABIS' }}
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
const activeImage = ref('');
const selectedSize = ref('M');
const availableSizes = ref(['S', 'M', 'L', 'XL', 'XXL']);

const heroVideo = ref(null);
const isMuted = ref(true); // Default muted agar diizinkan autoplay oleh browser

const authStore = useAuthStore();
const router = useRouter();
const route = useRoute();

// Toggle Suara Video
const toggleMute = () => {
  isMuted.value = !isMuted.value;
  if (heroVideo.value) {
    heroVideo.value.muted = isMuted.value;
  }
};

const playVideo = () => {
  if (heroVideo.value) {
    heroVideo.value.muted = isMuted.value;
    heroVideo.value.play().catch((err) => {
      console.warn('Autoplay video terhalang kebijakan browser:', err);
    });
  }
};

const currentSearchQuery = computed(() => route.query.search || '');

const filteredProducts = computed(() => {
  if (!currentSearchQuery.value) return products.value;
  const query = currentSearchQuery.value.toLowerCase();
  return products.value.filter(product => 
    product.name.toLowerCase().includes(query) ||
    (product.description && product.description.toLowerCase().includes(query))
  );
});

const productImages = computed(() => {
  if (!selectedProduct.value) return [];
  
  if (Array.isArray(selectedProduct.value.images) && selectedProduct.value.images.length > 0) {
    return selectedProduct.value.images.map(img => typeof img === 'object' ? getImageUrl(img.image_path) : getImageUrl(img));
  }
  
  return [getImageUrl(selectedProduct.value.image)];
});

const getImageUrl = (imagePath) => {
  if (!imagePath) return 'https://via.placeholder.com/300x300?text=No+Image';

  if (typeof imagePath === 'string' && imagePath.startsWith('[')) {
    try {
      const parsed = JSON.parse(imagePath);
      imagePath = parsed[0] || '';
    } catch (e) {
      console.error('Gagal parse path gambar:', e);
    }
  }

  if (typeof imagePath === 'string' && (imagePath.startsWith('http://') || imagePath.startsWith('https://'))) {
    return imagePath;
  }

  const baseUrl = 'http://localhost:8000';
  let cleanPath = imagePath.startsWith('/') ? imagePath : `/${imagePath}`;
  if (cleanPath.startsWith('/storage/')) {
    cleanPath = cleanPath.replace('/storage/', '/');
  }

  return `${baseUrl}/storage${cleanPath}`;
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
  selectedSize.value = product.size || 'M';
  activeImage.value = getImageUrl(product.image);
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
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&display=swap');

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
  width: 100%;
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

/* Tombol Toggle Sound */
.btn-sound-toggle {
  position: absolute;
  bottom: 40px;
  right: 40px;
  background: rgba(20, 20, 20, 0.85);
  color: #ffffff;
  border: 1px solid #333333;
  padding: 10px 16px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 1px;
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.25s ease;
  backdrop-filter: blur(4px);
}

.btn-sound-toggle:hover {
  background: #ffffff;
  color: #000000;
  border-color: #ffffff;
}

.sound-icon {
  width: 14px;
  height: 14px;
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

/* Modal Styling Minimalis Clean White */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999;
}

.modal-content-bape {
  background: #ffffff;
  color: #000000;
  width: 90%;
  max-width: 900px;
  padding: 40px;
  position: relative;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
}

.close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  border: none;
  background: transparent;
  font-size: 28px;
  cursor: pointer;
  color: #000000;
}

.modal-body-bape {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 40px;
  align-items: center;
}

@media (max-width: 768px) {
  .modal-body-bape {
    grid-template-columns: 1fr;
    gap: 20px;
  }
}

.main-image-container {
  width: 100%;
  height: 380px;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #fafafa;
}

.main-image-container img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.product-info-container {
  display: flex;
  flex-direction: column;
}

.bape-title {
  font-size: 22px;
  font-weight: 700;
  color: #000000;
  margin-bottom: 8px;
  line-height: 1.3;
}

.bape-price {
  font-size: 16px;
  font-weight: 600;
  color: #111111;
  margin-bottom: 24px;
}

.color-thumbnails {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
}

.thumb-box {
  width: 60px;
  height: 60px;
  border: 1px solid #e5e5e5;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.thumb-box.active, .thumb-box:hover {
  border: 2px solid #000000;
}

.thumb-box img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.size-section {
  margin-bottom: 30px;
}

.size-label {
  display: block;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.5px;
  margin-bottom: 10px;
  color: #000000;
}

.size-grid {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.size-box {
  width: 48px;
  height: 48px;
  background: #ffffff;
  border: 1px solid #e5e5e5;
  color: #000000;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.size-box:hover {
  border-color: #a3a3a3;
}

.size-box.active {
  border: 2px solid #000000;
  font-weight: 800;
}

.btn-select-size {
  width: 100%;
  background: #000000;
  color: #ffffff;
  border: none;
  padding: 16px;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.5px;
  cursor: pointer;
  transition: background 0.3s ease;
}

.btn-select-size:hover:not(:disabled) {
  background: #222222;
}

.btn-select-size:disabled {
  background: #cccccc;
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