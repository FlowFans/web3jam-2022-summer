<template>
  <div class="app-container">

    <el-input v-model="domain" placeholder="输入主域名，如http://weibo.com" style="margin-bottom:30px;" />
    <el-input v-model="site_name" placeholder="输入站点名称，如weibo" style="margin-bottom:30px;" />

    <el-input
      type="textarea"
      :rows="10"
      placeholder="请输入频道部分html源码"
      v-model="html_text">
    </el-input>

    <p></p>


    <el-form :inline="true" :model="formInline" class="demo-form-inline">
      <!--<el-form-item label="频道层级(1-3)">-->
        <!--<el-input v-model="level" placeholder="请输入标签匹配"></el-input>-->
      <!--</el-form-item>-->

      <el-form-item>
        <el-button type="primary" @click="get_nav_link()" :disabled="url_classify_button">分析频道链接</el-button>
      </el-form-item>

    </el-form>

    <el-input
      type="textarea"
      :rows="10"
      placeholder="结果显示"
      v-model="html_nav">
    </el-input>


    <!--<p>推荐正则：</p>-->

    <!--<el-card class="box-card">-->
    <!--<div slot="header" class="clearfix">-->
    <!--<span>推荐正则表达式</span>-->
    <!--</div>-->
    <!--<div class="text item">-->
    <!--*-->
    <!--</div>-->
    <!--</el-card>-->

  </div>
</template>

<script>

  import { getList,get_nav_link } from '@/api/ins'

  export default {
  data() {
    return {
      formInline:{},
      domain: '',
      html_nav:'', //显示的分析结果
      html_text:"",
      site_name:'',
      domain:"",
      stat_text:"", //正则统计数据
      level:1, //标签级别
      url_classify_button:false,
      url_match_button:false,
    }
  },
  methods: {
    get_nav_link() {
      this.url_classify_button = true
      get_nav_link({domain:this.domain, html:this.html_text, site_name:this.site_name}).then(response => {
        var data = response
        this.html_nav = data.results
        console.log(this.html_nav)
        this.url_classify_button = false
      })
    },
  }
}
</script>

<style scoped>
.line{
  text-align: center;
}
</style>

