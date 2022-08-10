import request from '@/utils/request'

var host_name = "http://rest.flow.study/"
//var host_name = "http://localhost:8080/"


export function hello() {
  return request({
    url: host_name + 'hello',
    method: 'get',
  })
}

export function search(params) {
  return request({
    url: host_name + 'search',
    method: 'get',
    params
  })
}

export function code(params) {
  return request({
    url: host_name + 'code',
    method: 'get',
    params
  })
}

//转盘相关
export function box_code(params) {
  return request({
    url: host_name + 'box_code',
    method: 'get',
    params
  })
}
