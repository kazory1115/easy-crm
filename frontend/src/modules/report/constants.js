export const REPORT_KEY_OPTIONS = [
  { value: 'quote_summary', label: '報價摘要' },
  { value: 'order_summary', label: '訂單摘要' },
  { value: 'sales_summary', label: '銷售摘要' },
  { value: 'inventory_snapshot', label: '庫存快照' },
  { value: 'inventory_status', label: '庫存狀態' }
]

export const EXPORT_STATUS_OPTIONS = [
  { value: 'queued', label: '排隊中' },
  { value: 'processing', label: '處理中' },
  { value: 'done', label: '已完成' },
  { value: 'failed', label: '失敗' }
]

export const EXPORT_FORMAT_OPTIONS = [
  { value: 'xlsx', label: 'Excel' },
  { value: 'csv', label: 'CSV' },
  { value: 'pdf', label: 'PDF' }
]

const REPORT_KEY_LABEL_MAP = Object.fromEntries(REPORT_KEY_OPTIONS.map((item) => [item.value, item.label]))
const EXPORT_STATUS_LABEL_MAP = Object.fromEntries(EXPORT_STATUS_OPTIONS.map((item) => [item.value, item.label]))
const EXPORT_FORMAT_LABEL_MAP = Object.fromEntries(EXPORT_FORMAT_OPTIONS.map((item) => [item.value, item.label]))

const EXPORT_STATUS_CLASS_MAP = {
  queued: 'bg-amber-100 text-amber-800',
  processing: 'bg-sky-100 text-sky-800',
  done: 'bg-emerald-100 text-emerald-800',
  failed: 'bg-rose-100 text-rose-800'
}

export function getReportKeyLabel(value) {
  return REPORT_KEY_LABEL_MAP[value] || value || '-'
}

export function getExportStatusLabel(value) {
  return EXPORT_STATUS_LABEL_MAP[value] || value || '-'
}

export function getFormatLabel(value) {
  return EXPORT_FORMAT_LABEL_MAP[value] || value || '-'
}

export function getExportStatusClass(value) {
  return EXPORT_STATUS_CLASS_MAP[value] || 'bg-slate-100 text-slate-700'
}
