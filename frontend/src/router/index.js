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
  { path: '/', name: 'Home', component: HomeView },
  { path: '/login', name: 'Login', component: LoginView },
  { path: '/register', name: 'Register', component: RegisterView },
  { path: '/cart', name: 'Cart', component: CartView, meta: { requiresAuth: true } },
  { path: '/checkout', name: 'Checkout', component: CheckoutView, meta: { requiresAuth: true } },
  { path: '/orders', name: 'Orders', component: OrdersView, meta: { requiresAuth: true } },
  { path: '/admin', name: 'AdminDashboard', component: AdminDashboardView, meta: { requiresAuth: true, requiresAdmin: true } },
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  
  // Baca token dan user data dengan fallback ke LocalStorage jika Pinia state belum rehidrasi
  const token = authStore.token || localStorage.getItem('auth_token');
  const user = authStore.user || JSON.parse(localStorage.getItem('user_data') || 'null');

  if (to.meta.requiresAuth && !token) {
    // 1. Jika butuh login tapi belum ada token -> Redirect ke Login
    next({ name: 'Login' });
  } else if (to.meta.requiresAdmin && (!user || !user.is_admin)) {
    // 2. Jika butuh akses Admin tapi user bukan Admin -> Lempar ke Home
    alert('Akses ditolak! Halaman ini khusus Admin.');
    next({ name: 'Home' });
  } else {
    // 3. Izin diberikan
    next();
  }
});

export default router;