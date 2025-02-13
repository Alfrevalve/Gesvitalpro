<template>
  <div class="toast-container position-fixed top-0 end-0 p-3">
    <div v-for="(toast, index) in toasts" 
         :key="index"
         class="toast show"
         :class="toast.type"
         role="alert">
      <div class="toast-header">
        <i :class="getIcon(toast.type)" class="me-2"></i>
        <strong class="me-auto">{{ toast.title }}</strong>
        <button type="button" 
                class="btn-close" 
                @click="removeToast(index)">
        </button>
      </div>
      <div class="toast-body">
        {{ toast.message }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ToastNotification',
  data() {
    return {
      toasts: []
    }
  },
  methods: {
    getIcon(type) {
      const icons = {
        'success': 'fas fa-check-circle text-success',
        'error': 'fas fa-exclamation-circle text-danger',
        'warning': 'fas fa-exclamation-triangle text-warning',
        'info': 'fas fa-info-circle text-info'
      }
      return icons[type] || icons.info
    },
    showToast(message, type = 'info', title = '', duration = 5000) {
      const toast = {
        message,
        type,
        title: title || this.getDefaultTitle(type),
      }
      this.toasts.push(toast)
      
      setTimeout(() => {
        this.removeToast(this.toasts.indexOf(toast))
      }, duration)
    },
    removeToast(index) {
      this.toasts.splice(index, 1)
    },
    getDefaultTitle(type) {
      const titles = {
        'success': '¡Éxito!',
        'error': 'Error',
        'warning': 'Advertencia',
        'info': 'Información'
      }
      return titles[type] || titles.info
    }
  },
  created() {
    // Registrar el método global para mostrar notificaciones
    window.$toast = this.showToast
  }
}
</script>

<style scoped>
.toast-container {
  z-index: 1050;
}

.toast {
  min-width: 300px;
  max-width: 400px;
  margin-bottom: 1rem;
  opacity: 1;
  border: none;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.toast.success {
  background-color: #d4edda;
  border-left: 4px solid #28a745;
}

.toast.error {
  background-color: #f8d7da;
  border-left: 4px solid #dc3545;
}

.toast.warning {
  background-color: #fff3cd;
  border-left: 4px solid #ffc107;
}

.toast.info {
  background-color: #cce5ff;
  border-left: 4px solid #007bff;
}

.toast-header {
  background-color: transparent;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.btn-close {
  font-size: 0.875rem;
  opacity: 0.75;
}

.btn-close:hover {
  opacity: 1;
}
</style>
