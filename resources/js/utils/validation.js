// Reglas de validación comunes
export const rules = {
    required: (value) => !!value || 'Este campo es requerido',
    email: (value) => {
        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return !value || pattern.test(value) || 'Email inválido';
    },
    phone: (value) => {
        const pattern = /^\d{10}$/;
        return !value || pattern.test(value) || 'Número de teléfono inválido (10 dígitos)';
    },
    minLength: (min) => (value) => {
        return !value || value.length >= min || `Mínimo ${min} caracteres`;
    },
    maxLength: (max) => (value) => {
        return !value || value.length <= max || `Máximo ${max} caracteres`;
    },
    numeric: (value) => {
        return !value || !isNaN(value) || 'Solo se permiten números';
    },
    date: (value) => {
        if (!value) return true;
        const date = new Date(value);
        return !isNaN(date.getTime()) || 'Fecha inválida';
    },
    futureDate: (value) => {
        if (!value) return true;
        const date = new Date(value);
        const now = new Date();
        return date > now || 'La fecha debe ser futura';
    },
    pastDate: (value) => {
        if (!value) return true;
        const date = new Date(value);
        const now = new Date();
        return date < now || 'La fecha debe ser pasada';
    },
    password: (value) => {
        const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;
        return !value || pattern.test(value) || 
            'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número';
    },
    matchPassword: (password) => (value) => {
        return value === password || 'Las contraseñas no coinciden';
    },
    url: (value) => {
        try {
            new URL(value);
            return true;
        } catch {
            return 'URL inválida';
        }
    }
};

// Función para validar un formulario completo
export const validateForm = (form, validations) => {
    const errors = {};
    let isValid = true;

    Object.keys(validations).forEach(field => {
        const value = form[field];
        const fieldRules = validations[field];

        if (Array.isArray(fieldRules)) {
            for (const rule of fieldRules) {
                const result = rule(value);
                if (result !== true) {
                    errors[field] = result;
                    isValid = false;
                    break;
                }
            }
        }
    });

    return { isValid, errors };
};

// Validador en tiempo real para Vue
export const createValidator = (rules) => {
    return {
        data() {
            return {
                errors: {},
                touched: {}
            };
        },
        methods: {
            $validate(field) {
                const value = this[field];
                const fieldRules = rules[field];

                if (!fieldRules) return;

                this.touched[field] = true;

                for (const rule of fieldRules) {
                    const result = rule(value);
                    if (result !== true) {
                        this.$set(this.errors, field, result);
                        return false;
                    }
                }

                this.$delete(this.errors, field);
                return true;
            },
            $validateAll() {
                let isValid = true;
                Object.keys(rules).forEach(field => {
                    if (!this.$validate(field)) {
                        isValid = false;
                    }
                });
                return isValid;
            },
            $touch(field) {
                this.touched[field] = true;
            },
            $reset() {
                this.errors = {};
                this.touched = {};
            }
        }
    };
};

// Ejemplo de uso:
/*
import { rules, createValidator } from '@/utils/validation';

export default {
    mixins: [createValidator({
        email: [rules.required, rules.email],
        password: [rules.required, rules.password],
        phone: [rules.phone]
    })],
    methods: {
        async submit() {
            if (this.$validateAll()) {
                // Proceder con el envío
            }
        }
    }
};
*/
