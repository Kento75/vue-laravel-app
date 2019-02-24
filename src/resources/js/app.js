import Vue from 'vue'
import router from "./router";
import store from "./store";
import "./bootstrap";

import App from "./App.vue";

// Vueインスタンス生成前に現在のユーザーを取得
const createApp = async() => {
  await store.dispatch("auth/currentUser")

  new Vue({
    el: '#app',
    router,
    store,
    components: { App },
    template: '<App />'
  })
};

createApp();
