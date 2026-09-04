<template>
  <div class="admin-page">
    <div class="container">
      <div class="admin-header">
        <h2>ADMIN CONTROL CENTER</h2>
        <div class="tab-buttons">
          <button 
            :class="{ active: activeTab === 'products' }" 
            @click="activeTab = 'products'"
          >PRODUCTS</button>
          <button 
            :class="{ active: activeTab === 'categories' }" 
            @click="activeTab = 'categories'"
          >CATEGORIES</button>
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
          <button @click="toggleForm('product')" class="btn-action">
            {{ showProductForm ? 'CANCEL' : '+ ADD NEW PRODUCT' }}
          </button>
        </div>

        <!-- Form Tambah / Edit Produk -->
        <form v-if="showProductForm" @submit.prevent="saveProduct" class="admin-form mt-4">
          <h4>{{ isEditingProduct ? 'EDIT PRODUCT' : 'CREATE NEW PRODUCT' }}</h4>
          <div class="form-grid">
            <div class="form-group">
              <label>Product Name</label>
              <input type="text" v-model="productForm.name" class="form-control" required />
            </div>
            <div class="form-group">
              <label>Category</label>
              <select v-model="productForm.category_id" class="form-control" required>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Price (IDR)</label>
              <input type="number" v-model="productForm.price" class="form-control" required />
            </div>
            <div class="form-group">
              <label>Stock</label>
              <input type="number" v-model="productForm.stock" class="form-control" required />
            </div>
            <div class="form-group">
              <label>Size</label>
              <input type="text" v-model="productForm.size" class="form-control" placeholder="e.g. S, M, L, XL / 42" required />
            </div>
          </div>
          
          <div class="form-group mt-4">
            <label>Product Images (Multiple files allowed)</label>
            <input 
              type="file" 
              @change="handleFileUpload" 
              accept="image/*" 
              class="form-control file-input" 
              multiple
              :required="!isEditingProduct"
            />
          </div>

          <button type="submit" class="btn-submit mt-4 w-full">
            {{ isEditingProduct ? 'UPDATE PRODUCT' : 'SAVE PRODUCT' }}
          </button>
        </form>

        <!-- Table Produk -->
        <div class="table-responsive mt-6">
          <table class="admin-table">
            <thead>
              <tr>
                <th>IMAGE</th>
                <th>NAME</th>
                <th>CATEGORY</th>
                <th>SIZE</th>
                <th>PRICE</th>
                <th>STOCK</th>
                <th>ACTION</th>
              </tr>
            </thead>
            <TransitionGroup tag="tbody" name="fade-row">
              <tr v-for="product in products" :key="product.id">
                <td>
                  <img 
                    :src="Array.isArray(product.image) ? product.image[0] : product.image" 
                    class="table-img" 
                    :alt="product.name" 
                  />
                </td>
                <td class="font-bold">{{ product.name }}</td>
                <td><span class="badge-cat">{{ product.category?.name || '-' }}</span></td>
                <td>{{ product.size || '-' }}</td>
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
      </div>

      <!-- TAB 2: CATEGORY MANAGEMENT -->
      <div v-if="activeTab === 'categories'" class="tab-content">
        <div class="admin-actions">
          <h3>Category Management</h3>
          <button @click="toggleForm('category')" class="btn-action">
            {{ showCategoryForm ? 'CANCEL' : '+ ADD NEW CATEGORY' }}
          </button>
        </div>

        <!-- Form Tambah / Edit Kategori -->
        <form v-if="showCategoryForm" @submit.prevent="saveCategory" class="admin-form mt-4">
          <h4>{{ isEditingCategory ? 'EDIT CATEGORY' : 'CREATE NEW CATEGORY' }}</h4>
          <div class="form-group">
            <label>Category Name</label>
            <input type="text" v-model="categoryForm.name" class="form-control" required placeholder="e.g. T-Shirt, Outerwear" />
          </div>
          <button type="submit" class="btn-submit mt-4 w-full">
            {{ isEditingCategory ? 'UPDATE CATEGORY' : 'SAVE CATEGORY' }}
          </button>
        </form>

        <!-- Table Kategori -->
        <div class="table-responsive mt-6">
          <table class="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>CATEGORY NAME</th>
                <th>ACTION</th>
              </tr>
            </thead>
            <TransitionGroup tag="tbody" name="fade-row">
              <tr v-for="cat in categories" :key="cat.id">
                <td>#{{ cat.id }}</td>
                <td class="font-bold">{{ cat.name }}</td>
                <td class="action-buttons">
                  <button @click="editCategory(cat)" class="btn-edit">EDIT</button>
                  <button @click="deleteCategory(cat.id)" class="btn-delete">DELETE</button>
                </td>
              </tr>
            </TransitionGroup>
          </table>
        </div>
      </div>

      <!-- TAB 3: ORDER MANAGEMENT -->
      <div v-if="activeTab === 'orders'" class="tab-content">
        <h3>Customer Orders History</h3>
        <div class="table-responsive mt-4">
          <table class="admin-table">
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
                <td class="font-bold">#{{ order.order_number || order.id }}</td>
                <td>{{ order.user?.name || 'Guest' }}</td>
                <td>Rp {{ Number(order.total_amount).toLocaleString('id-ID') }}</td>
                <td>
                  <span :class="['status-tag', order.status]">{{ order.status ? order.status.toUpperCase() : 'PENDING' }}</span>
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
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import api from '../services/api';

