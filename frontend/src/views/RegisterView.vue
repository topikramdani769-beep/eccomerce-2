<template>
  <div class="container auth-wrapper">
    <div class="auth-card">
      <h2>CREATE ACCOUNT</h2>

      <!-- Notifikasi Berhasil -->
      <div v-if="successMsg" class="alert alert-success">
        <span class="icon">✓</span>
        <div>
          <strong>REGISTRATION SUCCESSFUL!</strong>
          <p>Redirecting to store...</p>
        </div>
      </div>

      <!-- Notifikasi Gagal -->
      <div v-if="errorMsg" class="alert alert-error">
        <span class="icon">✕</span>
        <div>
          <strong>REGISTRATION FAILED</strong>
          <p>{{ errorMsg }}</p>
        </div>
      </div>

      <form @submit.prevent="handleRegister">
        <div class="form-group">
          <label>Full Name</label>
          <input 
            type="text" 
            v-model="form.name" 
            class="form-control" 
            placeholder="Ape Head"
            :disabled="isLoading"
            required 
          />
        </div>
        <div class="form-group">
          <label>Email Address</label>
          <input 
            type="email" 
            v-model="form.email" 
            class="form-control" 
            placeholder="bape@streetwear.com"
            :disabled="isLoading"
            required 
          />
        </div>
        <div class="form-group">
          <label>Password</label>
          <input 
            type="password" 
            v-model="form.password" 
            class="form-control" 
            placeholder="••••••••"
            :disabled="isLoading"
            required 
          />
        </div>
        <div class="form-group">
          <label>Confirm Password</label>
          <input 
            type="password" 
            v-model="form.password_confirmation" 
            class="form-control" 
            placeholder="••••••••"
            :disabled="isLoading"
            required 
          />
        </div>

        <button type="submit" class="btn-bape w-full btn-submit" :disabled="isLoading">
          <span v-if="!isLoading">JOIN THE CLUB</span>
          <span v-else class="loader-container">
            <span class="spinner"></span>
            PROCESSING...
          </span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const isLoading = ref(false);
const successMsg = ref(false);
const errorMsg = ref('');

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});

const handleRegister = async () => {
  isLoading.value = true;
  errorMsg.value = '';
  successMsg.value = false;

  try {
    await authStore.register(form);
    successMsg.value = true;
    
    // Memberikan jeda animasi 1.5 detik sebelum redirect ke Home
    setTimeout(() => {
      router.push('/');
    }, 1500);
  } catch (error) {
    const serverErrors = error.response?.data?.errors;
    if (serverErrors) {
      errorMsg.value = Object.values(serverErrors).flat().join(', ');
    } else {
      errorMsg.value = error.response?.data?.message || 'Registrasi gagal, coba lagi.';
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
.auth-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 70vh;
}

.auth-card {
  background-color: var(--bg-card);
  border: 1px solid var(--border-color);
  padding: 35px;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
  animation: fadeIn 0.4s ease-out;
}

.auth-card h2 {
  text-align: center;
  margin-bottom: 25px;
  letter-spacing: 3px;
  font-size: 20px;
}

/* Alert Notification Banner */
.alert {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 4px;
  margin-bottom: 20px;
  font-size: 13px;
  animation: slideDown 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.alert-success {
  background-color: rgba(205, 168, 81, 0.15);
  border: 1px solid var(--accent-gold);
  color: var(--accent-gold);
}

.alert-error {
  background-color: rgba(255, 77, 77, 0.15);
  border: 1px solid #ff4d4d;
  color: #ff4d4d;
}

.alert .icon {
  font-weight: bold;
  font-size: 16px;
}

.alert p {
  margin: 0;
  font-size: 11px;
  opacity: 0.8;
}

.w-full {
  width: 100%;
  padding: 12px;
  margin-top: 15px;
}

/* Custom Button & Loading Spinner */
.btn-submit {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 45px;
}

.loader-container {
  display: flex;
  align-items: center;
  gap: 10px;
}

.spinner {
  width: 14px;
  height: 14px;
  border: 2px solid #000;
  border-top: 2px solid transparent;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

/* Keyframe Animations */
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes slideDown {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<style scoped>
.auth-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 60vh;
}
.auth-card {
  background-color: var(--bg-card);
  border: 1px solid var(--border-color);
  padding: 30px;
  width: 100%;
  max-width: 400px;
}
.auth-card h2 {
  text-align: center;
  margin-bottom: 25px;
  letter-spacing: 2px;
}
.w-full {
  width: 100%;
  padding: 12px;
  margin-top: 10px;
}
</style>