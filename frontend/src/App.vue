<template>
  <div class="delarache-app-wrapper">
    <!-- Navbar -->
    <header v-if="showNavbar" class="navbar">
      <div class="nav-container">
        <router-link to="/" class="brand">
          <svg class="gorilla-icon" viewBox="0 0 512 512" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path d="M256 32C150 32 80 100 80 208c0 62 26 116 68 152 4 4 8 12 8 18v34c0 18 14 32 32 32h136c18 0 32-14 32-32v-34c0-6 4-14 8-18 42-36 68-90 68-152C432 100 362 32 256 32zm-64 128c18 0 32 14 32 32s-14 32-32 32-32-14-32-32 14-32 32-32zm128 0c18 0 32 14 32 32s-14 32-32 32-32-14-32-32 14-32 32-32zm-64 160c-28 0-52-16-60-40h120c-8 24-32 40-60 40z"/>
          </svg>
          <span class="brand-text glitch" data-text="DE LARACHE">DE LARACHE</span>
          <span class="brand-sub">®</span>
        </router-link>

        <nav class="nav-links">
          <router-link to="/" class="nav-item">
            <span class="glitch" data-text="SHOP">SHOP</span>
          </router-link>

          <button @click="openSearch" class="btn-search-trigger nav-item">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <span class="glitch" data-text="SEARCH">SEARCH</span>
          </button>
          
          <router-link to="/cart" class="nav-item nav-icon-item">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
              <line x1="3" y1="6" x2="21" y2="6"></line>
              <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
            <span class="glitch" data-text="CART">CART</span>
          </router-link>

          <template v-if="isAuthenticated">
            <router-link to="/orders" class="nav-item nav-icon-item">
              <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                <line x1="12" y1="22.08" x2="12" y2="12"></line>
              </svg>
              <span class="glitch" data-text="MY ORDERS">MY ORDERS</span>
            </router-link>

            <router-link v-if="Boolean(user?.is_admin)" to="/admin" class="nav-item nav-icon-item">
              <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
              <span class="glitch" data-text="ADMIN">ADMIN</span>
            </router-link>

            <span class="nav-item nav-icon-item greeting-item">
              <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              <span class="glitch" :data-text="'HI, ' + (user?.name?.toUpperCase() || '')">
                HI, {{ user?.name?.toUpperCase() }}
              </span>
            </span>

            <button @click="handleLogout" class="btn-logout nav-icon-item">
              <svg class="nav-icon logout-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
              </svg>
              <span class="glitch-blue" data-text="LOGOUT">LOGOUT</span>
            </button>
          </template>

          <template v-else>
            <router-link to="/login" class="nav-item link-auth">
              <span class="glitch" data-text="LOGIN">LOGIN</span>
            </router-link>
            <router-link to="/register" class="btn-register">
              <span class="glitch" data-text="REGISTER">REGISTER</span>
            </router-link>
          </template>
        </nav>
      </div>
    </header>

    <main class="main-content">
      <router-view />
    </main>

    <!-- Side Search Drawer -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="isSearchOpen" class="search-overlay" @click="closeSearch"></div>
      </Transition>

      <Transition name="slide">
        <aside v-if="isSearchOpen" class="search-drawer">
          <div class="drawer-header">
            <button @click="closeSearch" class="btn-close-drawer" aria-label="Close search">
              &#10005;
            </button>
          </div>

          <div class="drawer-body">
            <div class="search-input-wrapper">
              <input 
                ref="searchInputRef"
                type="text" 
                v-model="searchQuery" 
                @input="handleLiveSearch"
                placeholder="Search" 
                class="drawer-search-input"
              />
            </div>

            <!-- Area Hasil Pencarian -->
            <div class="search-results-container">
              <div v-if="isSearching" class="search-loading">
                <span class="spinner-sm"></span> Mencari produk...
              </div>

              <div v-else-if="searchResults.length > 0" class="results-list">
                <div 
                  v-for="product in searchResults" 
                  :key="product.id" 
                  class="result-item"
                  @click="selectProduct(product)"
                >
                  <img :src="product.image_url || '/placeholder.jpg'" :alt="product.name" class="result-img" />
                  <div class="result-info">
                    <span class="result-name">{{ product.name }}</span>
                    <span class="result-price">Rp {{ Number(product.price).toLocaleString('id-ID') }}</span>
                  </div>
                </div>
              </div>

              <div v-else-if="searchQuery.trim().length > 1 && !isSearching" class="no-results">
                Produk tidak ditemukan untuk "{{ searchQuery }}"
              </div>
            </div>
          </div>
        </aside>
      </Transition>
    </Teleport>

    <!-- MODAL DETAIL PRODUK -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="selectedProduct" class="modal-overlay" @click="closeDetailModal">
          <div class="product-modal-card" @click.stop>
            <button class="btn-close-modal" @click="closeDetailModal">&#10005;</button>
            
            <div class="modal-grid">
              <div class="modal-image-col">
                <img :src="selectedProduct.image_url || '/placeholder.jpg'" :alt="selectedProduct.name" class="modal-img" />
              </div>

              <div class="modal-info-col">
                <h2 class="product-title">{{ selectedProduct.name }}</h2>
                <div class="product-price">Rp {{ Number(selectedProduct.price).toLocaleString('id-ID') }}</div>
                
                <p class="product-description">
                  {{ selectedProduct.description || 'Tidak ada deskripsi produk.' }}
                </p>

                <div class="modal-actions">
                  <button @click="addToCart(selectedProduct)" class="btn-add-cart">
                    ADD TO CART
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import { useAuthStore } from './stores/auth';
import { useRouter, useRoute } from 'vue-router';
import api from './services/api';

