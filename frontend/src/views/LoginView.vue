<template>
  <div class="bape-auth-page">
    <div class="auth-card">
      <div class="brand-header">
        <div class="brand-badge">🦍</div>
        <span class="sub-heading">MEMBER ACCESS</span>
        <h2 class="main-title">SIGN IN</h2>
      </div>

      <!-- Alert Banner Gagal -->
      <Transition name="fade">
        <div v-if="errorMsg" class="alert alert-error">
          <span class="icon">✕</span>
          <div class="alert-content">
            <strong>LOGIN FAILED</strong>
            <p>{{ errorMsg }}</p>
          </div>
        </div>
      </Transition>

      <form @submit.prevent="handleLogin" class="auth-form">
        <div class="form-group">
          <label>EMAIL ADDRESS</label>
          <input 
            type="email" 
            v-model="email" 
            class="form-control" 
            placeholder="user@gmail.com"
            :disabled="isLoading"
            required 
          />
        </div>

        <div class="form-group">
          <label>PASSWORD</label>
          <input 
            type="password" 
            v-model="password" 
            class="form-control" 
            placeholder="••••••••"
            :disabled="isLoading"
            required 
          />
        </div>

        <button type="submit" class="btn-submit" :disabled="isLoading">
          <span v-if="!isLoading">SIGN IN</span>
          <span v-else class="loader-container">
            <span class="spinner"></span>
            AUTHENTICATING...
          </span>
        </button>

        <div class="auth-footer">
          <span>DON'T HAVE AN ACCOUNT?</span>
          <router-link to="/register" class="link-gold">CREATE ONE</router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const email = ref('');
const password = ref('');
const isLoading = ref(false);
const errorMsg = ref('');

const authStore = useAuthStore();
const router = useRouter();

const handleLogin = async () => {
  isLoading.value = true;
  errorMsg.value = '';

  try {
    await authStore.login({
      email: email.value,
      password: password.value
    });
    router.push('/');
  } catch (error) {
    errorMsg.value = error.response?.data?.message || 'Email atau password salah. Coba lagi.';
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');

.bape-auth-page {
  font-family: 'Plus Jakarta Sans', sans-serif;
  background-color: #e2e1dc;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
  color: #111111;
}

.auth-card {
  background: #f4f3ef;
  border-radius: 16px;
  padding: 40px 32px;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06);
  animation: fadeIn 0.4s ease-out;
}

.brand-header {
  text-align: center;
  margin-bottom: 28px;
}

.brand-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 42px;
  height: 42px;
  background: #2b2a28;
  border-radius: 50%;
  font-size: 20px;
  margin-bottom: 12px;
}

.sub-heading {
  display: block;
  font-size: 10px;
  letter-spacing: 3px;
  font-weight: 800;
  color: #666;
}

.main-title {
  font-size: 26px;
  font-weight: 900;
  letter-spacing: 2px;
  margin-top: 4px;
}

/* Alert Notification Banner */
.alert {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.alert-error {
  background-color: rgba(239, 68, 68, 0.1);
  border: 1px solid #ef4444;
  color: #dc2626;
}

.alert .icon {
  font-weight: bold;
  font-size: 14px;
}

.alert-content strong {
  display: block;
  font-size: 11px;
  letter-spacing: 1px;
}

.alert-content p {
  margin: 2px 0 0 0;
  font-size: 11px;
  opacity: 0.9;
}

/* Form Controls */
.auth-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 1.5px;
  color: #555;
}

.form-control {
  width: 100%;
  padding: 12px 14px;
  background: #e7e6e0;
  border: 1px solid transparent;
  border-radius: 8px;
  font-family: inherit;
  font-size: 13px;
  font-weight: 600;
  color: #111;
  box-sizing: border-box;
  transition: all 0.2s ease;
}

.form-control:focus {
  outline: none;
  background: #ffffff;
  border-color: #c8b282;
  box-shadow: 0 0 0 3px rgba(200, 178, 130, 0.2);
}

.form-control:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Custom Button & Loading Spinner */
.btn-submit {
  width: 100%;
  height: 46px;
  background: #c8b282;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 1.5px;
  cursor: pointer;
  margin-top: 6px;
  transition: background 0.2s ease;
  display: flex;
  justify-content: center;
  align-items: center;
}

.btn-submit:hover:not(:disabled) {
  background: #b59f6f;
}

.btn-submit:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.loader-container {
  display: flex;
  align-items: center;
  gap: 8px;
}

.spinner {
  width: 14px;
  height: 14px;
  border: 2px solid #ffffff;
  border-top: 2px solid transparent;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

.auth-footer {
  margin-top: 10px;
  text-align: center;
  font-size: 11px;
  font-weight: 700;
  color: #666;
  display: flex;
  gap: 6px;
  justify-content: center;
}

.link-gold {
  color: #111;
  font-weight: 800;
  text-decoration: underline;
}

.link-gold:hover {
  color: #c8b282;
}

/* Animations */
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>