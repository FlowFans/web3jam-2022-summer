<!--
 * 严肃声明：
 * 开源版本请务必保留此注释头信息，若删除我方将保留所有法律责任追究！
 * 本系统已申请软件著作权，受国家版权局知识产权以及国家计算机软件著作权保护！
 * 可正常分享和学习源码，不得用于违法犯罪活动，违者必究！
 * Copyright (c) 2020 sherard all rights reserved.
 * 版权所有，侵权必究！
 *
-->

<template>
  <div id="app">
    <router-view class="router-view" v-slot="{ Component }">
      <component :is="Component" />
    </router-view>
  </div>
</template>

<script>
  export default {
  }
</script>

<style lang="less">
html, body {
  height: 100% !important;
  overflow-x: hidden;
  overflow-y: auto;
  width: calc(100% + 15px) !important;
  padding-right: 15px;
  box-sizing: content-box;
}
#app {
  height: 100%;
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  // text-align: center;
  color: #2c3e50;
  font-size: 14px;
}

.router-view{
    width: 100%;
    height: auto;
    min-height: 100%;
}
</style>
