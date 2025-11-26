/**
 * 報價單模組路由配置
 */

export default [
  {
    path: '/quote',
    name: 'QuoteModule',
    redirect: '/quote/list',
    meta: {
      title: '報價單',
      icon: 'file-lines',
      requiresAuth: false, // 暫時不需要認證（後續整合時改為 true）
      permissions: ['quote.view']
    },
    children: [
      {
        path: 'list',
        name: 'QuoteList',
        component: () => import('./views/QuoteList.vue'),
        meta: {
          title: '報價單列表',
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '報價單', path: '/quote' },
            { name: '列表' }
          ]
        }
      },
      {
        path: 'create',
        name: 'QuoteCreate',
        component: () => import('./views/QuoteCreate.vue'),
        meta: {
          title: '建立報價單',
          permissions: ['quote.create'],
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '報價單', path: '/quote' },
            { name: '建立' }
          ]
        }
      },
      {
        path: 'edit/:id',
        name: 'QuoteEdit',
        component: () => import('./views/QuoteEdit.vue'),
        meta: {
          title: '編輯報價單',
          permissions: ['quote.edit'],
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '報價單', path: '/quote' },
            { name: '編輯' }
          ]
        }
      },
      {
        path: 'detail/:id',
        name: 'QuoteDetail',
        component: () => import('./views/QuoteDetail.vue'),
        meta: {
          title: '報價單詳情',
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '報價單', path: '/quote' },
            { name: '詳情' }
          ]
        }
      },
      {
        path: 'templates',
        name: 'TemplateManage',
        component: () => import('./views/TemplateManage.vue'),
        meta: {
          title: '範本管理',
          permissions: ['quote.template.manage'],
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '報價單', path: '/quote' },
            { name: '範本管理' }
          ]
        }
      }
    ]
  }
]
