import { createStore } from 'vuex';

const store = createStore({
    state() {
        return {
            // Estado inicial
            user: null,
            isAuthenticated: false,
        };
    },
    mutations: {
        setUser(state, user) {
            state.user = user;
            state.isAuthenticated = !!user;
        },
        logout(state) {
            state.user = null;
            state.isAuthenticated = false;
        },
    },
    actions: {
        login({ commit }, user) {
            // Lógica de autenticación (simulada)
            commit('setUser', user);
        },
        logout({ commit }) {
            commit('logout');
        },
    },
});

export default store;
