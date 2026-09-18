<template>
  <div class="product-detail-page">
    <div v-if="loading" class="state-container">
      <div class="spinner"></div>
    </div>

    <div v-else-if="product" class="bape-container">
      <!-- GALERI KIRI -->
      <div class="gallery-section">
        <div class="thumb-column">
          <div 
            v-for="(img, idx) in productImages" 
            :key="idx" 
            :class="['thumb-card', { active: activeImage === img }]"
            @click="activeImage = img"
          >
            <img :src="img" alt="thumbnail" />
          </div>
        </div>

        <div 
          class="main-image-card" 
          @mousemove="handleZoom" 
          @mouseleave="resetZoom"
        >
          <button class="search-zoom-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
          </button>
          <img 
            ref="mainImgRef" 
            :src="activeImage" 
            :alt="product.name" 
            class="display-image" 
          />
        </div>
      </div>

      <!-- PANEL INFORMASI KANAN -->
      <div class="info-section">
        <h1 class="bape-title">{{ product.name }}</h1>
        <div class="bape-price">
          Rp {{ Number(product.price).toLocaleString('id-ID') }},00
        </div>

        <!-- PILIHAN SIZE -->
        <div class="size-group">
          <span class="size-heading">SIZE</span>
          <div class="size-list">
            <button 
              v-for="sz in availableSizes" 
              :key="sz"
              :class="['size-btn', { active: selectedSize === sz }]"
              @click="selectedSize = sz"
            >
              {{ sz }}
            </button>
          </div>
        </div>

        <!-- TOMBOL UTAMA -->
        <button 
          @click="addToCart" 
          class="bape-submit-btn"
          :disabled="product.stock <= 0"
        >
          {{ product.stock > 0 ? (selectedSize ? 'TAMBAHKAN KE KERANJANG' : 'SELECT SIZE') : 'OUT OF STOCK' }}
        </button>

        <!-- ACCORDION DETAILS -->
        <div class="details-accordion">
          <div class="accordion-header" @click="isDetailsOpen = !isDetailsOpen">
            <span>Details</span>
            <span class="accordion-arrow">{{ isDetailsOpen ? '▲' : '▼' }}</span>
          </div>
          <div v-if="isDetailsOpen" class="accordion-body">
            <p>{{ product.description || 'Tidak ada deskripsi.' }}</p>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="state-container">
      <p>Produk tidak ditemukan.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '../services/api';
import { useCartStore } from '../stores/cart';

const route = useRoute();
const cartStore = useCartStore();

const product = ref(null);
const loading = ref(true);
const activeImage = ref('');
const productImages = ref([]);
const availableSizes = ref([]);
const selectedSize = ref('');
const isDetailsOpen = ref(true);
const mainImgRef = ref(null);

const getImageUrl = (path) => {
  if (!path) return 'https://via.placeholder.com/400';
  if (path.startsWith('http')) return path;
  return `http://localhost:8000/storage/${path.replace(/^\//, '')}`;
};

const fetchDetail = async () => {
  try {
    loading.value = true;
    const res = await api.get(`/products/${route.params.id}`);
    const data = res.data?.data || res.data;
    product.value = data;

    const mainImg = getImageUrl(data.image);
    const gallery = [mainImg];

    const extraImages = data.images || data.product_images || [];
    if (Array.isArray(extraImages) && extraImages.length > 0) {
      extraImages.forEach(imgObj => {
        const path = typeof imgObj === 'object' ? (imgObj.image_path || imgObj.url || imgObj.image) : imgObj;
        const fullUrl = getImageUrl(path);
        
        if (path && !gallery.includes(fullUrl)) {
          gallery.push(fullUrl);
        }
      });
    }

    if (gallery.length === 1) {
      gallery.push(mainImg, mainImg);
    }

    productImages.value = gallery;
    activeImage.value = gallery[0];

    if (data.size) {
      availableSizes.value = data.size.split(',').map(s => s.trim());
    } else {
      availableSizes.value = ['S', 'M', 'L', 'XL'];
    }
  } catch (err) {
    console.error('Error loading product:', err);
  } finally {
    loading.value = false;
  }
};

