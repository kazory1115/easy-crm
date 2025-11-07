<template>
  <div>
    <router-view />
  </div>
</template>

<script setup>
// 載入components 物件
// import HelloWorld from './components/HelloWorld.vue';
</script>

<!-- 
 <style scoped> 的功能，這是一個非常實用的機制，
 可以讓 CSS 只作用於當前元件的模板元素，防止樣式「污染」到其他元件。
-->
<style scoped>
body {
  margin: 0;
  display: flex;
  place-items: center;
  min-width: 320px;
  min-height: 100vh;
}
</style>
