<style scoped>
table {
  font-size: 0.9em; /* 或 90%、12px、或 1vw 都可以 */
}
.signature {
  margin-top: 50px;
  display: flex;
  /* justify-content: space-between; */
  border: 1px solid black;
}
.alignCenter {
  text-align: center;
}
</style>

<template>
  <div class="container">
    <div class="container-fluid py-4">
      <!-- Header with controls -->
      <div class="row mb-4">
        <div class="col-md-6">
          <h1 class="h3 mb-0">報價管理系統</h1>
        </div>
        <div class="col-md-6 text-md-end">
          <div class="btn-group">
            <button class="btn btn-primary">
              <i class="bi bi-printer me-1"></i> 列印
            </button>
            <button class="btn btn-success">
              <i class="bi bi-file-earmark-pdf me-1"></i> 匯出 PDF
            </button>
            <button class="btn btn-info" @click="exportDoc">
              <i class="bi bi-file-earmark-word me-1"></i> 匯出 Word
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Quotation card -->
    <div class="card shadow-sm mb-4">
      <!-- Quotation header -->
      <div class="card-header bg-light">
        <div class="text-center mb-4">
          <h2 class="card-title mb-0">報價單</h2>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="mb-3 row">
              <label class="col-sm-3 col-form-label">客戶名稱:</label>
              <div class="col-sm-9">
                <input
                  type="text"
                  class="form-control"
                  v-model="customerName"
                />
              </div>
            </div>
            <div class="mb-3 row">
              <label class="col-sm-3 col-form-label">聯絡電話:</label>
              <div class="col-sm-9">
                <input
                  type="text"
                  class="form-control"
                  placeholder="請輸入聯絡電話"
                />
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="mb-3 row">
              <label class="col-sm-3 col-form-label">報價單號:</label>
              <div class="col-sm-9">
                <input
                  type="text"
                  class="form-control"
                  v-model="quotationNumber"
                />
              </div>
            </div>
            <div class="mb-3 row">
              <label class="col-sm-3 col-form-label">報價日期:</label>
              <div class="col-sm-9">
                <input type="date" class="form-control" v-model="date" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="card-body">
        <div class="dropdown mb-3 text-end">
          <button
            class="btn btn-primary dropdown-toggle"
            type="button"
            id="dropdownMenuButton1"
            data-bs-toggle="dropdown"
            aria-expanded="false"
          >
            <i class="bi bi-plus-circle me-1"></i> 新增項目
          </button>
          <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
            <li>
              <button
                class="dropdown-item"
                type="button"
                @click.prevent="addRow('drop')"
              >
                一般項目
              </button>
            </li>
            <li>
              <button
                class="dropdown-item"
                type="button"
                @click.prevent="addRow('host')"
              >
                主機資料
              </button>
            </li>
            <li>
              <button
                class="dropdown-item"
                type="button"
                @click.prevent="addRow('input')"
              >
                填寫項目
              </button>
            </li>
          </ul>
        </div>

        <div class="table-responsive" style="overflow-x: auto; width: 100%">
          <table
            class="table table-bordered table-hover"
            style="min-width: 900px"
          >
            <thead class="table-light">
              <tr>
                <th style="width: 60px">項次</th>
                <th>品名規格</th>
                <th style="width: 140px">數量</th>
                <th style="width: 120px">單位</th>
                <th style="width: 140px">單價</th>
                <th style="width: 120px">複價</th>
                <th style="width: 80px">功能</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in items" :key="index">
                <td class="pt-3">
                  {{ item.id }}
                </td>

                <td>
                  <input
                    v-if="item.type === 'input'"
                    type="text"
                    v-model="item.name"
                    class="form-control"
                    style="width: 100%"
                    placeholder="請輸入品名規格"
                  />
                  <select
                    v-if="item.type === 'drop'"
                    class="form-select"
                    @change="selectRowData(item.id, $event, 'drop')"
                  >
                    <option value="" disabled selected>新增項目</option>
                    <option
                      v-for="dropItem in itemDatas"
                      :key="dropItem.id"
                      :value="dropItem.id"
                    >
                      {{ dropItem.name }}
                    </option>
                  </select>
                  <select
                    v-if="item.type === 'host'"
                    class="form-select"
                    @change="selectRowData(item.id, $event, 'host')"
                  >
                    <option value="" disabled selected>新增項目</option>
                    <option
                      v-for="hostItem in hostDatas"
                      :key="hostItem.id"
                      :value="hostItem.id"
                    >
                      {{ hostItem.name }}
                    </option>
                  </select>

                  <div
                    class="text-start"
                    style="padding-left: 12px"
                    v-html="hostShowText[item.id]"
                    v-if="item.type === 'host'"
                  ></div>
                </td>
                <td>
                  <input
                    type="number"
                    v-model="item.quantity"
                    class="form-control"
                    style="width: 100%"
                    placeholder="請輸入數量"
                    list="quantityList"
                  />
                  <datalist id="quantityList">
                    <option v-for="item in 10" :key="item" :value="item">
                      {{ item }}
                    </option>
                  </datalist>
                </td>
                <td>
                  <input
                    type="text"
                    v-model="item.unit"
                    class="form-control"
                    style="width: 100%"
                    placeholder="輸入單位"
                  />
                </td>
                <td>
                  <input
                    type="number"
                    v-model="item.price"
                    class="form-control"
                    style="width: 100%"
                    placeholder="請輸入單價"
                  />
                </td>
                <td class="text-end align-middle">
                  {{ (item.quantity * item.price).toLocaleString() }}
                </td>
                <td class="text-center">
                  <button
                    class="btn btn-danger btn-sm"
                    @click="delRow(item.id)"
                    :disabled="items.length <= 1"
                  >
                    <i class="bi bi-dash-circle"></i>
                  </button>
                </td>
              </tr>
              <tr></tr>
            </tbody>
          </table>
        </div>

        <!-- Summary -->
        <div class="row mt-4">
          <div class="col-md-7">
            <div class="card h-100">
              <div class="card-header bg-light">
                <h5 class="mb-0">備註</h5>
              </div>
              <div class="card-body">
                <textarea
                  class="form-control"
                  rows="3"
                  placeholder="請輸入備註事項..."
                ></textarea>
              </div>
            </div>
          </div>
          <div class="col-md-5">
            <div class="card h-100">
              <div class="card-header bg-light">
                <h5 class="mb-0">金額計算</h5>
              </div>
              <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                  <span>小計:</span>
                  <span>{{ total.toLocaleString() }}</span>
                </div>

                <hr />
                <div class="d-flex justify-content-between fw-bold">
                  <span>總金額:</span>
                  <span class="text-primary"
                    >{{ total.toLocaleString() }} 元</span
                  >
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Footer -->
      <div class="card-footer bg-light">
        <div class="row">
          <div class="col-md-6 mb-3">
            <h5>製表人簽名</h5>
            <div class="border p-3 bg-white" style="height: 100px"></div>
          </div>
          <div class="col-md-6 mb-3">
            <h5>客戶人簽名</h5>
            <div class="border p-3 bg-white" style="height: 100px"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const customerName = ref('王小明');
