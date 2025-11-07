<template>
  <div class="flex h-screen relative">
    <!-- 側邊欄 -->
    <aside
      :class="[
        'bg-gray-800 text-white w-64 space-y-6 py-7 px-2 transform transition-transform duration-200 ease-in-out z-30 fixed md:relative md:translate-x-0 h-screen',
        isSidebarOpen ? 'translate-x-0' : '-translate-x-full',
      ]"
    >
      <h1 class="text-center my-4">
        <img
          class="mx-auto"
          src="../assets/icon.svg"
          alt="Logo"
          aria-hidden="true"
          style="max-width: 100px; height: auto"
        />
        <span class="sr-only">Logo</span>
      </h1>
      <nav>
        <router-link
          class="block py-2.5 px-4 hover:bg-gray-700 rounded transition-colors"
          to="/"
          active-class="bg-gray-700"
        >
          <i class="fa-solid fa-house mr-2"></i>
          報價管理系統
        </router-link>
        <router-link
          class="block py-2.5 px-4 hover:bg-gray-700 rounded transition-colors"
          to="/add"
          active-class="bg-gray-700"
        >
          <i class="fa-solid fa-cog mr-2"></i>
          項目範本管理
        </router-link>
        <router-link
          class="block py-2.5 px-4 hover:bg-gray-700 rounded transition-colors"
          to="/history"
          active-class="bg-gray-700"
        >
          <i class="fa-solid fa-clock-rotate-left mr-2"></i>
          歷史報價單
        </router-link>
      </nav>
    </aside>

    <!-- 主內容區 -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- 手機版漢堡選單按鈕 -->
      <header
        class="bg-gray-100 p-4 md:hidden flex justify-between items-center"
      >
        <div class="text-lg font-semibold">網站標題</div>
        <button
          @click="toggleSidebar"
          class="text-gray-800 text-3xl focus:outline-none"
        >
          ☰
        </button>
      </header>

      <!-- 遮罩層 -->
      <div
        v-if="isSidebarOpen && windowWidth < 768"
        @click="toggleSidebar"
        class="fixed inset-0 bg-black opacity-50 z-20 md:hidden"
      ></div>

      <main class="p-6 overflow-auto flex-1">
        <router-view />
      </main>

      <FooterElement />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import FooterElement from '../components/Footer.vue';

const isSidebarOpen = ref(false);
const windowWidth = ref(window.innerWidth);

// 切換側邊欄
function toggleSidebar() {
  isSidebarOpen.value = !isSidebarOpen.value;
}

// 監聽畫面寬度變化
function updateWindowWidth() {
  windowWidth.value = window.innerWidth;
  if (window.innerWidth >= 768) {
    isSidebarOpen.value = false; // ⚠️自動關閉手機漢堡側欄
  }
}

onMounted(() => {
  window.addEventListener('resize', updateWindowWidth); // 監聽視窗大小變化變動後呼叫函式
  updateWindowWidth(); // 初始設定
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', updateWindowWidth);
});
</script>

<style scoped>
/* 保底處理讓側邊欄在桌機固定顯示 */
@media (min-width: 768px) {
  aside {
    transform: translateX(0) !important;
    position: relative !important;
  }
}
</style>
