/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import 'bootstrap';
import axios from 'axios';
import _ from 'lodash';
import moment from 'moment';

// Configurar Axios
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

// Configurar CSRF Token
let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Configurar Lodash
window._ = _;

// Configurar Moment.js
window.moment = moment;
moment.locale('es');

// Configurar variables globales
window.APP_URL = document.head.querySelector('meta[name="app-url"]')?.content || '';

// Funciones de utilidad globales
window.formatDate = function(date) {
    return moment(date).format('DD/MM/YYYY');
};

window.formatDateTime = function(date) {
    return moment(date).format('DD/MM/YYYY HH:mm');
};

window.formatCurrency = function(amount) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(amount);
};

// Manejador global de errores de Axios
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response) {
            // Manejar errores de autenticación
            if (error.response.status === 401) {
                window.location.href = '/login';
            }
            
            // Manejar errores de validación
            if (error.response.status === 422) {
                console.error('Validation error:', error.response.data.errors);
            }

            // Manejar otros errores
            if (error.response.status === 500) {
                console.error('Server error:', error.response.data);
            }
        }

        return Promise.reject(error);
    }
);

// Event Bus simple para comunicación entre componentes
window.EventBus = {
    _events: {},
    
    on(event, callback) {
        if (!this._events[event]) {
            this._events[event] = [];
        }
        this._events[event].push(callback);
    },
    
    emit(event, data) {
        if (this._events[event]) {
            this._events[event].forEach(callback => callback(data));
        }
    },
    
    off(event, callback) {
        if (this._events[event]) {
            this._events[event] = this._events[event].filter(cb => cb !== callback);
        }
    }
};

// Configuración de notificaciones Toast
window.showToast = function(message, type = 'info') {
    // Implementar según el sistema de notificaciones que uses
    if (window.toastr) {
        toastr[type](message);
    } else {
        alert(message);
    }
};

// Helpers para formularios
window.resetForm = function(formId) {
    document.getElementById(formId).reset();
};

window.serializeForm = function(formId) {
    return Object.fromEntries(new FormData(document.getElementById(formId)));
};

// Configuración de validación de formularios
window.validateForm = function(formId, rules = {}) {
    const form = document.getElementById(formId);
    let isValid = true;
    
    // Limpiar errores anteriores
    form.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    form.querySelectorAll('.invalid-feedback').forEach(el => {
        el.remove();
    });
    
    // Validar cada campo
    Object.entries(rules).forEach(([field, validations]) => {
        const input = form.querySelector(`[name="${field}"]`);
        if (!input) return;
        
        validations.forEach(validation => {
            const [rule, ...params] = validation.split(':');
            if (!validateField(input, rule, params)) {
                isValid = false;
                showFieldError(input, getErrorMessage(rule, field, params));
            }
        });
    });
    
    return isValid;
};

function validateField(input, rule, params) {
    const value = input.value;
    switch (rule) {
        case 'required':
            return value.trim() !== '';
        case 'min':
            return value.length >= params[0];
        case 'max':
            return value.length <= params[0];
        case 'email':
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        case 'numeric':
            return !isNaN(value);
        default:
            return true;
    }
}

function showFieldError(input, message) {
    input.classList.add('is-invalid');
    const error = document.createElement('div');
    error.className = 'invalid-feedback';
    error.textContent = message;
    input.parentNode.appendChild(error);
}

function getErrorMessage(rule, field, params) {
    const messages = {
        required: `El campo ${field} es obligatorio`,
        min: `El campo ${field} debe tener al menos ${params[0]} caracteres`,
        max: `El campo ${field} no debe tener más de ${params[0]} caracteres`,
        email: `El campo ${field} debe ser un correo electrónico válido`,
        numeric: `El campo ${field} debe ser numérico`
    };
    return messages[rule] || `El campo ${field} es inválido`;
}