const date = ref(new Date().toISOString().slice(0, 10)); // 預設當日 YYYY-MM-DD
const quotationNumber = ref();
const hostShowText = ref([]);

// 項目資料
const items = ref([
  // { id: 1, type: 'host', name: null, quantity: null, unit: null, price: null }, //主機資料
  // { id: 2, type: 'drop', name: null, quantity: null, unit: null, price: null }, // DB資料
  // { id: 3, type: 'input', name: null, quantity: null, unit: null, price: null }, // input資料
]);

const itemDatas = [
  { id: 1, type: 'drop', name: '選單1', quantity: 1, unit: 'kg', price: 100 },
  { id: 2, type: 'drop', name: '選單2', quantity: 2, unit: 'kg', price: 200 },
  { id: 3, type: 'drop', name: '選單3', quantity: 3, unit: 'kg', price: 300 },
];

//主機資料
const hostDatas = [
  {
    id: 1,
    name: '主機A馬力4.5kw',
    main_hoist_power: '4.5kw', //主吊馬力
    hoisting_speed: '6m/min', //吊速
    trolley_motor_power: '0.75kw', //橫行馬力
    trolley_speed: '25m/min', //橫速
    long_travel_motor_power: '1.5kw', // 縱行馬力
    long_travel_speed: '40m/min', // 縱速
    unit: '式',
    quantity: 1,
    price: 100,
  },
  {
    id: 2,
    name: '主機B馬力4.9kw',
    main_hoist_power: '4.9kw', //主吊馬力
    hoisting_speed: '6m/min', //吊速
    trolley_motor_power: '0.75kw', //橫行馬力
    trolley_speed: '13/4m/min', //橫速
    long_travel_motor_power: '1.5kw', // 縱行馬力
    long_travel_speed: '40m/min', // 縱速
    unit: '式',
    quantity: 1,
    price: 100,
  },
];

const total = computed(() =>
  items.value.reduce((sum, item) => sum + item.quantity * item.price, 0)
);

function addRow(type) {
  // 取得目前最大 id，自動遞增
  const newId = Math.max(...items.value.map((item) => item.id)) + 1;
  items.value.push({
    id: newId,
    type: type,
    name: null,
    quantity: null,
    unit: null,
    price: null,
    total: 0,
  });
  // 重新排序 id
  items.value.forEach((item, idx) => {
    item.id = idx + 1;
  });
}