const activeTab = ref('products');

// State Produk
const products = ref([]);
const showProductForm = ref(false);
const isEditingProduct = ref(false);
const editingProductId = ref(null);
const selectedFiles = ref([]);

const productForm = reactive({
  name: '',
  category_id: '',
  price: 0,
  stock: 10,
  size: 'M',
  image: ''
});

// State Kategori
const categories = ref([]);
const showCategoryForm = ref(false);
const isEditingCategory = ref(false);
const editingCategoryId = ref(null);
const categoryForm = reactive({
  name: ''
});

// State Orders
const orders = ref([]);

const handleFileUpload = (event) => {
  selectedFiles.value = Array.from(event.target.files);
};

const toggleForm = (type) => {
  if (type === 'product') {
    showProductForm.value = !showProductForm.value;
    if (!showProductForm.value) resetProductForm();
  } else if (type === 'category') {
    showCategoryForm.value = !showCategoryForm.value;
    if (!showCategoryForm.value) resetCategoryForm();
  }
};

const resetProductForm = () => {
  productForm.name = '';
  productForm.category_id = categories.value[0]?.id || '';
  productForm.price = 0;
  productForm.stock = 10;
  productForm.size = 'M';
  productForm.image = '';
  selectedFiles.value = [];
  isEditingProduct.value = false;
  editingProductId.value = null;
};

const resetCategoryForm = () => {
  categoryForm.name = '';
  isEditingCategory.value = false;
  editingCategoryId.value = null;
};

const fetchProducts = async () => {
  try {
    const res = await api.get('/products');
    products.value = res.data;
  } catch (e) {
    console.error('Error fetching products:', e);
  }
};

const fetchCategories = async () => {
  try {
    const res = await api.get('/categories');
    categories.value = res.data;
    if (categories.value.length > 0 && !productForm.category_id) {
      productForm.category_id = categories.value[0].id;
    }
  } catch (e) {
    console.error('Error fetching categories:', e);
  }
};

const fetchOrders = async () => {
  try {
    const res = await api.get('/admin/orders');
    orders.value = res.data;
  } catch (e) {
    console.error('Error fetching orders:', e);
  }
};

