<template>
  <div class="delarache-app-wrapper">
    <!-- Navbar hanya akan muncul jika bukan di halaman /login atau /register -->
    <header v-if="showNavbar" class="navbar">
      <div class="nav-container">
        <!-- Brand Logo DE LARACHE dengan Ikon Gorila & Efek Glitch -->
        <router-link to="/" class="brand">
          <!-- Siluet Ikon Gorila (Ape Head) -->
          <svg class="gorilla-icon" viewBox="0 0 512 512" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path d="M256 32C150 32 80 100 80 208c0 62 26 116 68 152 4 4 8 12 8 18v34c0 18 14 32 32 32h136c18 0 32-14 32-32v-34c0-6 4-14 8-18 42-36 68-90 68-152C432 100 362 32 256 32zm-64 128c18 0 32 14 32 32s-14 32-32 32-32-14-32-32 14-32 32-32zm128 0c18 0 32 14 32 32s-14 32-32 32-32-14-32-32 14-32 32-32zm-64 160c-28 0-52-16-60-40h120c-8 24-32 40-60 40z"/>
          </svg>

          <!-- Teks Brand Glitch -->
          <span class="brand-text glitch" data-text="DE LARACHE">DE LARACHE</span>
          <span class="brand-sub">®</span>
        </router-link>

        <!-- Search Bar Streetwear Style -->
        <div class="search-box">
          <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input 
            type="text" 
            v-model="searchQuery" 
            @keyup.enter="handleSearch"
            placeholder="CARIPRODUK..." 
            class="search-input"
          />
        </div>

        <!-- Navigasi Utama -->
        <nav class="nav-links">
          <router-link to="/" class="nav-item">
            <span class="glitch" data-text="SHOP">SHOP</span>
          </router-link>
          
          <!-- Menu Cart -->
          <router-link to="/cart" class="nav-item nav-icon-item">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
              <line x1="3" y1="6" x2="21" y2="6"></line>
              <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
            <span class="glitch" data-text="CART">CART</span>
          </router-link>

          <!-- Status Setelah Login -->
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

            <router-link 
              v-if="Boolean(user?.is_admin)" 
              to="/admin" 
              class="nav-item nav-icon-item"
            >
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

          <!-- Status Belum Login -->
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
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from './stores/auth';
import { useRouter, useRoute } from 'vue-router';

const searchQuery = ref('');
const authStore = useAuthStore();
const router = useRouter();
const route = useRoute();

const isAuthenticated = computed(() => authStore.isAuthenticated);
const user = computed(() => authStore.user);

// Menyembunyikan navbar jika sedang di halaman /login atau /register
const showNavbar = computed(() => {
  const hiddenRoutes = ['/login', '/register'];
  return !hiddenRoutes.includes(route.path);
});

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ path: '/', query: { search: searchQuery.value.trim() } });
  }
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

/* Header Navbar */
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

/* Brand Group */
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

/* Search Bar Streetwear Style */
.search-box {
  display: flex;
  align-items: center;
  background: #f4f4f4;
  border: 1.5px solid #111111;
  padding: 6px 12px;
  border-radius: 2px;
  width: 100%;
  max-width: 220px;
  transition: all 0.2s ease;
}

.search-box:focus-within {
  border-color: #d61c24;
  background: #ffffff;
  box-shadow: 0 0 0 1px #d61c24;
}

.search-icon {
  width: 14px;
  height: 14px;
  color: #111111;
  margin-right: 8px;
  flex-shrink: 0;
}

.search-input {
  border: none;
  background: transparent;
  outline: none;
  font-family: 'Oswald', sans-serif;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 1px;
  color: #111111;
  width: 100%;
}

.search-input::placeholder {
  color: #888888;
}

/* Efek Glitch Standard (Merah & Cyan) */
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

/* Efek Glitch Khusus Logout (Biru & Cyan) */
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

/* Styling Item Navigasi & Ikon */
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

.nav-item.router-link-active {
  border-bottom: 2px solid #111111;
  padding-bottom: 2px;
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

/* Tombol Logout */
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

/* Tombol Register */
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

.main-content {
  padding: 0;
}

@media (max-width: 900px) {
  .search-box {
    max-width: 140px;
  }
}

@media (max-width: 768px) {
  .navbar {
    padding: 12px 16px;
  }

  .nav-container {
    flex-wrap: wrap;
  }

  .search-box {
    order: 3;
    max-width: 100%;
    margin-top: 8px;
  }

  .nav-links {
    gap: 12px;
  }

  .brand-text {
    font-size: 18px;
  }

  .gorilla-icon {
    width: 22px;
    height: 22px;
  }
}
</style>