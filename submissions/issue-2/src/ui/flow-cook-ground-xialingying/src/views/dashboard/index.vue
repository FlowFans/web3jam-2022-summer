<template>
  <div class="dashboard-container">
    <el-card style="height: 100px">
      <!-- 搜索框 -->
      <div class="search">
        <el-input
          placeholder="Please input keyword to search code"
          v-model="word"
          class="input-with-select"
          @change="search()"
          style="width: 90%; margin-left: 5%; margin-top: 10px"
        >
          <el-button slot="append" @click="search()">Search</el-button>
        </el-input>
      </div>
<!--      <el-select v-model="es_index"  @change="change_index"	style="margin-left: 52px;margin-top: 10px" placeholder="选择索引库">-->
<!--        <el-option-->
<!--          v-for="item in options"-->
<!--          :key="item.value"-->
<!--          :label="item.label"-->
<!--          :value="item.value">-->
<!--        </el-option>-->
<!--      </el-select>-->
<!--      （索引文件：{{select_index["docs.count"]}}，索引大小：{{select_index["store.size"]}}）-->
    </el-card>

    <!-- 搜索结果 -->
    <el-card  v-for="(item, index) in search_result" :key="index" style="height: 150px" >
      <div slot="header" class="clearfix">
        <span><el-link type="primary" :href="item.url" target="_blank">
          <span v-html="(start+index+1) + '：'+ item.title"></span>
        </el-link></span>
        <el-button style="float: right; padding: 3px 0;color: green" type="text">{{item.score}}</el-button>
      </div>
      <div class="text item">
        <span v-html="item.content"></span>
      </div>
    </el-card>

    <el-row type="flex" justify="end" style="margin-top: 10px">
      <el-pagination
        style=""
        background
        layout="prev, pager, next"
        @current-change="change_page"
        :total="total">
      </el-pagination>
    </el-row>



  </div>
</template>

<script>
import { get_index, get_index_info } from '@/api/es'
import { hello, search } from '@/api/es_code'

export default {
  name: 'Dashboard',
  filters: {
    filterStr: function (value) {
      if(value&& value.length > 500) {
        value= value.substring(0,500)+ '...';
      }
      return value;
    },
    tranNumber(num) {
      // 十万以内直接返回，大于6位数是十万 (以10W分割 10W以下全部显示)，大于8位数是亿
      if (num != null) {
        const numStr = num.toString()

        if (numStr.length > 8) {
          // eslint-disable-next-line no-undef
          // let decimal = numStr.substring(numStr.length - 8, 3);
          return parseFloat(num / 100000000).toFixed(1) + '亿'
        } else if (numStr.length > 4) {
          // let decimal = numStr.substring(numStr.length - 4, 3);
          // return parseFloat(parseInt(num / 10000) + "." + decimal) + " w";
          return parseFloat(num / 10000).toFixed(1) + 'w'
        }
        return numStr
      }
    },
    formatPrice(value) {
      // 截取当前数据到小数点后两位
      const realVal = value.toFixed(2)
      return realVal
    }
  },
  data() {
    return {
      select_index:{},
      word:"",//搜索词
      es_index:"",//索引名称
      search_result:[],//搜索结果
      total:0,//搜索结构数
      options: [],//索引列表
      start:0,//搜索结果index
    }
  },
  created() {
    //获得索引列表
    //this.get_index()
  },
  methods: {
    hello() {
      hello().then(response => {
        console.log(response)
      })
    },

    change_index(es_index) {
      console.log("select index", es_index)
      get_index_info(es_index).then(response => {
        this.select_index = response[0]
      })
    },
    //搜索
    search() {
      let index_name = this.es_index //索引名
      let data = {
        "word": this.word,
        "start":this.start,
        "limit":10
      }
      search(data).then(response => {
        console.log(response)
        this.search_result = []
        for (let i=0;i<response.results.length;i++) {
          let title = response.results[i].contract_name //默认使用原来的标题
          let content =   response.results[i].contract_code //默认使用原来的标题
          let score = response.results[i]._score
          let url = response.results[i].url
          this.search_result.push({title:title,content:this.filter_str(content),score:score,url:url })
        }
        this.total = response.num
      })
    },
    //获得索引列表
    get_index() {
      const TronWeb = require('tronweb')
      const tronWeb = new TronWeb({
        fullHost: 'https://api.shasta.trongrid.io',
        headers: { "TRON-PRO-API-KEY": 'fec19e87-e92e-4e92-abbb-9b5d497e527f' },
        privateKey: 'c279a9ef3d84f8d45038834e15039dd4ea57f2f19a1d4bae5489a79febe83b57'
      })
      console.log(tronWeb.address.toHex("TNPeeaaFB7K9cmo4uQpcU32zGK8G1NYqeL"))

      tronWeb.trx.getAccount('TUjgT7GiZeyDAokyjFp7WiatHJUjCZUL4J').then(result => console.log(result))
    },
    //翻页
    change_page(page) {
      //默认一页10个结果。page-size
      this.start = 10*(page-1)
      this.search()//搜索

    },

    filter_str(value) {
      value = value.replace(/<BR\/>/g, "")
      if(value&& value.length > 500) {
        value= value.substring(0,500)+ '...';
      }
      return value;
    },
  }
}
</script>

<style lang="scss" scoped>
.dashboard {
  &-container {
    margin: 30px;
  }
  &-text {
    font-size: 30px;
    line-height: 46px;
  }
}
</style>

<style>
em{
  color:red;
}
</style>