function delRow(id) {
  if (items.value.length > 1) {
    items.value = items.value.filter((item) => item.id !== id);
  }
  // 重新排序 id
  items.value.forEach((item, idx) => {
    item.id = idx + 1;
  });
}

function selectRowData(id, event, type) {
  const selectedValue = event.target.value;
  // 找到 items.value 陣列裡 id 等於傳入 id 的索引
  let data = type === 'drop' ? itemDatas : hostDatas;
  const index = items.value.findIndex((item) => item.id === id);
  if (index === -1) {
    console.warn('找不到對應 id 的項目');
    return;
  }
  console.log(selectedValue);
  // 從 itemDatas 找到選中項目（id == selectedValue）
  const selectedItem = data.find((item) => item.id == selectedValue);
  if (!selectedItem) {
    console.warn('找不到選擇的資料');
    return;
  }
  let itemName;
  //HACK 暫時性
  if (type === 'host') {
    hostShowText.value[id] =
      '<div style="display: flex; justify-content: space-between; width: 70%;"><span>主吊馬力</span><span>' +
      selectedItem.main_hoist_power +
      '<span></div><br>' +
      '<div style="display: flex; justify-content: space-between; width: 70%;"><span>吊速</span><span>' +
      selectedItem.hoisting_speed +
      '<span></div><br>' +
      '<div style="display: flex; justify-content: space-between; width: 70%;"><span>橫行馬力</span><span>' +
      selectedItem.trolley_motor_power +
      '<span></div><br>' +
      '<div style="display: flex; justify-content: space-between; width:70%;"><span>橫速</span><span>' +
      selectedItem.trolley_speed +
      '<span></div><br>' +
      '<div style="display: flex; justify-content: space-between; width: 70%;"><span>縱行馬力</span><span>' +
      selectedItem.long_travel_motor_power +
      '<span></div><br>' +
      '<div style="display: flex; justify-content: space-between; width:70%;"><span>縱速</span><span>' +
      selectedItem.long_travel_speed;
    itemName = selectedItem.name + '<br>' + hostShowText.value[id];
  } else {
    itemName = selectedItem.name;
  }

  // 更新 items.value 陣列中對應索引的資料
  items.value[index] = {
    ...items.value[index],
    name: itemName,
    quantity: selectedItem.quantity,
    unit: selectedItem.unit,
    price: selectedItem.price,
    total: selectedItem.total,
    // 其他欄位...
  };
}

// 匯出 Word 檔案 (.doc)
function exportDoc() {
  let content = `
    <html>
    <head>
      <meta charset='utf-8'>

      <style>
        table {
          border-collapse: collapse;
          width: 100%;
        }
        th, td {
          border: 1px solid #000;
          padding: 8px;

        }
        th {
          background-color: #f2f2f2;
          text-align: center;
        }
        td:nth-child(2), td:nth-child(3), td:nth-child(4) {
          text-align: right;
        }
        .alignCenter {
          text-align: center;
        }
      </style>
    </head>
    <body>
      <div class= "alignCenter">
        <h2>報價單</h2>
        <p><strong>客戶名稱：</strong>${customerName.value}</p>
      </div>
      <table>
        <tr>
          <th style="width: 10%">項次</th>
          <th style="width: 30%">品名規格</th>
          <th style="width: 10%">數量</th>

          <th style="width: 10%">單位</th>
          <th style="width: 20%">單價</th>
          <th style="width: 20%">複價</th>
        </tr>
        ${items.value
          .map(
            (item) => `
          <tr>
            <td class= "alignCenter">${item.id}</td>
            <td>${item.name}</td>
            <td>${item.quantity}</td>
            <td>${item.unit}</td>
            <td>${item.price}</td>
            <td>${(item.quantity * item.price).toLocaleString()}</td>
          </tr>`
          )
          .join('')}
      </table>
      <p style="text-align: right; margin-top: 10px;"><strong>總金額：</strong>${
        total.value
      } 元</p>

    <div style="margin-top: 40px;">
      <table style="width: 100%; border-collapse: collapse;">
        <tr>
          <td style="width: 50%; border: 1px solid black; background-color: #ddd; text-align: center; padding: 8px;">製表人簽名</td>
          <td style="width: 50%; border: 1px solid black; background-color: #ddd; text-align: center; padding: 8px;">審核人簽名</td>
        </tr>
        <tr style="height: 100px;">
          <td style="border: 1px solid black;"></td>
          <td style="border: 1px solid black;"></td>
        </tr>
      </table>
    </div>

    </body>
    </html>
  `;

  const blob = new Blob(['\ufeff' + content], {
    type: 'application/msword',
  });

  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = '報價單.doc';
  link.click();
}
</script>
