<template>
  <header class="navbar">
    <div class="container nav-container">
      <router-link to="/" class="brand">A BATHING APE®</router-link>

      <nav class="nav-links">
        <router-link to="/">SHOP</router-link>
        <router-link to="/cart">CART</router-link>

        <template v-if="isAuthenticated">
          <router-link to="/orders">MY ORDERS</router-link>

          <!-- Pengecekan status Admin secara aman -->
          <router-link v-if="Boolean(user?.is_admin)" to="/admin" class="btn-admin">
            ADMIN PANEL
          </router-link>

          <span class="user-greeting">HI, {{ user?.name?.toUpperCase() }}</span>
          <button @click="handleLogout" class="btn-logout">LOGOUT</button>
        </template>

        <template v-else>
          <router-link to="/login" class="link-auth">LOGIN</router-link>
          <router-link to="/register" class="btn-bape btn-nav">REGISTER</router-link>
        </template>
      </nav>
    </div>
  </header>

  <main class="main-content">
    <router-view />
  </main>
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
.navbar {
  background-color: var(--bg-card);
  border-bottom: 1px solid var(--border-color);
  padding: 15px 0;
  position: sticky;
  top: 0;
  z-index: 100;
}

.nav-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.brand {
  font-size: 18px;
  font-weight: 900;
  letter-spacing: 3px;
  color: var(--text-main);
  text-decoration: none;
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 20px;
}

.nav-links a {
  color: var(--text-main);
  text-decoration: none;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 1px;
  transition: color 0.2s;
}

.nav-links a:hover,
.nav-links a.router-link-active {
  color: var(--accent-gold);
}

.btn-admin {
  color: var(--accent-gold) !important;
  border: 1px solid var(--accent-gold);
  padding: 4px 10px;
  border-radius: 2px;
  font-size: 11px !important;
  transition: background-color 0.2s;
}

.btn-admin:hover {
  background-color: var(--accent-gold);
  color: #000 !important;
}

.user-greeting {
  font-size: 11px;
  color: var(--accent-gold);
  font-weight: bold;
  letter-spacing: 1px;
}

.btn-logout {
  background: none;
  border: 1px solid var(--border-color);
  color: #ff4d4d;
  padding: 6px 12px;
  font-size: 11px;
  font-weight: bold;
  cursor: pointer;
  border-radius: 2px;
}

.btn-nav {
  padding: 6px 14px;
  font-size: 11px;
}

.main-content {
  padding: 40px 0;
}
</style>