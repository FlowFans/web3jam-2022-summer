import { createApp } from 'vue'
import App from './App.vue'
import store from './store'
import router from './router'
import 'lib-flexible/flexible'
import Antd from 'ant-design-vue';
import 'ant-design-vue/dist/antd.css';
import {notification} from 'ant-design-vue';
import 'vant/lib/index.css';
import { Uploader } from 'vant';

const app = createApp(App) // 创建实例
app.use(notification)
app.use(Uploader);
app.use(Antd)
app.use(router)
app.use(store)

app.mount('#app')
