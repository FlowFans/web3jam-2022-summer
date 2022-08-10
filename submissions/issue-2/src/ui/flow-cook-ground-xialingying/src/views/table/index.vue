<template>
  <div class="app-container">

    <el-form :inline="true"  class="demo-form-inline">
      <div  v-for="(item, index) in item_prob">
        <el-form-item label="Item name">
          <el-input v-model="item.name"  placeholder="name like ipad" />
        </el-form-item>
        <el-form-item label="Item prob">
          <el-input v-model="item.prob"  placeholder="prob like 0.1, [0,1]" />
        </el-form-item>

        <el-form-item>
          <el-button type="danger" icon="el-icon-delete" circle @click="del_item(index)"></el-button>
        </el-form-item>

        <br/>

      </div>
      <el-button type="success" icon="el-icon-plus" circle @click="add_item()"></el-button>

    </el-form>

    <hr/>

    <el-form ref="form" label-width="120px">

      <el-form-item label="Contract name">
        <el-input v-model="name"
                  placeholder="please input contract name，the NFT collection Name"
        />
      </el-form-item>

      <el-form-item label="Address">
        <el-input v-model="contract_address"
                  placeholder="please input contract addess like 0x1231313"
        />
      </el-form-item>

      <el-form-item label="Code type">
        <el-select v-model="es_index"  @change="change_index" placeholder="">
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
      <el-form-item label="Code content">
        <el-input v-model="code_text"
                  placeholder="Main  code"
                  :rows="10" type="textarea"/>
      </el-form-item>

    </el-form>


  </div>
</template>

<script>

import { box_code } from '@/api/es_code'

export default {
  data() {
    return {
      formInline:{},
      item_prob:[{"name":"pen","prob":0.5},
        {"name":"keyboard","prob":0.4},
        {"name":"ipad","prob":0.1},],
      name:"",//输入的行业名称
      contract_address:"",
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
    filterNode(value, data) {
      if (!value) return true
      return data.label.indexOf(value) !== -1
    },
    //获得类型列表
    get_index() {
      this.options.push({value:'cadence',label:'cadence'})
      this.options.push({value:'vue.js',label:'vue.js'})

      this.es_index = 'cadence' //默认一个index
      this.select_index = "cadence"
    },

    change_index(es_index) {
      this.select_index = es_index
      console.log("select index", es_index)
    },

    //获得合约
    analyze() {
      this.code_text = ""
      let data = {
        "name": this.name,
        "contract_address":this.contract_address,
        "code_type": this.select_index,
        "quality_prob":JSON.stringify(this.item_prob)
      }

      console.log(data)
      box_code(data).then(response => {
        console.log(response)
        this.code_text = response.results[0].code
      })
    },

    //删除item
    del_item(index) {
      console.log(index)
      this.item_prob.splice(index,1)
    },
    //添加item
    add_item() {
      this.item_prob.push({"name":"","prob":0},)
    }

  }
}
</script>
