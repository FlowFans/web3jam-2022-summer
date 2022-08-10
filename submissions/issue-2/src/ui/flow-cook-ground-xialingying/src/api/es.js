import request from '@/utils/request'

var host_name = "http://47.242.206.108:9200/"


export function hello() {
  return request({
    url: host_name,
    method: 'get',
  })
}

//index,索引，data：post的数据
export function search(index, data) {
  return request({
    url: host_name + index +"/_search",
    method: 'post',
    data
  })
}

//获得索引列表
export function get_index(params) {
  return request({
    url: host_name + '_cat/indices?format=json',
    method: 'get',
    params
  })
}


//获得索引信息
export function get_index_info(index) {
  return request({
    url: host_name + '_cat/indices/'+ index +'?format=json',
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

//获得分词结果，index,索引，data：post的数据
export function analyze(index, data) {
  return request({
    url: host_name + index +"/_analyze",
    method: 'post',
    data
  })
}
