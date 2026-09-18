<template>
  <div class="delarache-catalog">
    <!-- Hero Banner -->
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

        <!-- Toggle Suara -->
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
          @click="$router.push(`/product/${product.id}`)"
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
              
              <!-- Tombol Cepat Tambah ke Cart -->
              <button 
                @click.stop="handleQuickAddToCart(product)" 
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useCartStore } from '../stores/cart';
import api from '../services/api';

const cartStore = useCartStore();

// State dasar
const loading = ref(false);
const products = ref([]);
const currentSearchQuery = ref('');
const isMuted = ref(true);
const heroVideo = ref(null);

// Helper untuk menentukan ukuran dinamis berdasarkan kategori produk
const getSizesForProduct = (product) => {
  if (!product) return [];

  const categoryName = (product.category?.name || product.category || '').toLowerCase();

  // 1. Kategori Celana / Pants / Bottoms
  if (categoryName.includes('pant') || categoryName.includes('celana') || categoryName.includes('jeans') || categoryName.includes('short')) {
    return ['28', '30', '32', '34', '36'];
  }

  // 2. Kategori Baju / Tops / Outerwear
  if (
    categoryName.includes('shirt') || 
    categoryName.includes('baju') || 
    categoryName.includes('tee') || 
    categoryName.includes('hoodie') ||
    categoryName.includes('jacket') ||
    categoryName.includes('sweater')
  ) {
    return ['S', 'M', 'L', 'XL', 'XXL'];
  }

  // 3. Default Aksesori
  return ['ALL SIZE'];
};

const filteredProducts = computed(() => {
  if (!currentSearchQuery.value) return products.value;
  return products.value.filter(p => 
    p.name.toLowerCase().includes(currentSearchQuery.value.toLowerCase())
  );
});

const playVideo = () => {
  if (heroVideo.value) {
    heroVideo.value.play().catch(e => console.log('Autoplay blocked:', e));
  }
};

const toggleMute = () => {
  isMuted.value = !isMuted.value;
};

const getImageUrl = (imagePath) => {
  if (!imagePath) return 'https://via.placeholder.com/300';
  if (imagePath.startsWith('http')) return imagePath;
  return `http://localhost:8000/storage/${imagePath.replace(/^\//, '')}`;
};

// Quick Add dari Grid Katalog Utama (Otomatis memilih ukuran pertama yang valid)
const handleQuickAddToCart = (product) => {
  const dynamicSizes = getSizesForProduct(product);
  const itemToAdd = {
    ...product,
    selectedSize: dynamicSizes[0] || 'ALL SIZE'
  };
  cartStore.addToCart(itemToAdd);
};

onMounted(async () => {
  loading.value = true;
  try {
    const res = await api.get('/products');
    products.value = res.data?.data || res.data || [];
  } catch (e) {
    console.error('Gagal mengambil produk:', e);
  } finally {
    loading.value = false;
  }
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
</style>