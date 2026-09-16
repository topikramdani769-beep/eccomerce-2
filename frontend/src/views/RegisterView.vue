<template>
  <div class="delarache-auth-page">
    <!-- VIDEO BACKGROUND -->
    <video class="bg-video" autoplay loop muted playsinline>
      <source src="/hero-bg.mp4" type="video/mp4" />
      Browser Anda tidak mendukung video HTML5.
    </video>

    <!-- Overlay Gelap -->
    <div class="video-overlay"></div>

    <!-- KARTU FORM REGISTER -->
    <div class="auth-card">
      <div class="brand-header">
        <span class="tag-red">MEMBER ACCESS</span>
        <h2 class="main-title">CREATE ACCOUNT</h2>
      </div>

      <!-- Notifikasi Berhasil -->
      <Transition name="fade">
        <div v-if="successMsg" class="alert alert-success">
          <span class="icon">✓</span>
          <div class="alert-content">
            <strong>REGISTRATION SUCCESSFUL!</strong>
            <p>Redirecting to store...</p>
          </div>
        </div>
      </Transition>

      <!-- Notifikasi Gagal -->
      <Transition name="fade">
        <div v-if="errorMsg" class="alert alert-error">
          <span class="icon">✕</span>
          <div class="alert-content">
            <strong>REGISTRATION FAILED</strong>
            <p>{{ errorMsg }}</p>
          </div>
        </div>
      </Transition>

      <form @submit.prevent="handleRegister" class="auth-form">
        <div class="form-group">
          <label>FULL NAME</label>
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
          <label>EMAIL ADDRESS</label>
          <input 
            type="email" 
            v-model="form.email" 
            class="form-control" 
            placeholder="user@example.com"
            :disabled="isLoading"
            required 
          />
        </div>

        <div class="form-group">
          <label>PASSWORD</label>
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
          <label>CONFIRM PASSWORD</label>
          <input 
            type="password" 
            v-model="form.password_confirmation" 
            class="form-control" 
            placeholder="••••••••"
            :disabled="isLoading"
            required 
          />
        </div>

        <button type="submit" class="btn-submit" :disabled="isLoading">
          <span v-if="!isLoading">JOIN THE CLUB</span>
          <span v-else class="loader-container">
            <span class="spinner"></span>
            PROCESSING...
          </span>
        </button>

        <div class="auth-footer">
          <span>ALREADY HAVE AN ACCOUNT?</span>
          <router-link to="/login" class="link-red">SIGN IN</router-link>
        </div>
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
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800;900&display=swap');

.delarache-auth-page {
  font-family: 'Montserrat', sans-serif;
  position: relative;
  min-height: 100vh;
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 30px 20px;
  overflow: hidden;
}

/* STYLE UNTUK VIDEO BACKGROUND FULLSCREEN */
.bg-video {
  position: absolute;
  top: 50%;
  left: 50%;
  min-width: 100%;
  min-height: 100%;
  width: auto;
  height: auto;
  z-index: 1;
  transform: translate(-50%, -50%);
  object-fit: cover;
}

/* LAPISAN OVERLAY HITAM TRANSPARAN */
.video-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 2;
}

.auth-card {
  position: relative;
  z-index: 3;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: 1.5px solid #e0e0e0;
  border-radius: 6px;
  padding: 40px 32px;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  animation: fadeIn 0.4s ease-out;
}

.brand-header {
  text-align: left;
  margin-bottom: 28px;
}

.tag-red {
  background-color: #e62129;
  color: #ffffff;
  font-size: 10px;
  font-weight: 800;
  padding: 4px 8px;
  letter-spacing: 1.5px;
  display: inline-block;
  margin-bottom: 8px;
  border-radius: 2px;
}

.main-title {
  font-size: 26px;
  font-weight: 900;
  letter-spacing: 2px;
  color: #111111;
  margin: 0;
}

/* Alert Notification Banner */
.alert {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 4px;
  margin-bottom: 20px;
}

.alert-success {
  background-color: #eefbe8;
  border: 1px solid #4ade80;
  color: #166534;
}

.alert-error {
  background-color: #fff0f0;
  border: 1px solid #ff4d4d;
  color: #d61c24;
}

.alert .icon {
  font-weight: bold;
  font-size: 14px;
}

.alert-content strong {
  display: block;
  font-size: 10px;
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
  gap: 8px;
}

.form-group label {
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 1.5px;
  color: #555555;
}

.form-control {
  width: 100%;
  padding: 12px 14px;
  background: #f8f9fa;
  border: 1.5px solid #dcdcdc;
  border-radius: 4px;
  font-family: inherit;
  font-size: 13px;
  font-weight: 600;
  color: #111111;
  box-sizing: border-box;
  transition: all 0.2s ease;
}

.form-control:focus {
  outline: none;
  background: #ffffff;
  border-color: #e62129;
  box-shadow: 0 0 0 3px rgba(230, 33, 41, 0.1);
}

.form-control:disabled {
  background: #e9ecef;
  opacity: 0.7;
  cursor: not-allowed;
}

/* Custom Button & Loading Spinner */
.btn-submit {
  width: 100%;
  height: 48px;
  background: #111111;
  color: #ffffff;
  border: none;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 900;
  letter-spacing: 1.5px;
  cursor: pointer;
  margin-top: 6px;
  transition: all 0.3s ease;
  display: flex;
  justify-content: center;
  align-items: center;
}

.btn-submit:hover:not(:disabled) {
  background: #e62129;
  color: #ffffff;
}

.btn-submit:disabled {
  background: #cccccc;
  color: #777777;
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
  font-weight: 800;
  letter-spacing: 1px;
  color: #666666;
  display: flex;
  gap: 6px;
  justify-content: center;
}

.link-red {
  color: #111111;
  font-weight: 800;
  text-decoration: underline;
  transition: color 0.2s;
}

.link-red:hover {
  color: #e62129;
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