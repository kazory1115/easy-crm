/**
 * 進銷存模組入口
 */

export { default as inventoryRoutes } from './routes';

export const moduleInfo = {
  id: 'inventory',
  name: '進銷存',
  version: '0.0.1',
  description: '商品管理、庫存追蹤、進銷記錄'
};