const searchQuery = ref('');
const isSearchOpen = ref(false);
const isSearching = ref(false);
const searchResults = ref([]);
const searchInputRef = ref(null);
const selectedProduct = ref(null); // State simpan data produk terpilih untuk modal
let searchDebounce = null;

const authStore = useAuthStore();
const router = useRouter();
const route = useRoute();

const isAuthenticated = computed(() => authStore.isAuthenticated);
const user = computed(() => authStore.user);

const showNavbar = computed(() => {
  const hiddenRoutes = ['/login', '/register'];
  return !hiddenRoutes.includes(route.path);
});

const openSearch = async () => {
  isSearchOpen.value = true;
  document.body.style.overflow = 'hidden';
  await nextTick();
  if (searchInputRef.value) {
    searchInputRef.value.focus();
  }
};

const closeSearch = () => {
  isSearchOpen.value = false;
  searchQuery.value = '';
  searchResults.value = [];
  if (!selectedProduct.value) {
    document.body.style.overflow = '';
  }
};

const handleLiveSearch = () => {
  clearTimeout(searchDebounce);
  
  if (!searchQuery.value.trim()) {
    searchResults.value = [];
    isSearching.value = false;
    return;
  }

  isSearching.value = true;
  searchDebounce = setTimeout(async () => {
    try {
      const res = await api.get(`/products?search=${encodeURIComponent(searchQuery.value.trim())}`);
      searchResults.value = res.data?.data || res.data || [];
    } catch (e) {
      console.error('Error fetching live search:', e);
      searchResults.value = [];
    } finally {
      isSearching.value = false;
    }
  }, 300);
};

// Fungsi saat hasil pencarian di-klik
const selectProduct = (product) => {
  selectedProduct.value = product; // Set data produk
  closeSearch(); // Tutup search drawer
  document.body.style.overflow = 'hidden'; // Tetap kunci scroll belakang
};

// Tutup Modal Detail Produk
const closeDetailModal = () => {
  selectedProduct.value = null;
  document.body.style.overflow = '';
};

// Tambah ke Keranjang
const addToCart = (product) => {
  alert(`Produk ${product.name} berhasil ditambahkan ke keranjang!`);
  closeDetailModal();
};

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@800;900&family=Oswald:wght@700&display=swap');

.delarache-app-wrapper {
  font-family: 'Montserrat', sans-serif;
  background-color: #080808;
  min-height: 100vh;
  color: #ffffff;
}

.navbar {
  background: #ffffff;
  border-bottom: 2px solid #111111;
  padding: 12px 32px;
  position: sticky;
  top: 0;
  z-index: 1000;
}

