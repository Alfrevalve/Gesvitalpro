import { createApp, defineAsyncComponent } from 'vue';
import App from './App.vue';
import store from './store'; // Importar el store de Vuex

const app = createApp(App);

// Lazy Loading de componentes
app.component('UserPanel', defineAsyncComponent(() => import('./components/user-panel.vue')));
app.component('CirugiaIndex', defineAsyncComponent(() => import('./cirugias/index.vue')));
app.component('PacienteIndex', defineAsyncComponent(() => import('./pacientes/index.vue')));
app.component('UserManagementIndex', defineAsyncComponent(() => import('./user_management/index.vue')));
app.use(store); // Usar el store de Vuex
app.mount('#app');
