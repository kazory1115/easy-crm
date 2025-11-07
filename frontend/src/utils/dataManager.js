/**
 * 資料管理工具 - 使用 LocalStorage 管理資料
 * 模擬 JSON 檔案的讀寫功能
 */

import itemsJson from '@/data/items.json';
import templatesJson from '@/data/templates.json';
import quotesJson from '@/data/quotes.json';

const STORAGE_KEYS = {
  ITEMS: 'quote_items',
  TEMPLATES: 'quote_templates',
  QUOTES: 'quote_quotes',
};

/**
 * 初始化 LocalStorage 資料
 * 如果 LocalStorage 沒有資料，則從 JSON 檔案載入預設資料
 */
export function initializeStorage() {
  // 初始化一般項目資料
  if (!localStorage.getItem(STORAGE_KEYS.ITEMS)) {
    localStorage.setItem(STORAGE_KEYS.ITEMS, JSON.stringify(itemsJson));
  }

  // 初始化項目模板資料
  if (!localStorage.getItem(STORAGE_KEYS.TEMPLATES)) {
    localStorage.setItem(STORAGE_KEYS.TEMPLATES, JSON.stringify(templatesJson));
  }

  // 初始化報價單資料
  if (!localStorage.getItem(STORAGE_KEYS.QUOTES)) {
    localStorage.setItem(STORAGE_KEYS.QUOTES, JSON.stringify(quotesJson));
  }
}

// ========== 一般項目管理 ==========

/**
 * 取得所有一般項目
 * @returns {Array} 一般項目陣列
 */
export function getItems() {
  const data = localStorage.getItem(STORAGE_KEYS.ITEMS);
  return data ? JSON.parse(data) : [];
}

/**
 * 新增一般項目
 * @param {Object} item - 項目物件 { name, quantity, unit, price }
 * @returns {Object} 新增的項目（包含自動生成的 id）
 */
export function addItem(item) {
  const items = getItems();
  const newId = items.length > 0 ? Math.max(...items.map((i) => i.id)) + 1 : 1;
  const newItem = {
    id: newId,
    type: 'drop',
    ...item,
  };
  items.push(newItem);
  localStorage.setItem(STORAGE_KEYS.ITEMS, JSON.stringify(items));
  return newItem;
}

/**
 * 更新一般項目
 * @param {Number} id - 項目 ID
 * @param {Object} updates - 要更新的欄位
 * @returns {Boolean} 是否更新成功
 */
export function updateItem(id, updates) {
  const items = getItems();
  const index = items.findIndex((item) => item.id === id);
  if (index === -1) return false;

  items[index] = { ...items[index], ...updates };
  localStorage.setItem(STORAGE_KEYS.ITEMS, JSON.stringify(items));
  return true;
}

/**
 * 刪除一般項目
 * @param {Number} id - 項目 ID
 * @returns {Boolean} 是否刪除成功
 */
export function deleteItem(id) {
  const items = getItems();
  const filteredItems = items.filter((item) => item.id !== id);
  if (filteredItems.length === items.length) return false;

  localStorage.setItem(STORAGE_KEYS.ITEMS, JSON.stringify(filteredItems));
  return true;
}

// ========== 項目模板管理 ==========

/**
 * 取得所有項目模板
 * @returns {Array} 項目模板陣列
 */
export function getTemplates() {
  const data = localStorage.getItem(STORAGE_KEYS.TEMPLATES);
  return data ? JSON.parse(data) : [];
}

/**
 * 新增項目模板
 * @param {Object} template - 模板物件
 * @returns {Object} 新增的模板（包含自動生成的 id）
 */
export function addTemplate(template) {
  const templates = getTemplates();
  const newTemplate = {
    id: crypto.randomUUID(),
    ...template,
  };
  templates.push(newTemplate);
  localStorage.setItem(STORAGE_KEYS.TEMPLATES, JSON.stringify(templates));
  return newTemplate;
}

/**
 * 更新項目模板
 * @param {String} id - 模板 ID
 * @param {Object} updates - 要更新的欄位
 * @returns {Boolean} 是否更新成功
 */
export function updateTemplate(id, updates) {
  const templates = getTemplates();
  const index = templates.findIndex((template) => template.id === id);
  if (index === -1) return false;

  templates[index] = { ...templates[index], ...updates };
  localStorage.setItem(STORAGE_KEYS.TEMPLATES, JSON.stringify(templates));
  return true;
}

