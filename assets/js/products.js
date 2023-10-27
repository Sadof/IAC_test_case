import Vue from 'vue';
import App from './pages/product_table.vue';

import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

Vue.use(BootstrapVue)
Vue.use(IconsPlugin)

new Vue({
    name: "ProductTable",
    render: h => h(App)
}).$mount("#app");