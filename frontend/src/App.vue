<template>
  <div class="bape-app-wrapper">
    <header class="navbar">
      <div class="nav-container">
        <!-- Logo Brand dengan Efek Glitch Streetwear -->
        <router-link to="/" class="brand">
          <span class="brand-text glitch" data-text="A BATHING APE">A BATHING APE</span>
          <span class="brand-sub">®</span>
        </router-link>

        <!-- Navigation Links -->
        <nav class="nav-links">
          <router-link to="/" class="nav-item">SHOP</router-link>
          <router-link to="/cart" class="nav-item">CART</router-link>

          <!-- Status Setelah Login -->
          <template v-if="isAuthenticated">
            <router-link to="/orders" class="nav-item">MY ORDERS</router-link>

            <router-link 
              v-if="Boolean(user?.is_admin)" 
              to="/admin" 
              class="btn-admin"
            >
              ADMIN
            </router-link>

            <span class="user-greeting">HI, {{ user?.name?.toUpperCase() }}</span>

            <button @click="handleLogout" class="btn-logout">LOGOUT</button>
          </template>

          <!-- Status Belum Login -->
          <template v-else>
            <router-link to="/login" class="nav-item link-auth">LOGIN</router-link>
            <router-link to="/register" class="btn-register">REGISTER</router-link>
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
import { computed } from 'vue';
import { useAuthStore } from './stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const isAuthenticated = computed(() => authStore.isAuthenticated);
const user = computed(() => authStore.user);

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap');

.bape-app-wrapper {
  font-family: 'Plus Jakarta Sans', sans-serif;
  background-color: #e2e1dc; /* Sama persis dengan background Login/Register */
  min-height: 100vh;
  color: #111111;
}

/* Header & Navbar */
.navbar {
  background: rgba(244, 243, 239, 0.92); /* Background senada dengan kartu form */
  backdrop-filter: blur(12px);
  border-bottom: 2px solid #111111;
  padding: 16px 32px;
  position: sticky;
  top: 0;
  z-index: 100;
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

/* Brand Logo */
.brand {
  display: flex;
  align-items: flex-start;
  gap: 2px;
  text-decoration: none;
  color: #111111;
}

.brand-text {
  font-family: 'Oswald', sans-serif;
  font-size: 22px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #111111;
  position: relative;
  display: inline-block;
}

.brand-sub {
  font-size: 10px;
  font-weight: 800;
  color: #111111;
}

/* EFEK GLITCH PADA BRAND TEXT (VERSI STREETWEAR LUXURY) */
.glitch {
  position: relative;
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
}

.glitch:hover::before {
  left: -2px;
  text-shadow: 2px 0 #c8b282; /* Warna aksen Gold */
  background: #e2e1dc;
  clip: rect(10px, 550px, 40px, 0);
  animation: glitch-anim 0.3s infinite linear alternate-reverse;
}

.glitch:hover::after {
  left: 2px;
  text-shadow: -2px 0 #111111;
  background: #e2e1dc;
  clip: rect(35px, 550px, 70px, 0);
  animation: glitch-anim 0.2s infinite linear alternate-reverse;
}

@keyframes glitch-anim {
  0% { clip: rect(5px, 9999px, 25px, 0); }
  25% { clip: rect(30px, 9999px, 10px, 0); }
  50% { clip: rect(15px, 9999px, 45px, 0); }
  75% { clip: rect(40px, 9999px, 20px, 0); }
  100% { clip: rect(10px, 9999px, 35px, 0); }
}

/* Nav Links */
.nav-links {
  display: flex;
  align-items: center;
  gap: 24px;
}

.nav-item {
  font-family: 'Oswald', sans-serif;
  color: #555555;
  text-decoration: none;
  font-size: 13px;
  font-weight: 600;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  transition: all 0.2s ease;
}

.nav-item:hover,
.nav-item.router-link-active {
  color: #111111;
}

.nav-item.router-link-active {
  border-bottom: 2px solid #111111;
  padding-bottom: 2px;
}

/* Buttons & Elements */
.btn-admin {
  font-family: 'Oswald', sans-serif;
  color: #111111;
  background: #c8b282;
  border: 1px solid #111111;
  padding: 4px 10px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 1.5px;
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn-admin:hover {
  background: #111111;
  color: #ffffff;
}

.user-greeting {
  font-family: 'Oswald', sans-serif;
  font-size: 12px;
  color: #111111;
  font-weight: 700;
  letter-spacing: 1.5px;
  background: rgba(200, 178, 130, 0.25);
  padding: 4px 10px;
  border-radius: 4px;
}

.btn-logout {
  font-family: 'Oswald', sans-serif;
  background: transparent;
  border: 1px solid #dc2626;
  color: #dc2626;
  padding: 4px 12px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 1.5px;
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.btn-logout:hover {
  background: #dc2626;
  color: #ffffff;
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
  border-radius: 4px;
  transition: all 0.2s ease;
}

.btn-register:hover {
  background: #c8b282;
  color: #111111;
}

.main-content {
  padding: 0;
}

@media (max-width: 768px) {
  .navbar {
    padding: 14px 16px;
  }
  
  .nav-links {
    gap: 12px;
  }
}
</style>