import { defineConfig } from 'umi';

export default defineConfig({
  nodeModulesTransform: {
    type: 'none',
  },
  routes: [
    { path: '/', component: '@/pages/index' },
    { path: '/user', component: '@/pages/unity/index' },
  ],
  fastRefresh: {},
  chainWebpack(config) {
    config.module
      .rule('woff')
      .test(/.(woff|eot|woff2|ttf|otf)$/)
      .use('file-loader')
      .loader('file-loader');
      .rules.push({
        test: /\.cdc/,
        type: "asset/source",
      })
  },
});