.nav-container {
  max-width: 1350px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.brand {
  display: flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  color: #111111;
  flex-shrink: 0;
}

.gorilla-icon {
  width: 28px;
  height: 28px;
  fill: #111111;
  transition: transform 0.2s ease, fill 0.2s ease;
}

.brand:hover .gorilla-icon {
  transform: scale(1.15) rotate(-5deg);
  fill: #d61c24;
}

.brand-text {
  font-family: 'Oswald', sans-serif;
  font-size: 24px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #111111;
}

.brand-sub {
  font-size: 10px;
  font-weight: 900;
  color: #111111;
  align-self: flex-start;
  margin-top: 2px;
}

.btn-search-trigger {
  background: transparent;
  border: none;
  display: flex;
  align-items: center;
  gap: 6px;
}

.search-icon {
  width: 16px;
  height: 16px;
  color: #111111;
  transition: stroke 0.2s ease;
}

.nav-item:hover .search-icon {
  stroke: #d61c24;
}

/* SIDE SEARCH DRAWER */
.search-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(2px);
  z-index: 9998;
}

.search-drawer {
  position: fixed;
  top: 0;
  right: 0;
  width: 100%;
  max-width: 500px;
  height: 100vh;
  background: #ffffff;
  color: #111111;
  z-index: 9999;
  box-shadow: -10px 0 30px rgba(0, 0, 0, 0.2);
  display: flex;
  flex-direction: column;
}

.drawer-header {
  padding: 24px 32px 12px 32px;
  display: flex;
  justify-content: flex-start;
}

.btn-close-drawer {
  background: transparent;
  border: none;
  font-size: 24px;
  font-weight: 300;
  color: #111111;
  cursor: pointer;
  line-height: 1;
  transition: transform 0.2s ease;
}

.btn-close-drawer:hover {
  transform: scale(1.2);
}

.drawer-body {
  padding: 10px 32px 32px 32px;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.search-input-wrapper {
  margin-top: 10px;
  border-bottom: 1.5px solid #111111;
}

.drawer-search-input {
  width: 100%;
  border: none;
  outline: none;
  font-family: inherit;
  font-size: 18px;
  padding: 8px 0;
  color: #111111;
  background: transparent;
}

.drawer-search-input::placeholder {
  color: #888888;
}

.search-results-container {
  margin-top: 24px;
  flex: 1;
  overflow-y: auto;
}

.search-loading {
  font-size: 13px;
  font-weight: 600;
  color: #666666;
  display: flex;
  align-items: center;
  gap: 8px;
}

.results-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.result-item {
  display: flex;
  align-items: center;
  gap: 16px;
  cursor: pointer;
  padding: 8px;
  border-radius: 4px;
  transition: background 0.2s ease;
}

.result-item:hover {
  background: #f4f4f4;
}

.result-img {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 2px;
  border: 1px solid #eeeeee;
}

.result-info {
  display: flex;
  flex-direction: column;
}

.result-name {
  font-size: 13px;
  font-weight: 700;
  color: #111111;
  text-transform: uppercase;
}

.result-price {
  font-size: 12px;
  font-weight: 600;
  color: #666666;
  margin-top: 2px;
}

.no-results {
  font-size: 13px;
  font-weight: 600;
  color: #888888;
}

/* STYLING MODAL DETAIL PRODUK */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(4px);
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.product-modal-card {
  background: #ffffff;
  color: #111111;
  width: 100%;
  max-width: 800px;
  border-radius: 8px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(0,0,0,0.4);
  padding: 32px;
}

.btn-close-modal {
  position: absolute;
  top: 16px;
  right: 20px;
  background: transparent;
  border: none;
  font-size: 22px;
  cursor: pointer;
  color: #111111;
  z-index: 2;
}

.modal-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 32px;
  align-items: center;
}

.modal-img {
  width: 100%;
  max-height: 350px;
  object-fit: cover;
  border-radius: 4px;
}

.modal-info-col {
  display: flex;
  flex-direction: column;
}

.product-title {
  font-family: 'Oswald', sans-serif;
  font-size: 24px;
  font-weight: 700;
  text-transform: uppercase;
  margin: 0 0 8px 0;
}

.product-price {
  font-size: 18px;
  font-weight: 800;
  color: #d61c24;
  margin-bottom: 16px;
}

.product-description {
  font-size: 14px;
  color: #555555;
  line-height: 1.5;
  margin-bottom: 24px;
}

.btn-add-cart {
  font-family: 'Oswald', sans-serif;
  background: #111111;
  color: #ffffff;
  border: none;
  padding: 12px 24px;
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 1px;
  cursor: pointer;
  transition: background 0.2s ease;
}

