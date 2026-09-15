<template>
  <div class="orders-page">
    <div class="container">
      <h2 class="page-title">MY ORDERS</h2>

      <div v-if="loading" class="state-msg">
        <span class="spinner"></span>
        <p>Loading order history...</p>
      </div>

      <div v-else-if="orders.length === 0" class="empty-state">
        <p>You haven't placed any orders yet.</p>
        <router-link to="/" class="btn-shop mt-4">Start Shopping</router-link>
      </div>

      <div v-else class="orders-list">
        <div v-for="order in orders" :key="order.id" class="order-card">
          <div class="order-header">
            <div>
              <span class="order-number">ORDER #{{ order.order_number || order.id }}</span>
              <small class="order-date">{{ new Date(order.created_at).toLocaleDateString('id-ID') }}</small>
            </div>
            <span :class="['status-badge', order.status || 'pending']">
              {{ (order.status || 'PENDING').toUpperCase() }}
            </span>
          </div>

          <div class="order-items">
            <div v-for="item in order.order_items" :key="item.id" class="order-item-row">
              <span class="item-name">{{ item.product?.name }} x {{ item.quantity }}</span>
              <span class="item-price">Rp {{ Number(item.price * item.quantity).toLocaleString('id-ID') }}</span>
            </div>
          </div>

          <div class="order-footer">
            <span class="total-label">Total Amount</span>
            <strong class="total-amount">Rp {{ Number(order.total_amount).toLocaleString('id-ID') }}</strong>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../services/api';

const orders = ref([]);
const loading = ref(true);

const fetchOrders = async () => {
  try {
    const res = await api.get('/orders');
    orders.value = res.data;
  } catch (e) {
    console.error('Error loading orders:', e);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchOrders();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800;900&display=swap');

.orders-page {
  font-family: 'Montserrat', sans-serif;
  background-color: #f4f5f7;
  min-height: calc(100vh - 70px);
  padding: 40px 20px;
  color: #111111;
}

.container {
  max-width: 900px;
  margin: 0 auto;
}

.page-title {
  font-size: 26px;
  font-weight: 900;
  letter-spacing: 2px;
  margin-bottom: 30px;
  border-bottom: 2px solid #111111;
  padding-bottom: 12px;
  color: #111111;
}

.empty-state, .state-msg {
  text-align: center;
  padding: 80px 20px;
  background: #ffffff;
  border: 1.5px solid #e0e0e0;
  border-radius: 6px;
  color: #555555;
  font-weight: 700;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}

.empty-state p {
  font-size: 14px;
  font-weight: 800;
  letter-spacing: 1px;
  margin-bottom: 16px;
}

.btn-shop {
  display: inline-block;
  background: #111111;
  color: #ffffff;
  padding: 12px 28px;
  font-size: 11px;
  font-weight: 900;
  letter-spacing: 1.5px;
  text-decoration: none;
  border-radius: 4px;
  transition: all 0.3s ease;
}

.btn-shop:hover {
  background: #e62129;
  color: #ffffff;
}

.order-card {
  background: #ffffff;
  border: 1.5px solid #e0e0e0;
  padding: 24px;
  margin-bottom: 20px;
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1.5px solid #eeeeee;
  padding-bottom: 14px;
}

.order-number {
  font-size: 14px;
  font-weight: 900;
  letter-spacing: 1px;
  display: block;
  color: #111111;
}

.order-date {
  color: #666666;
  font-size: 11px;
  font-weight: 600;
  margin-top: 2px;
  display: block;
}

.status-badge {
  font-size: 10px;
  padding: 4px 10px;
  font-weight: 800;
  border-radius: 2px;
  letter-spacing: 1px;
}

.status-badge.pending { background: #f59e0b; color: #ffffff; }
.status-badge.paid { background: #10b981; color: #ffffff; }
.status-badge.cancelled { background: #e62129; color: #ffffff; }

.order-items {
  padding: 18px 0;
  border-bottom: 1.5px solid #eeeeee;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.order-item-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  font-weight: 600;
}

.item-name {
  color: #333333;
}

.item-price {
  font-weight: 800;
  color: #111111;
}

.order-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 16px;
}

.total-label {
  font-size: 13px;
  font-weight: 700;
  color: #555555;
}

.total-amount {
  color: #111111;
  font-size: 16px;
  font-weight: 900;
}

.spinner { 
  display: inline-block; 
  width: 24px; 
  height: 24px; 
  border: 3px solid rgba(0, 0, 0, 0.1); 
  border-top-color: #e62129; 
  border-radius: 50%; 
  animation: spin 0.8s linear infinite; 
  margin-bottom: 12px;
}

@keyframes spin { 
  to { transform: rotate(360deg); } 
}

.mt-4 { margin-top: 16px; }
</style>