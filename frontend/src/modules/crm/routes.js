export default [
  {
    path: '/crm',
    name: 'CrmModule',
    redirect: '/crm/customers',
    meta: {
      title: '客戶管理',
      icon: 'users',
      requiresAuth: true,
      permissions: ['crm.view']
    },
    children: [
      {
        path: 'customers',
        name: 'CustomerList',
        component: () => import('./views/CustomerList.vue'),
        meta: {
          title: '客戶列表',
          permissions: ['crm.view'],
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '客戶管理', path: '/crm' },
            { name: '客戶列表' }
          ]
        }
      },
      {
        path: 'customers/create',
        name: 'CustomerCreate',
        component: () => import('./views/CustomerForm.vue'),
        meta: {
          title: '新增客戶',
          permissions: ['crm.create'],
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '客戶管理', path: '/crm' },
            { name: '新增客戶' }
          ]
        }
      },
      {
        path: 'customers/:id',
        name: 'CustomerDetail',
        component: () => import('./views/CustomerDetail.vue'),
        meta: {
          title: '客戶詳情',
          permissions: ['crm.view'],
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '客戶管理', path: '/crm' },
            { name: '客戶詳情' }
          ]
        }
      },
      {
        path: 'customers/:id/edit',
        name: 'CustomerEdit',
        component: () => import('./views/CustomerForm.vue'),
        meta: {
          title: '編輯客戶',
          permissions: ['crm.edit'],
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '客戶管理', path: '/crm' },
            { name: '編輯客戶' }
          ]
        }
      },
      {
        path: 'opportunities',
        name: 'OpportunityList',
        component: () => import('./views/OpportunityList.vue'),
        meta: {
          title: '商機列表',
          permissions: ['crm.view'],
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '客戶管理', path: '/crm' },
            { name: '商機列表' }
          ]
        }
      }
    ]
  }
]
