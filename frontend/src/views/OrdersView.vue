<template>
  <div class="container">
    <h2 class="page-title">MY ORDERS</h2>

    <div v-if="loading" class="state-msg">
      <span class="spinner"></span>
      <p>Loading order history...</p>
    </div>

    <div v-else-if="orders.length === 0" class="empty-state">
      <p>You haven't placed any orders yet.</p>
      <router-link to="/" class="btn-bape mt-4">Start Shopping</router-link>
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
            <span>{{ item.product?.name }} x {{ item.quantity }}</span>
            <span>Rp {{ Number(item.price * item.quantity).toLocaleString('id-ID') }}</span>
          </div>
        </div>

        <div class="order-footer">
          <span>Total Amount</span>
          <strong class="total-amount">Rp {{ Number(order.total_amount).toLocaleString('id-ID') }}</strong>
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
.page-title {
  font-size: 28px;
  letter-spacing: 3px;
  margin-bottom: 30px;
  border-bottom: 1px solid var(--border-color);
  padding-bottom: 10px;
}

.empty-state, .state-msg {
  text-align: center;
  padding: 60px 0;
  color: var(--text-muted);
}

.order-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  padding: 20px;
  margin-bottom: 20px;
  border-radius: 4px;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid var(--border-color);
  padding-bottom: 12px;
}

.order-number {
  font-weight: bold;
  letter-spacing: 1px;
  display: block;
}

.order-date {
  color: var(--text-muted);
  font-size: 11px;
}

.status-badge {
  font-size: 10px;
  padding: 4px 10px;
  font-weight: bold;
  border-radius: 2px;
  letter-spacing: 1px;
}

.status-badge.pending { background: #d97706; color: white; }
.status-badge.paid { background: #059669; color: white; }
.status-badge.cancelled { background: #dc2626; color: white; }

.order-items {
  padding: 15px 0;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.order-item-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
}

.order-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 12px;
}

.total-amount {
  color: var(--accent-gold);
  font-size: 16px;
}

.spinner { display: inline-block; width: 20px; height: 20px; border: 2px solid var(--border-color); border-top-color: var(--accent-gold); border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.mt-4 { margin-top: 16px; }
</style>