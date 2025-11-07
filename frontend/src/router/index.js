// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router';

// 匯入你要使用的元件
import Home from '../views/Home.vue';
import Add from '../views/Add.vue';
import History from '../views/History.vue';
import SidebarLayout from '../layouts/SidebarLayout.vue';

const routes = [
  {
    path: '/',
    component: SidebarLayout,
    children: [
      {
        path: '',
        component: Home,
        name: 'Home',
      },
      {
        path: '/add',
        component: Add,
        name: 'Add',
      },
      {
        path: '/history',
        component: History,
        name: 'History',
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
