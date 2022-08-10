<template>

  <div class="app-container">

    <el-form ref="form" label-width="120px">

      <el-form-item label="Contract name">
        <el-input v-model="name"
                  placeholder="please input contract name，the NFT/Token Name"
                   />
      </el-form-item>

      <el-form-item label="Contract type">
        <el-select v-model="es_index"  @change="change_index" placeholder="选择索引库">
          <el-option
            v-for="item in options"
            :key="item.value"
            :label="item.label"
            :value="item.value">
          </el-option>
        </el-select>
      </el-form-item>


      <el-form-item>
        <el-button type="primary" @click="analyze()">
          Generate code
        </el-button>
      </el-form-item>
    </el-form>


    <el-form ref="form1"  label-width="120px">
      <el-form-item label="Contract code">
        <el-input v-model="code_text"
                  placeholder="Main contract code"
                  :rows="10" type="textarea"/>
      </el-form-item>

    </el-form>

  </div>


</template>

<script>
import { code } from '@/api/es_code'

export default {
  data() {
    return {
      name:"",//输入的行业名称
      code_text:"",//结果
      select_index:"",
      es_index:"",
      options: [],//索引列表
    }
  },
  created() {
    this.get_index()
  },
  methods: {
    //获得类型列表
    get_index() {
      this.options.push({value:'token',label:'token'})
      this.options.push({value:'nft',label:'nft'})

      this.es_index = 'nft' //默认一个index
      this.select_index = "nft"
    },

    change_index(es_index) {
      this.select_index = es_index
      console.log("select index", es_index)
    },

    //获得合约
    analyze() {
      let data = {
        "name": this.name,
        "code_type": this.select_index
      }
      code(data).then(response => {
        console.log(response)
        this.code_text = response.results[0].contract
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

