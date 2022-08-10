export default {
    chainWebpack(memo, { env, webpack, createCSSRule }) {
      // 设置 alias
      memo.resolve.alias.set('foo', '/tmp/a/b/foo');
      // 删除 umi 内置插件
      memo.plugins.delete('progress');
      memo.plugins.delete('friendly-error');
      memo.plugins.delete('copy');
    }
  }