export default [
  {
    path: '/inventory',
    name: 'InventoryModule',
    redirect: '/inventory/stock',
    meta: {
      title: '進銷存',
      icon: 'box',
      requiresAuth: true,
      permissions: ['inventory.view']
    },
    children: [
      {
        path: 'warehouses',
        name: 'WarehouseList',
        component: () => import('./views/WarehouseList.vue'),
        meta: {
          title: '倉庫列表',
          permissions: ['inventory.view'],
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '進銷存', path: '/inventory' },
            { name: '倉庫列表' }
          ]
        }
      },
      {
        path: 'stock',
        name: 'StockList',
        component: () => import('./views/StockList.vue'),
        meta: {
          title: '庫存列表',
          permissions: ['inventory.view'],
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '進銷存', path: '/inventory' },
            { name: '庫存列表' }
          ]
        }
      },
      {
        path: 'movements',
        name: 'StockMovementList',
        component: () => import('./views/StockMovementList.vue'),
        meta: {
          title: '庫存異動',
          permissions: ['inventory.edit'],
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '進銷存', path: '/inventory' },
            { name: '庫存異動' }
          ]
        }
      },
      {
        path: 'adjustments',
        name: 'StockAdjustmentForm',
        component: () => import('./views/StockAdjustmentForm.vue'),
        meta: {
          title: '庫存調整',
          permissions: ['inventory.edit'],
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '進銷存', path: '/inventory' },
            { name: '庫存調整' }
          ]
        }
      }
    ]
  }
]