const saveProduct = async () => {
  try {
    const formData = new FormData();
    formData.append('name', productForm.name);
    formData.append('category_id', productForm.category_id);
    formData.append('price', productForm.price);
    formData.append('stock', productForm.stock);
    formData.append('size', productForm.size);
    
    if (selectedFiles.value.length > 0) {
      selectedFiles.value.forEach((file) => {
        formData.append('images[]', file);
      });
    }

    const config = {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    };

    if (isEditingProduct.value) {
      formData.append('_method', 'PUT');
      await api.post(`/admin/products/${editingProductId.value}`, formData, config);
      alert('Produk berhasil diupdate!');
    } else {
      await api.post('/admin/products', formData, config);
      alert('Produk berhasil ditambahkan!');
    }

    showProductForm.value = false;
    resetProductForm();
    fetchProducts();
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menyimpan produk');
  }
};

const editProduct = (product) => {
  isEditingProduct.value = true;
  editingProductId.value = product.id;
  productForm.name = product.name;
  productForm.category_id = product.category_id;
  productForm.price = product.price;
  productForm.stock = product.stock;
  productForm.size = product.size || 'M';
  productForm.image = product.image;
  selectedFiles.value = [];
  showProductForm.value = true;
};

const deleteProduct = async (id) => {
  if (!confirm('Hapus produk ini dari katalog?')) return;
  try {
    await api.delete(`/admin/products/${id}`);
    products.value = products.value.filter(p => p.id !== id);
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menghapus produk');
  }
};

const saveCategory = async () => {
  try {
    if (isEditingCategory.value) {
      await api.put(`/admin/categories/${editingCategoryId.value}`, categoryForm);
      alert('Kategori berhasil diupdate!');
    } else {
      await api.post('/admin/categories', categoryForm);
      alert('Kategori berhasil ditambahkan!');
    }
    showCategoryForm.value = false;
    resetCategoryForm();
    fetchCategories();
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menyimpan kategori');
  }
};

const editCategory = (category) => {
  isEditingCategory.value = true;
  editingCategoryId.value = category.id;
  categoryForm.name = category.name;
  showCategoryForm.value = true;
};

const deleteCategory = async (id) => {
  if (!confirm('Hapus kategori ini?')) return;
  try {
    await api.delete(`/admin/categories/${id}`);
    categories.value = categories.value.filter(c => c.id !== id);
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menghapus kategori');
  }
};

const updateOrderStatus = async (orderId, newStatus) => {
  try {
    await api.put(`/admin/orders/${orderId}/status`, { status: newStatus });
    alert('Status pesanan berhasil diperbarui!');
    fetchOrders();
  } catch (e) {
    alert('Gagal memperbarui status pesanan');
  }
};

watch(activeTab, (newTab) => {
  if (newTab === 'products') {
    fetchProducts();
    fetchCategories();
  }
  if (newTab === 'categories') fetchCategories();
  if (newTab === 'orders') fetchOrders();
});

