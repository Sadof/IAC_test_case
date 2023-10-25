import Vue from 'vue';
import App from './pages/product_table.vue';
// import './styles/app.css';

new Vue({
    name: "ProductTable",
    render: h => h(App)
}).$mount("#app");