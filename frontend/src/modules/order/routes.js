export default [
  {
    path: '/order',
    name: 'OrderModule',
    redirect: '/order/list',
    meta: {
      title: '訂單管理',
      icon: 'clipboard-list',
      requiresAuth: true,
      permissions: ['order.view']
    },
    children: [
      {
        path: 'list',
        name: 'OrderList',
        component: () => import('./views/OrderList.vue'),
        meta: {
          title: '訂單列表',
          permissions: ['order.view'],
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '訂單管理', path: '/order' },
            { name: '列表' }
          ]
        }
      },
      {
        path: 'create',
        name: 'OrderCreate',
        component: () => import('./views/OrderCreate.vue'),
        meta: {
          title: '建立訂單',
          permissions: ['order.create'],
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '訂單管理', path: '/order' },
            { name: '建立' }
          ]
        }
      },
      {
        path: 'detail/:id',
        name: 'OrderDetail',
        component: () => import('./views/OrderDetail.vue'),
        meta: {
          title: '訂單詳情',
          permissions: ['order.view'],
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '訂單管理', path: '/order' },
            { name: '詳情' }
          ]
        }
      },
      {
        path: 'edit/:id',
        name: 'OrderEdit',
        component: () => import('./views/OrderEdit.vue'),
        meta: {
          title: '編輯訂單',
          permissions: ['order.edit'],
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '訂單管理', path: '/order' },
            { name: '編輯' }
          ]
        }
      }
    ]
  }
]
