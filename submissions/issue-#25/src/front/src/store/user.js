import { reactive } from 'vue';
export const user = reactive({
  name: 'The Wekend',
  accountId: null,
  authTime: null
});

export function setUser(obj) {
  Object.assign(user, obj);
  sessionStorage.setItem('__account', JSON.stringify(user));
}
export function resetUser() {
  for (let x in user) {
    user[x] = null;
  }
  sessionStorage.removeItem('__account');
}
export function getUser() {
  if(user.accountId) {
    sessionStorage.setItem('__account', JSON.stringify(user));
    return user;
  }
  const obj = sessionStorage.getItem('__account');
  try {
    if(obj) {
      Object.assign(user, JSON.parse(obj));
      return user;
    }else{
      return {}
    }
  } catch (error) {
    console.log(error);
    return {}
  }
}
export function updateUser() {
  if(user.accountId) {
    user.authTime = new Date().getTime();
    sessionStorage.setItem('__account', JSON.stringify(user));
  }
}
