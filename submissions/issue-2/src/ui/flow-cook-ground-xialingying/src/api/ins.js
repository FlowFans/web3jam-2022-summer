import request from '@/utils/request'

var host_name = ""
if (process.env.NODE_ENV=="development") {
  host_name = "http://127.0.0.1:8080/"
} else {
  host_name = "http://47.242.166.104:8080/"
}

host_name = "http://47.242.166.104:8080/"


export function hello() {
  return request({
    url: host_name + 'hello',
    method: 'get',
  })
}


export function get_url_classify(params) {
  return request({
    url: host_name + 'get_url_classify',
    method: 'get',
    params
  })
}

//正则匹配
export function get_url_match(params) {
  return request({
    url: host_name + 'get_url_match',
    method: 'get',
    params
  })
}

//标签分组
export function get_tag_classify(params) {
  return request({
    url: host_name + 'get_tag_classify',
    method: 'get',
    params
  })
}

//标签匹配
export function get_tag_match(params) {
  return request({
    url: host_name + 'get_tag_match',
    method: 'get',
    params
  })
}

//获得导航链接
export function get_nav_link(params) {
  return request({
    url: host_name + 'get_nav_link',
    method: 'get',
    params
  })
}

//获得标题正文
export function get_title_content(params) {
  return request({
    url: host_name + 'get_title_content',
    method: 'get',
    params
  })
}
