<template>
  <div class="container">
    <div class="admin-header">
      <h2>ADMIN CONTROL CENTER</h2>
      <div class="tab-buttons">
        <button 
          :class="{ active: activeTab === 'products' }" 
          @click="activeTab = 'products'"
        >PRODUCTS</button>
        <button 
          :class="{ active: activeTab === 'orders' }" 
          @click="activeTab = 'orders'"
        >ORDERS</button>
      </div>
    </div>

    <!-- TAB 1: PRODUCT MANAGEMENT -->
    <div v-if="activeTab === 'products'" class="tab-content">
      <div class="admin-actions">
        <h3>Product Catalog Management</h3>
        <button @click="toggleForm" class="btn-bape">
          {{ showForm ? 'CANCEL' : '+ ADD NEW PRODUCT' }}
        </button>
      </div>

      <!-- Form Tambah / Edit Produk -->
      <form v-if="showForm" @submit.prevent="saveProduct" class="admin-form mt-4">
        <h4>{{ isEditing ? 'EDIT PRODUCT' : 'CREATE NEW PRODUCT' }}</h4>
        <div class="form-grid">
          <div class="form-group">
            <label>Product Name</label>
            <input type="text" v-model="form.name" class="form-control" required />
          </div>
          <div class="form-group">
            <label>Category ID</label>
            <input type="number" v-model="form.category_id" class="form-control" required />
          </div>
          <div class="form-group">
            <label>Price (IDR)</label>
            <input type="number" v-model="form.price" class="form-control" required />
          </div>
          <div class="form-group">
            <label>Stock</label>
            <input type="number" v-model="form.stock" class="form-control" required />
          </div>
        </div>
        <div class="form-group mt-4">
          <label>Image URL</label>
          <input type="text" v-model="form.image" class="form-control" required placeholder="https://..." />
        </div>
        <button type="submit" class="btn-bape mt-4 w-full">
          {{ isEditing ? 'UPDATE PRODUCT' : 'SAVE PRODUCT' }}
        </button>
      </form>

      <!-- Table Produk dengan Animasi TransitionGroup -->
      <table class="admin-table mt-6">
        <thead>
          <tr>
            <th>IMAGE</th>
            <th>NAME</th>
            <th>PRICE</th>
            <th>STOCK</th>
            <th>ACTION</th>
          </tr>
        </thead>
        <TransitionGroup tag="tbody" name="fade-row">
          <tr v-for="product in products" :key="product.id">
            <td><img :src="product.image" class="table-img" :alt="product.name" /></td>
            <td>{{ product.name }}</td>
            <td>Rp {{ Number(product.price).toLocaleString('id-ID') }}</td>
            <td>{{ product.stock }}</td>
            <td class="action-buttons">
              <button @click="editProduct(product)" class="btn-edit">EDIT</button>
              <button @click="deleteProduct(product.id)" class="btn-delete">DELETE</button>
            </td>
          </tr>
        </TransitionGroup>
      </table>
    </div>

    <!-- TAB 2: ORDER MANAGEMENT -->
    <div v-if="activeTab === 'orders'" class="tab-content">
      <h3>Customer Orders History</h3>
      <table class="admin-table mt-4">
        <thead>
          <tr>
            <th>ORDER #</th>
            <th>CUSTOMER</th>
            <th>TOTAL</th>
            <th>STATUS</th>
            <th>ACTION</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.id">
            <td>#{{ order.order_number || order.id }}</td>
            <td>{{ order.user?.name || 'Guest' }}</td>
            <td>Rp {{ Number(order.total_amount).toLocaleString('id-ID') }}</td>
            <td>
              <span :class="['status-tag', order.status]">{{ order.status.toUpperCase() }}</span>
            </td>
            <td>
              <select 
                :value="order.status" 
                @change="updateOrderStatus(order.id, $event.target.value)"
                class="status-select"
              >
                <option value="pending">PENDING</option>
                <option value="paid">PAID</option>
                <option value="cancelled">CANCELLED</option>
              </select>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import api from '../services/api';

const activeTab = ref('products');
const showForm = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const products = ref([]);
const orders = ref([]);

const form = reactive({
  name: '',
  category_id: 1,
  price: 0,
  stock: 10,
  image: ''
});

const resetForm = () => {
  form.name = '';
  form.category_id = 1;
  form.price = 0;
  form.stock = 10;
  form.image = '';
  isEditing.value = false;
  editingId.value = null;
};

const toggleForm = () => {
  showForm.value = !showForm.value;
  if (!showForm.value) resetForm();
};

