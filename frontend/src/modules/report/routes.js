/**
 * 報表中心模組路由配置
 */

export default [
  {
    path: '/report',
    name: 'ReportModule',
    redirect: '/report/dashboard',
    meta: {
      title: '報表中心',
      icon: 'chart-bar',
      requiresAuth: true,
      permissions: ['report.view']
    },
    children: [
      {
        path: 'dashboard',
        name: 'ReportDashboard',
        component: () => import('./views/ReportDashboard.vue'),
        meta: {
          title: '報表總覽',
          permissions: ['report.view'],
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '報表中心', path: '/report' },
            { name: '報表總覽' }
          ]
        }
      },
      {
        path: 'exports',
        name: 'ReportExportList',
        component: () => import('./views/ReportExportList.vue'),
        meta: {
          title: '匯出紀錄',
          permissions: ['report.view'],
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '報表中心', path: '/report' },
            { name: '匯出紀錄' }
          ]
        }
      }
    ]
  }
];
