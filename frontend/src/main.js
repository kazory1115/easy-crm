import { createApp } from 'vue';
import './style.css';
import App from './App.vue';
import router from './router';
import { initializeStorage } from './utils/dataManager';

// 初始化 LocalStorage 資料
initializeStorage();

const app = createApp(App);

/**
 * 載入路由
 */
app.use(router);

// 載入主檔案
app.mount('#app');
