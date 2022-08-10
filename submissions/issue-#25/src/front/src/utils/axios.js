/**
 * 严肃声明：
 * 开源版本请务必保留此注释头信息，若删除我方将保留所有法律责任追究！
 * 本系统已申请软件著作权，受国家版权局知识产权以及国家计算机软件著作权保护！
 * 可正常分享和学习源码，不得用于违法犯罪活动，违者必究！
 * Copyright (c) 2020 sherard all rights reserved.
 * 版权所有，侵权必究！
 */
import axios from 'axios';
import { Toast } from 'vant';
//import router from '../router';
import { getUser,setUser} from '../store/user';
import { notification } from 'ant-design-vue';

axios.defaults.baseURL = process.env.NODE_ENV == 'development' ? 'http://8.140.32.8/' : 'http://8.140.32.8/'
//axios.defaults.withCredentials = true;
//axios.defaults.headers['X-Requested-With'] = 'XMLHttpRequest';
//axios.defaults.headers['token'] = localStorage.getItem('token') || '';
//axios.defaults.headers.post['Content-Type'] = 'application/json';

axios.interceptors.request.use(req => {
  if(req.url.indexOf('blitok/getVideoList')>-1) {
    return req;
  }
  const user = getUser();
  if (!user.accountId) {
        localStorage.removeItem('__account');
        //router.push({ path: '/login' });
        notification.warning({
          message: 'Authentication status error, please login again!'
        });
        return Promise.reject(new Error('Authentication status error'))
      } /* else if (
        !user.authTime ||
        new Date().getTime() - user.authTime > 15 * 60 * 1000
      ) {
        notification.warning({
          message: 'Authentication status timeout, please login again! '
        });
        resetUser();
        router.push({ path: '/login' });
      } */ else {
        setUser({authTime:new Date().getTime()})
      }
  return req;
});
axios.interceptors.response.use(res => {
  console.log(res);
  if (typeof res !== 'object') {
    console.log('!== object');
    Toast.fail('服务端异常！');
    return Promise.reject(res);
  }
  if (res.status != 200) {
    return Promise.reject(res.data);
  }
  return res.data;
},function (error) {
  console.log('error');
// 对请求错误做些什么
return Promise.reject(error);
});

export default axios;
