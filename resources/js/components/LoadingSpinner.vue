<template>
  <div v-if="loading" class="loading-overlay">
    <div class="loading-content">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <div class="loading-text mt-2">{{ message }}</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LoadingSpinner',
  data() {
    return {
      loading: false,
      message: 'Cargando...'
    }
  },
  methods: {
    show(message = 'Cargando...') {
      this.message = message
      this.loading = true
    },
    hide() {
      this.loading = false
    }
  },
  created() {
    // Registrar métodos globales
    window.$loading = {
      show: this.show,
      hide: this.hide
    }
  }
}
</script>

<style scoped>
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.loading-content {
  text-align: center;
  background-color: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.spinner-border {
  width: 3rem;
  height: 3rem;
}

.loading-text {
  font-size: 1.1rem;
  color: #666;
  margin-top: 1rem;
}

/* Animación de aparición */
.loading-overlay {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Responsive */
@media (max-width: 768px) {
  .loading-content {
    padding: 1.5rem;
  }
  
  .spinner-border {
    width: 2.5rem;
    height: 2.5rem;
  }
  
  .loading-text {
    font-size: 1rem;
  }
}
</style>
