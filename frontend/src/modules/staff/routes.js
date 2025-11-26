/**
 * 員工管理模組路由配置
 */

export default [
  {
    path: '/staff',
    name: 'StaffModule',
    redirect: '/staff/list',
    meta: {
      title: '員工管理',
      icon: 'briefcase',
      requiresAuth: true,
      permissions: ['staff.view']
    },
    children: [
      {
        path: 'list',
        name: 'StaffList',
        component: () => import('./views/StaffList.vue'),
        meta: {
          title: '員工列表',
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '員工管理', path: '/staff' },
            { name: '列表' }
          ]
        }
      },
      {
        path: 'logs',
        name: 'ActivityLogs',
        component: () => import('./views/ActivityLogs.vue'),
        meta: {
          title: '操作紀錄',
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '員工管理', path: '/staff' },
            { name: '操作紀錄' }
          ]
        }
      }
    ]
  }
];