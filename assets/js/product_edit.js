import Vue from 'vue';
import App from './pages/product_edit.vue';
// import './styles/app.css';

new Vue({
    name: "ProductEdit",
    render: h => h(App)
}).$mount("#app");