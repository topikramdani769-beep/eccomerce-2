<template>
  <div class="container">
    <div class="hero-banner">
      <h1>NEW ARRIVALS</h1>
      <p>Official BAPE Streetwear Collection</p>
    </div>

    <div v-if="loading" class="state-msg">Loading catalog...</div>

    <div v-else class="product-grid">
      <div v-for="product in products" :key="product.id" class="product-card">
        <div class="image-wrapper">
          <img :src="product.image" :alt="product.name" />
        </div>
        <div class="card-body">
          <span class="category-badge">{{ product.category?.name || 'Streetwear' }}</span>
          <h3 class="product-title">{{ product.name }}</h3>
          <div class="card-footer">
            <span class="price">Rp {{ Number(product.price).toLocaleString('id-ID') }}</span>
            <button @click="addToCart(product.id)" class="btn-bape">+ Cart</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const products = ref([]);
const loading = ref(true);
const authStore = useAuthStore();
const router = useRouter();

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

const addToCart = async (productId) => {
  if (!authStore.isAuthenticated) {
    alert('Silakan login terlebih dahulu!');
    return router.push('/login');
  }

  try {
    await api.post('/cart', { product_id: productId, quantity: 1 });
    alert('Produk berhasil ditambahkan ke keranjang!');
  } catch (error) {
    alert(error.response?.data?.message || 'Gagal menambahkan ke keranjang');
  }
};

onMounted(() => {
  fetchProducts();
});
</script>

<style scoped>
.hero-banner {
  text-align: center;
  padding: 30px 0;
  border-bottom: 1px solid var(--border-color);
  margin-bottom: 40px;
}

.hero-banner h1 {
  font-size: 32px;
  letter-spacing: 4px;
}

.hero-banner p {
  color: var(--text-muted);
  font-size: 14px;
}

.state-msg {
  text-align: center;
  color: var(--text-muted);
  padding: 50px 0;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 25px;
}

.product-card {
  background-color: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 4px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  transition: transform 0.2s, border-color 0.2s;
}

.product-card:hover {
  border-color: var(--accent-gold);
  transform: translateY(-3px);
}

.image-wrapper img {
  width: 100%;
  height: 240px;
  object-fit: cover;
  background-color: var(--bg-secondary);
}

.card-body {
  padding: 15px;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  justify-content: space-between;
}

.category-badge {
  font-size: 10px;
  color: var(--accent-gold);
  text-transform: uppercase;
  letter-spacing: 1px;
  font-weight: 700;
}

.product-title {
  font-size: 16px;
  font-weight: 700;
  margin: 5px 0 15px 0;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.price {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-main);
}
</style>