/**
 * 刪除項目模板
 * @param {String} id - 模板 ID
 * @returns {Boolean} 是否刪除成功
 */
export function deleteTemplate(id) {
  const templates = getTemplates();
  const filteredTemplates = templates.filter((template) => template.id !== id);
  if (filteredTemplates.length === templates.length) return false;

  localStorage.setItem(STORAGE_KEYS.TEMPLATES, JSON.stringify(filteredTemplates));
  return true;
}

// ========== 報價單管理 ==========

/**
 * 取得所有報價單
 * @returns {Array} 報價單陣列
 */
export function getQuotes() {
  const data = localStorage.getItem(STORAGE_KEYS.QUOTES);
  return data ? JSON.parse(data) : [];
}

/**
 * 取得單一報價單
 * @param {Number} id - 報價單 ID
 * @returns {Object|null} 報價單物件或 null
 */
export function getQuoteById(id) {
  const quotes = getQuotes();
  return quotes.find((quote) => quote.id === id) || null;
}

/**
 * 儲存報價單
 * @param {Object} quote - 報價單物件
 * @returns {Object} 儲存的報價單（包含自動生成的 id 和時間戳記）
 */
export function saveQuote(quote) {
  const quotes = getQuotes();
  const newId = quotes.length > 0 ? Math.max(...quotes.map((q) => q.id)) + 1 : 1;
  const newQuote = {
    id: newId,
    ...quote,
    createdAt: new Date().toISOString(),
    updatedAt: new Date().toISOString(),
  };
  quotes.push(newQuote);
  localStorage.setItem(STORAGE_KEYS.QUOTES, JSON.stringify(quotes));
  return newQuote;
}

/**
 * 更新報價單
 * @param {Number} id - 報價單 ID
 * @param {Object} updates - 要更新的資料
 * @returns {Boolean} 是否更新成功
 */
export function updateQuote(id, updates) {
  const quotes = getQuotes();
  const index = quotes.findIndex((quote) => quote.id === id);
  if (index === -1) return false;

  quotes[index] = {
    ...quotes[index],
    ...updates,
    updatedAt: new Date().toISOString(),
  };
  localStorage.setItem(STORAGE_KEYS.QUOTES, JSON.stringify(quotes));
  return true;
}

/**
 * 刪除報價單
 * @param {Number} id - 報價單 ID
 * @returns {Boolean} 是否刪除成功
 */
export function deleteQuote(id) {
  const quotes = getQuotes();
  const filteredQuotes = quotes.filter((quote) => quote.id !== id);
  if (filteredQuotes.length === quotes.length) return false;

  localStorage.setItem(STORAGE_KEYS.QUOTES, JSON.stringify(filteredQuotes));
  return true;
}

// ========== 工具函式 ==========

/**
 * 重置所有資料為初始狀態
 */
export function resetAllData() {
  localStorage.setItem(STORAGE_KEYS.ITEMS, JSON.stringify(itemsJson));
  localStorage.setItem(STORAGE_KEYS.TEMPLATES, JSON.stringify(templatesJson));
  localStorage.setItem(STORAGE_KEYS.QUOTES, JSON.stringify(quotesJson));
}

/**
 * 匯出所有資料（用於備份）
 * @returns {Object} 包含所有資料的物件
 */
export function exportAllData() {
  return {
    items: getItems(),
    templates: getTemplates(),
    quotes: getQuotes(),
    exportedAt: new Date().toISOString(),
  };
}

/**
 * 匯入資料（用於還原備份）
 * @param {Object} data - 要匯入的資料物件
 * @returns {Boolean} 是否匯入成功
 */
export function importAllData(data) {
  try {
    if (data.items) {
      localStorage.setItem(STORAGE_KEYS.ITEMS, JSON.stringify(data.items));
    }
    if (data.templates) {
      localStorage.setItem(STORAGE_KEYS.TEMPLATES, JSON.stringify(data.templates));
    }
    if (data.quotes) {
      localStorage.setItem(STORAGE_KEYS.QUOTES, JSON.stringify(data.quotes));
    }
    return true;
  } catch (error) {
    console.error('匯入資料失敗:', error);
    return false;
  }
}
