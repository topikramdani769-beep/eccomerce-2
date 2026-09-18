<template>
  <div class="delarache-app-wrapper">
    <!-- Memanggil Komponen Navbar Terpisah -->
    <Navbar />

    <!-- Tempat Komponen Halaman Dirender -->
    <main class="main-content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import Navbar from './components/Navbar.vue';
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@800;900&family=Oswald:wght@700&display=swap');

.delarache-app-wrapper {
  font-family: 'Montserrat', sans-serif;
  background-color: #080808;
  min-height: 100vh;
  color: #ffffff;
}

.main-content {
  width: 100%;
}
</style>