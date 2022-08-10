
module.exports = {
  productionSourceMap:true,
  publicPath: "./",
  // cli3 代理是从指定的target后面开始匹配的，不是任意位置；配置pathRewrite可以做替换
  devServer: {
    proxy: {
      // 请求接口
      '/baseUrl': {
        target: 'http://8.140.32.8/',
        changeOrigin: true,
        secure: true,
        // remove path
        pathRewrite: { '^/baseUrl': '' }
      }
    }
  },
}
