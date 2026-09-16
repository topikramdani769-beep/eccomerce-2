import { createRouter, createWebHistory } from 'vue-router';
import HomeView from '../views/HomeView.vue';
import LoginView from '../views/LoginView.vue';
import RegisterView from '../views/RegisterView.vue';
import CartView from '../views/CartView.vue';
import CheckoutView from '../views/CheckoutView.vue';
import OrdersView from '../views/OrdersView.vue';
import AdminDashboardView from '../views/AdminDashboardView.vue';
import { useAuthStore } from '../stores/auth';

const routes = [
  { 
    path: '/', 
    name: 'Home', 
    component: HomeView,
    meta: { title: 'DE LARACHE - Official Store' }
  },
  { 
    path: '/login', 
    name: 'Login', 
    component: LoginView,
    meta: { title: 'Login - DE LARACHE' }
  },
  { 
    path: '/register', 
    name: 'Register', 
    component: RegisterView,
    meta: { title: 'Register - DE LARACHE' }
  },
  { 
    path: '/cart', 
    name: 'Cart', 
    component: CartView, 
    meta: { requiresAuth: true, title: 'Shopping Cart - DE LARACHE' } 
  },
  { 
    path: '/checkout', 
    name: 'Checkout', 
    component: CheckoutView, 
    meta: { requiresAuth: true, title: 'Checkout - DE LARACHE' } 
  },
  { 
    path: '/orders', 
    name: 'Orders', 
    component: OrdersView, 
    meta: { requiresAuth: true, title: 'My Orders - DE LARACHE' } 
  },
  { 
    path: '/admin', 
    name: 'AdminDashboard', 
    component: AdminDashboardView, 
    meta: { requiresAuth: true, requiresAdmin: true, title: 'Admin Dashboard - DE LARACHE' } 
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  
  // 1. Ganti judul tab browser sesuai meta.title
  document.title = to.meta.title || 'DE LARACHE';

  // Baca token dan user data dengan fallback ke LocalStorage jika Pinia state belum rehidrasi
  const token = authStore.token || localStorage.getItem('auth_token');
  const user = authStore.user || JSON.parse(localStorage.getItem('user_data') || 'null');

  if (to.meta.requiresAuth && !token) {
    // Jika butuh login tapi belum ada token -> Redirect ke Login
    next({ name: 'Login' });
  } else if (to.meta.requiresAdmin && (!user || !user.is_admin)) {
    // Jika butuh akses Admin tapi user bukan Admin -> Lempar ke Home
    alert('Akses ditolak! Halaman ini khusus Admin.');
    next({ name: 'Home' });
  } else {
    // Izin diberikan
    next();
  }
});

export default router;