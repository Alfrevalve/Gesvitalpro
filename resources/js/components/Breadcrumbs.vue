<template>
  <nav aria-label="breadcrumb" class="breadcrumb-wrapper">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">
        <a href="/" class="home-link">
          <i class="fas fa-home"></i>
        </a>
      </li>
      <template v-for="(item, index) in items" :key="index">
        <li class="breadcrumb-item" :class="{ 'active': isLast(index) }">
          <template v-if="isLast(index)">
            {{ item.text }}
          </template>
          <a v-else :href="item.url" @click.prevent="navigate(item)">
            {{ item.text }}
          </a>
        </li>
      </template>
    </ol>
  </nav>
</template>

<script>
export default {
  name: 'Breadcrumbs',
  props: {
    items: {
      type: Array,
      required: true,
      validator: function(items) {
        return items.every(item => item.text && (item.url || item.isActive))
      }
    }
  },
  methods: {
    isLast(index) {
      return index === this.items.length - 1
    },
    navigate(item) {
      if (item.callback && typeof item.callback === 'function') {
        item.callback()
      } else if (item.url) {
        window.location.href = item.url
      }
    }
  }
}
</script>

<style scoped>
.breadcrumb-wrapper {
  background-color: #f8f9fa;
  padding: 0.75rem 1rem;
  border-radius: 0.25rem;
  margin-bottom: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.breadcrumb {
  margin-bottom: 0;
  padding: 0;
  background-color: transparent;
}

.breadcrumb-item {
  display: flex;
  align-items: center;
  color: #6c757d;
}

.breadcrumb-item + .breadcrumb-item::before {
  content: "›";
  font-size: 1.2em;
  line-height: 1;
  color: #6c757d;
  margin: 0 0.5rem;
}

.breadcrumb-item a {
  color: #007bff;
  text-decoration: none;
  transition: color 0.2s ease-in-out;
}

.breadcrumb-item a:hover {
  color: #0056b3;
  text-decoration: underline;
}

.breadcrumb-item.active {
  color: #495057;
  font-weight: 500;
}

.home-link {
  color: #6c757d;
  font-size: 1.1em;
}

.home-link:hover {
  color: #007bff;
}

/* Responsive styles */
@media (max-width: 768px) {
  .breadcrumb-wrapper {
    padding: 0.5rem;
    margin-bottom: 0.5rem;
  }

  .breadcrumb-item {
    font-size: 0.9em;
  }

  /* Ocultar algunos elementos en móviles si hay muchos items */
  .breadcrumb-item:not(:first-child):not(:last-child):not(:nth-last-child(2)) {
    display: none;
  }

  .breadcrumb-item:nth-last-child(2)::before {
    content: "...";
    margin: 0 0.5rem;
  }
}
</style>

<script setup>
// Ejemplo de uso:
/*
<Breadcrumbs :items="[
  { text: 'Inicio', url: '/' },
  { text: 'Pacientes', url: '/pacientes' },
  { text: 'Juan Pérez', isActive: true }
]" />
*/
</script>