// Fungsi Zoom Mengikuti Kursor
const handleZoom = (e) => {
  const card = e.currentTarget;
  const img = mainImgRef.value;
  if (!img) return;

  const rect = card.getBoundingClientRect();
  const x = ((e.clientX - rect.left) / card.clientWidth) * 100;
  const y = ((e.clientY - rect.top) / card.clientHeight) * 100;

  img.style.transformOrigin = `${x}% ${y}%`;
  img.style.transform = 'scale(2)';
};

const resetZoom = () => {
  const img = mainImgRef.value;
  if (!img) return;
  img.style.transformOrigin = 'center center';
  img.style.transform = 'scale(1)';
};

const addToCart = async () => {
  if (!selectedSize.value) {
    alert('Silakan pilih ukuran terlebih dahulu!');
    return;
  }

  try {
    await cartStore.addToCart({
      ...product.value,
      selectedSize: selectedSize.value
    });
  } catch (error) {
    console.error('Gagal memasukkan ke keranjang:', error);
    if (error.response?.status === 401) {
      alert('Silakan login terlebih dahulu untuk belanja.');
    } else {
      alert('Gagal menambahkan produk ke keranjang. Coba lagi.');
    }
  }
};

onMounted(() => {
  fetchDetail();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

.product-detail-page {
  background-color: #ffffff;
  color: #000000;
  font-family: 'Inter', sans-serif;
  min-height: 90vh;
  padding: 40px 20px;
}

.bape-container {
  max-width: 1100px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 60px;
}

/* Galeri Kiri */
.gallery-section {
  display: flex;
  gap: 20px;
  align-items: flex-start;
}

.thumb-column {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.thumb-card {
  width: 60px;
  height: 60px;
  border: 1px solid #e0e0e0;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff;
}

.thumb-card.active {
  border: 1.5px solid #000000;
}

.thumb-card img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.main-image-card {
  position: relative;
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #ffffff;
  min-height: 420px;
  overflow: hidden; /* Mencegah gambar meluber keluar kotak saat di-zoom */
  cursor: crosshair;
}

.display-image {
  max-width: 100%;
  max-height: 480px;
  object-fit: contain;
  transition: transform 0.1s ease-out; /* Pergerakan zoom halus mengikuti kursor */
}

.search-zoom-btn {
  position: absolute;
  top: 0;
  right: 0;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 5px;
  z-index: 2;
}

/* Panel Kanan */
.info-section {
  display: flex;
  flex-direction: column;
}

.bape-title {
  font-size: 28px;
  font-weight: 600;
  line-height: 1.2;
  margin: 0 0 12px 0;
  color: #000000;
  letter-spacing: -0.5px;
}

.bape-price {
  font-size: 16px;
  color: #000000;
  margin-bottom: 28px;
  font-weight: 400;
}

/* Size */
.size-group {
  margin-bottom: 20px;
}

.size-heading {
  display: block;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.5px;
  margin-bottom: 10px;
}

.size-list {
  display: flex;
  gap: 8px;
}

.size-btn {
  width: 48px;
  height: 48px;
  background: #ffffff;
  border: 1px solid #e0e0e0;
  color: #000000;
  font-size: 14px;
  font-weight: 400;
  cursor: pointer;
}

.size-btn.active, .size-btn:hover {
  border-color: #000000;
}

/* Tombol Utama */
.bape-submit-btn {
  width: 100%;
  background: #000000;
  color: #ffffff;
  border: none;
  height: 52px;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 1px;
  cursor: pointer;
  margin-bottom: 25px;
  text-transform: uppercase;
}

.bape-submit-btn:hover {
  opacity: 0.9;
}

/* Accordion Details */
.details-accordion {
  border-top: 1px solid #e5e5e5;
  padding-top: 14px;
}

.accordion-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
}

.accordion-arrow {
  font-size: 10px;
}

.accordion-body {
  margin-top: 15px;
  font-size: 13px;
  color: #333333;
  line-height: 1.6;
}

.state-container {
  text-align: center;
  padding: 100px 0;
}

.spinner {
  width: 30px;
  height: 30px;
  border: 3px solid #eee;
  border-top-color: #000;
  border-radius: 50%;
  margin: 0 auto;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  .bape-container {
    grid-template-columns: 1fr;
    gap: 30px;
  }
}
</style>