.btn-add-cart:hover {
  background: #d61c24;
}

/* TRANSITIONS */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

.slide-enter-active, .slide-leave-active {
  transition: transform 0.3s cubic-bezier(0.25, 1, 0.5, 1);
}
.slide-enter-from, .slide-leave-to {
  transform: translateX(100%);
}

.glitch {
  position: relative;
  display: inline-block;
}

.glitch::before,
.glitch::after {
  content: attr(data-text);
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  clip: rect(0, 0, 0, 0);
  background: transparent;
}

*:hover > .glitch::before,
.glitch:hover::before {
  left: -2px;
  text-shadow: 2px 0 #d61c24;
  clip: rect(5px, 9999px, 20px, 0);
  animation: glitch-anim-1 0.25s infinite linear alternate-reverse;
}

*:hover > .glitch::after,
.glitch:hover::after {
  left: 2px;
  text-shadow: -2px 0 #00ffff;
  clip: rect(18px, 9999px, 35px, 0);
  animation: glitch-anim-2 0.2s infinite linear alternate-reverse;
}

.glitch-blue {
  position: relative;
  display: inline-block;
}

.glitch-blue::before,
.glitch-blue::after {
  content: attr(data-text);
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  clip: rect(0, 0, 0, 0);
  background: transparent;
}

.btn-logout:hover .glitch-blue::before {
  left: -2px;
  text-shadow: 2px 0 #0066ff;
  clip: rect(5px, 9999px, 20px, 0);
  animation: glitch-anim-1 0.25s infinite linear alternate-reverse;
}

.btn-logout:hover .glitch-blue::after {
  left: 2px;
  text-shadow: -2px 0 #00e5ff;
  clip: rect(18px, 9999px, 35px, 0);
  animation: glitch-anim-2 0.2s infinite linear alternate-reverse;
}

@keyframes glitch-anim-1 {
  0% { clip: rect(2px, 9999px, 15px, 0); }
  20% { clip: rect(18px, 9999px, 6px, 0); }
  40% { clip: rect(10px, 9999px, 25px, 0); }
  60% { clip: rect(22px, 9999px, 12px, 0); }
  80% { clip: rect(5px, 9999px, 30px, 0); }
  100% { clip: rect(14px, 9999px, 8px, 0); }
}

@keyframes glitch-anim-2 {
  0% { clip: rect(12px, 9999px, 28px, 0); }
  25% { clip: rect(4px, 9999px, 16px, 0); }
  50% { clip: rect(20px, 9999px, 10px, 0); }
  75% { clip: rect(8px, 9999px, 22px, 0); }
  100% { clip: rect(16px, 9999px, 5px, 0); }
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 20px;
}

.nav-item {
  font-family: 'Oswald', sans-serif;
  color: #111111;
  text-decoration: none;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  transition: all 0.2s ease;
  cursor: pointer;
}

.nav-icon-item {
  display: flex;
  align-items: center;
  gap: 6px;
}

.nav-icon {
  width: 16px;
  height: 16px;
  color: #111111;
  transition: transform 0.2s ease, stroke 0.2s ease;
}

.nav-item:hover .nav-icon {
  transform: translateY(-2px);
  stroke: #d61c24;
}

.btn-logout:hover .logout-icon {
  transform: translateX(2px);
  stroke: #0066ff;
}

.nav-item:hover,
.nav-item.router-link-active {
  color: #d61c24;
}

.greeting-item {
  cursor: default;
}

.greeting-item:hover {
  color: #111111;
}

.greeting-item:hover .nav-icon {
  stroke: #111111;
  transform: none;
}

.btn-logout {
  font-family: 'Oswald', sans-serif;
  background: transparent;
  border: none;
  color: #111111;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 1.5px;
  cursor: pointer;
  padding: 0;
  text-transform: uppercase;
  transition: color 0.2s ease;
}

.btn-logout:hover {
  color: #0066ff;
}

.btn-register {
  font-family: 'Oswald', sans-serif;
  background: #111111;
  color: #ffffff;
  padding: 8px 18px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 1.5px;
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn-register:hover {
  background: #d61c24;
}

.spinner-sm {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid #111111;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  .search-drawer {
    max-width: 100vw;
  }
  .modal-grid {
    grid-template-columns: 1fr;
  }
}
</style>