onMounted(() => {
  fetchProducts();
  fetchCategories();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@600;700&family=Plus+Jakarta+Sans:wght@500;700;800&display=swap');

.admin-page {
  font-family: 'Plus Jakarta Sans', sans-serif;
  background-color: #e2e1dc;
  min-height: calc(100vh - 70px);
  padding: 40px 20px;
  color: #111111;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
}

.admin-header {
  display: flex;
  flex-direction: column;
  gap: 15px;
  border-bottom: 2px solid #111111;
  padding-bottom: 15px;
  margin-bottom: 30px;
}

@media (min-width: 768px) {
  .admin-header {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
}

.admin-header h2 {
  font-family: 'Oswald', sans-serif;
  font-size: 28px;
  letter-spacing: 2px;
  margin: 0;
}

.tab-buttons {
  display: flex;
  gap: 8px;
}

.tab-buttons button {
  font-family: 'Oswald', sans-serif;
  background: #f4f3ef;
  border: 1px solid rgba(0, 0, 0, 0.15);
  color: #111111;
  padding: 8px 18px;
  cursor: pointer;
  font-weight: 700;
  letter-spacing: 1.5px;
  font-size: 13px;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.tab-buttons button.active {
  background: #111111;
  color: #c8b282;
  border-color: #111111;
}

.admin-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.admin-actions h3 {
  font-family: 'Oswald', sans-serif;
  font-size: 20px;
  letter-spacing: 1px;
}

.btn-action {
  font-family: 'Oswald', sans-serif;
  background: #111111;
  color: #ffffff;
  border: none;
  padding: 10px 20px;
  font-size: 12px;
  letter-spacing: 1.5px;
  cursor: pointer;
  border-radius: 6px;
  transition: background 0.2s;
}

.btn-action:hover {
  background: #c8b282;
  color: #111111;
}

.admin-form {
  background: #f4f3ef;
  padding: 24px;
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}

.admin-form h4 {
  font-family: 'Oswald', sans-serif;
  margin-bottom: 20px;
  color: #8f794b;
  font-size: 18px;
  letter-spacing: 1px;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
}

@media (min-width: 768px) {
  .form-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.form-group label {
  display: block;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 1px;
  margin-bottom: 6px;
  text-transform: uppercase;
}

.form-control {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid rgba(0, 0, 0, 0.15);
  border-radius: 6px;
  background: #ffffff;
  font-family: inherit;
  font-size: 14px;
  box-sizing: border-box;
}

.btn-submit {
  font-family: 'Oswald', sans-serif;
  background: #111111;
  color: #ffffff;
  border: none;
  padding: 12px;
  font-size: 13px;
  letter-spacing: 2px;
  cursor: pointer;
  border-radius: 6px;
  transition: background 0.2s;
}

.btn-submit:hover {
  background: #c8b282;
  color: #111111;
}

.table-responsive {
  overflow-x: auto;
  background: #f4f3ef;
  border-radius: 10px;
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}

.admin-table {
  width: 100%;
  border-collapse: collapse;
}

.admin-table th {
  font-family: 'Oswald', sans-serif;
  background: #111111;
  color: #ffffff;
  padding: 14px 16px;
  text-align: left;
  font-size: 13px;
  letter-spacing: 1.5px;
}

.admin-table td {
  padding: 14px 16px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  font-size: 13px;
}

.font-bold {
  font-weight: 800;
}

.badge-cat {
  font-family: 'Oswald', sans-serif;
  font-size: 11px;
  color: #8f794b;
  letter-spacing: 1px;
  font-weight: 700;
}

.table-img {
  width: 48px;
  height: 48px;
  object-fit: cover;
  border-radius: 6px;
  background: #e2e1dc;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.btn-edit {
  background: none;
  border: 1px solid #8f794b;
  color: #8f794b;
  padding: 6px 12px;
  font-weight: 700;
  font-size: 11px;
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.2s;
}

.btn-edit:hover {
  background: #8f794b;
  color: #ffffff;
}

.btn-delete {
  background: none;
  border: 1px solid #dc2626;
  color: #dc2626;
  padding: 6px 12px;
  font-weight: 700;
  font-size: 11px;
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.2s;
}

.btn-delete:hover {
  background: #dc2626;
  color: #ffffff;
}

.status-select {
  background: #ffffff;
  color: #111111;
  border: 1px solid rgba(0, 0, 0, 0.2);
  padding: 6px 10px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 700;
}

.status-tag {
  font-family: 'Oswald', sans-serif;
  font-size: 11px;
  padding: 4px 8px;
  font-weight: 700;
  letter-spacing: 1px;
  border-radius: 4px;
}

.status-tag.pending { background: #fef3c7; color: #d97706; }
.status-tag.paid { background: #d1fae5; color: #059669; }
.status-tag.cancelled { background: #fee2e2; color: #dc2626; }

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
  background-color: rgba(239, 68, 68, 0.15);
}

.fade-row-move {
  transition: transform 0.4s ease;
}
</style>