const fetchProducts = async () => {
  try {
    const res = await api.get('/products');
    products.value = res.data;
  } catch (e) {
    console.error('Error fetching products:', e);
  }
};

const fetchOrders = async () => {
  try {
    const res = await api.get('/admin/orders');
    orders.value = res.data;
  } catch (e) {
    console.error('Error fetching orders:', e);
    if (e.response?.status === 401) {
      alert('Sesi login telah berakhir atau Anda tidak memiliki akses Admin.');
    }
  }
};

const saveProduct = async () => {
  try {
    if (isEditing.value) {
      await api.put(`/admin/products/${editingId.value}`, form);
      alert('Produk berhasil diupdate!');
    } else {
      await api.post('/admin/products', form);
      alert('Produk berhasil ditambahkan!');
    }
    showForm.value = false;
    resetForm();
    fetchProducts();
  } catch (e) {
    console.error('Error saving product:', e);
    alert(e.response?.data?.message || 'Gagal menyimpan produk');
  }
};

const editProduct = (product) => {
  isEditing.value = true;
  editingId.value = product.id;
  form.name = product.name;
  form.category_id = product.category_id;
  form.price = product.price;
  form.stock = product.stock;
  form.image = product.image;
  showForm.value = true;
};

// HAPUS PRODUK DENGAN ANIMASI HAPUS LOKAL
const deleteProduct = async (id) => {
  if (!confirm('Hapus produk ini dari katalog?')) return;
  try {
    await api.delete(`/admin/products/${id}`);
    
    // Hapus baris data secara lokal dari state Vue agar animasi jalan & data langsung hilang
    products.value = products.value.filter(product => product.id !== id);
  } catch (e) {
    console.error('Error deleting product:', e);
    alert(e.response?.data?.message || 'Gagal menghapus produk');
  }
};

const updateOrderStatus = async (orderId, newStatus) => {
  try {
    await api.put(`/admin/orders/${orderId}/status`, { status: newStatus });
    alert('Status pesanan berhasil diperbarui!');
    fetchOrders();
  } catch (e) {
    console.error('Error updating order:', e);
    alert('Gagal memperbarui status pesanan');
  }
};

watch(activeTab, (newTab) => {
  if (newTab === 'products') fetchProducts();
  if (newTab === 'orders') fetchOrders();
});

onMounted(() => {
  fetchProducts();
});
</script>

<style scoped>
.admin-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid var(--border-color);
  padding-bottom: 15px;
  margin-bottom: 25px;
}

.tab-buttons button {
  background: none;
  border: 1px solid var(--border-color);
  color: var(--text-main);
  padding: 8px 16px;
  cursor: pointer;
  font-weight: bold;
}

.tab-buttons button.active {
  background: var(--accent-gold);
  color: #000;
  border-color: var(--accent-gold);
}

.admin-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.admin-form {
  background: var(--bg-card);
  padding: 20px;
  border: 1px solid var(--border-color);
}

.admin-form h4 {
  margin-bottom: 15px;
  color: var(--accent-gold);
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 15px;
}

.admin-table {
  width: 100%;
  border-collapse: collapse;
  background: var(--bg-card);
}

.admin-table th, .admin-table td {
  border: 1px solid var(--border-color);
  padding: 12px;
  text-align: left;
  font-size: 13px;
}

.table-img {
  width: 45px;
  height: 45px;
  object-fit: cover;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.btn-edit {
  background: none;
  border: 1px solid var(--accent-gold);
  color: var(--accent-gold);
  padding: 4px 8px;
  cursor: pointer;
}

.btn-delete {
  background: none;
  border: 1px solid #ff4d4d;
  color: #ff4d4d;
  padding: 4px 8px;
  cursor: pointer;
}

.status-select {
  background: var(--bg-secondary);
  color: white;
  border: 1px solid var(--border-color);
  padding: 4px 8px;
}

.status-tag {
  font-size: 10px;
  padding: 2px 6px;
  font-weight: bold;
}

.status-tag.pending { color: #d97706; }
.status-tag.paid { color: #059669; }
.status-tag.cancelled { color: #dc2626; }

.mt-4 { margin-top: 16px; }
.mt-6 { margin-top: 24px; }
.w-full { width: 100%; }

/* --- CSS ANIMASI HAPUS & TAMBAH --- */
.fade-row-enter-active,
.fade-row-leave-active {
  transition: all 0.4s ease;
}

.fade-row-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.fade-row-leave-to {
  opacity: 0;
  transform: translateX(30px);
  background-color: rgba(255, 77, 77, 0.2);
}

.fade-row-move {
  transition: transform 0.4s ease;
}
</style>