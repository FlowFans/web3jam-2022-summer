<!--
 * 严肃声明：
 * 开源版本请务必保留此注释头信息，若删除我方将保留所有法律责任追究！
 * 本系统已申请软件著作权，受国家版权局知识产权以及国家计算机软件著作权保护！
 * 可正常分享和学习源码，不得用于违法犯罪活动，违者必究！
 * Copyright (c) 2020 sherard all rights reserved.
 * 版权所有，侵权必究！
 *
-->

<template>
  <div class="container">
    <div class="home">
      <div class="header">
        <div class="title"><img src="../assets/logo.png" />cryptoSharing</div>
        <div class="tabs">
          <div
            class="item"
            v-bind:class="[{ active: tab == 'rent' }]"
            @click="switchTo('rent')"
          >
            RENT
          </div>
          <div
            class="item"
            v-bind:class="[{ active: tab == 'lend' }]"
            @click="switchTo('lend')"
          >
            LEND
          </div>
          <div
            class="item"
            v-bind:class="[{ active: tab == 'dash' }]"
            @click="switchTo('dash')"
          >
            MY DASHBOARD
          </div>
        </div>
       <div style="margin-right:20px;">{{user?user.addr : ''}}</div>
        <div class="contact">
          <a-button v-if="!isLogin" class="login" type="primary" @click="goTo"
            >Connect wallet</a-button
          >
          <!-- <img v-if="isLogin" src="../assets/person.svg"/> -->
          <img v-if="isLogin" src="../assets/wallet.svg" @click="showDrawer" />
        </div>
      </div>
      <img class="collections" src="./../assets/view_bg.png" />
      <div class="content" v-if="tab == 'rent'">
        <div class="nologin" v-if="!isLogin">
          You don't have any NFTs to rent
        </div>
        <div v-if="isLogin" class="detail_container">
          <div class="title">Explore Rent Collections</div>
          <div class="detail_bag" v-if="rentDetails && !_rentLoading">
            <div class="detail_total">{{ rentDetails.total }} items</div>
            <div class="main-content">
              <div
                v-for="(item, index) in rentDetails"
                :key="index"
                class="min-item"
                v-show="!item.notshow"
              >
                <img :src="lendImg[index]" />
                <div style="    position: absolute;top: 5px;width: 100%;text-align: center;">{{item.fromAddress}}</div>
                <div class="name">{{ item.id }}</div>
                <div class="tags">
                  <div class="tag flex">price：<div>{{item.price}} FLOW / Day</div></div>
                  <div class="tag">maxRentTime：<div>{{item.maxRentTime}}</div></div>
                </div>
                <div class="desc">
                  <a-button class="normal-btn" type="primary" @click="rentItem(item)"
                    >Rent</a-button
                  >
                </div>
              </div>
            </div>
          </div>
          <div style="text-align: center;padding: 40px 0;" v-if="_rentLoading">
            <a-spin />
          </div>
        </div>
      </div>
      <div class="content" v-if="tab == 'lend'">
        <div class="nologin" v-if="!isLogin">
          You don't have any NFTs to lend
        </div>
        <div v-if="isLogin" class="detail_container">
          <div class="title">Explore Lend Collections</div>
          <div class="detail_bag" v-if="lendDetails && !_lendLoading">
            <div class="detail_total">{{ lendDetails.length }} items</div>
            <div class="main-content">
              <div
                v-for="(item, index) in lendDetails"
                :key="index"
                class="min-item"
              >
                <img :src="lendImg[index]" />
                <div class="name">{{ item.id }}</div>
                <div class="desc">
                  <a-button v-if="!item.desc" class="normal-btn" type="primary" @click="lendItem(item.id)"
                    >Lend</a-button
                  >
                  <div v-if="item.desc">{{item.desc}} </div>
                </div>
              </div>
            </div>
          </div>
          <div style="text-align: center;padding: 40px 0;" v-if="_lendLoading">
            <a-spin />
          </div>
        </div>
      </div>
      <div class="content" v-if="tab == 'dash'">
        <div class="nologin" v-if="!isLogin">
          You don't have any NFTs in your dashboard
        </div>
        <div v-if="isLogin" class="detail_container">
          <div class="title">Explore dashboard Collections</div>
          <div class="dashtab">
            <div class="mytab" v-bind:class="[{ active: dashtab == 'mylend' }]" @click="switchDashTo('mylend')">my lendNFT</div>
            <div class="mytab tab2" v-bind:class="[{ active: dashtab == 'myrent' }]" @click="switchDashTo('myrent')">my rentNFT</div>
          </div>
          <div class="detail_bag" v-if="dashDetails && !_dashLoading">
            <div class="detail_total">{{ dashDetails.total }} items</div>
            <div class="main-content">
              <div
                v-for="(item, index) in dashDetails"
                :key="index"
                class="min-item"
              >
                <img :src="lendImg[index]" />
                <div class="name">{{ item.id }}</div>
                <div class="tags" v-if="item.desc || item.endTime">
                  <div class="tag flex" v-if="item.desc">{{item.desc}}</div>
                  <div class="tag flex" v-if="item.endTime">过期时间：<div>{{item.endTime}}</div></div>
                </div>
              </div>
            </div>
          </div>
          <div style="text-align: center;padding: 40px 0;" v-if="_dashLoading">
            <a-spin />
          </div>
        </div>
      </div>
    </div>

    <!-- search 弹窗  -->
    <div class="mask" id="searchMask" v-if="_searchDicshow" @click="closeSearch">
      <div class="search-form">
        <div class="title">选择代币</div>
        <a-input
          class="input"
          v-model:value="searchNtf"
          placeholder="搜索名称或粘贴地址"
          @change="searchOnChange"
          />
        <div class="common-tips">
          常用代币
          <img src="../assets/yips.png" height="20" width="20">
        </div>
        <div class="common">
          <div class="item">
            <img src="../assets/toast_1.png">
            <span>AXS</span>
          </div>
          <div class="item">
            <img src="../assets/toast_2.png">
            <span>MANA</span>
          </div>
          <div class="item">
            <img src="../assets/toast_3.png">
            <span>SAND</span>
          </div>
        </div>

        <div class="all-ntf">
          <div v-for="(ntf, index) in ntfsFilterItems" :key="index" class="item"  @click="confirmSearch(ntf, index)">
            <img :src="lendGameImg[index]" >
            <div class="bag">
              <div class="name">{{ntf._name}}</div>
              <div class="symbol">{{ntf._symbol}}</div>
            </div>
          </div>
        </div>
      </div>
      <div class="x"  @click="_searchDicshow = false">X</div>
    </div>
    <!-- search2 弹窗  -->
    <div class="mask" id="searchMask2" v-if="_searchDicshow2" @click="closeSearch2">
      <div class="search-form">
        <div class="title">选择代币</div>
        <a-input
          class="input"
          v-model:value="searchNtf"
          placeholder="搜索名称或粘贴地址"
          @change="searchOnChange"
          />
        <div class="common-tips">
          常用代币
          <img src="../assets/yips.png" height="20" width="20">
        </div>
        <div class="common">
          <div class="item">
            <img src="../assets/eth.png">
            <span>ETH</span>
          </div>
          <div class="item">
            <img src="../assets/usdc.png">
            <span>USDC</span>
          </div>
          <div class="item">
            <img src="../assets/usdt.png">
            <span>USDT</span>
          </div>
        </div>

        <div class="all-ntf">
          <div v-for="(ntf, index) in ntfsFilterItems2" :key="index" class="item">
            <img :src="biteBi[index]" >
            <div class="bag">
              <div class="name">{{ntf.name}}</div>
              <div class="symbol">{{ntf.desc}}</div>
            </div>
          </div>
        </div>
      </div>
      <div class="x"  @click="_searchDicshow2 = false">X</div>
    </div>
    <!-- 侧边栏  -->
    <div>
      <a-drawer
        class="drawer"
        placement="right"
        :closable="false"
        :visible="drawerVisible"
        :after-visible-change="afterVisibleChange"
        @close="onClose"
        width="400"
      >
        <div class="content" v-if="isLogin">
          <div class="title">
            <div class="online">
              <img src="../assets/online.png" />
            </div>
            <a-collapse
              style="width: 130px;"
              class="expand"
              v-model="activeKey"
              :expand-icon-position="expandIconPosition"
            >
              <a-collapse-panel key="1" header="My Wallet">
                <div
                  style="display:flex;align-items: center;cursor: pointer;"
                  @click="logOut"
                >
                  <LogoutOutlined style="margin-right:14px;" />
                  <p style="margin-bottom: 0;">Log out</p>
                </div>
              </a-collapse-panel>
            </a-collapse>
            <a-tooltip class="account">
              <template #title>{{ user.accountId }}</template>
              {{ user.accountId }}
            </a-tooltip>
          </div>
          <div class="money">
            <div class="balance">Total balance</div>
            <div class="btn">add Funds</div>
          </div>
        </div>
      </a-drawer>
    </div>
    <!-- rent -->
    <a-modal
      :visible="modalRentVisible"
      title="fill rent information"
      style="top: 20px"
      :footer="false"
      :closable="false"
      width="400px"
      :confirmLoading="confirmRentLoading"
      @cancel="modalRentVisible = false"
    >
      <a-form :model="rentFormState">
        <a-form-item label="How many day you want rent" name="rentDay">
          <a-input
            v-model:value="rentFormState.rentDay"
            type="number"
            placeholder="fill nft price"
          />
        </a-form-item>
      </a-form>
      <div class="footer">
        <a-button
          class="normal-btn"
          type="primary"
          :loading="confirmRentLoading"
          @click="confirmRent(id, index)"
          >Confirm</a-button
        >
      </div>
    </a-modal>

    <!-- lend  -->
    <a-modal
      :visible="modalVisible"
      title="fill lend information"
      style="top: 20px"
      :closable="false"
      width="400px"
      :confirmLoading="confirmLendLoading"
      @cancel="modalVisible = false"
      :footer="false"
    >
      <a-form :model="lendFormState">
        <a-form-item label="lend deadline" name="lendTime">
          <a-date-picker
            v-model:value="lendFormState.lendTime"
            show-time
            type="date"
            allowClear="false"
            placeholder="Pick a date"
            style="width: 100%"
            :disabledMinutes="true"
          />
        </a-form-item>
        <a-form-item label="lend money a day" name="lendMoney">
          <a-input
            v-model:value="lendFormState.lendMoney"
            placeholder="fill nft price"
          />
        </a-form-item>
      </a-form>
      <div class="footer">
        <a-button
          class="normal-btn"
          type="primary"
          :loading="confirmLendLoading"
          @click="confirmLend(id, index)"
          >Confirm</a-button
        >
      </div>
    </a-modal>

    <!-- dashboar lent setting -->
    <a-modal
      :visible="modalLentSettingVisible"
      title="set RENT NFT"
      style="top: 20px"
      :footer="false"
      :closable="false"
      width="400px"
      :confirmLoading="confirmLentSettingLoading"
      @cancel="modalLentSettingVisible = false"
    >
      <a-form :model="lentSettingFormState">
        <a-form-item label="setPrice" name="price">
          <a-input
            v-model:value="lentSettingFormState.price"
            type="number"
            placeholder="inputing"
            style="width: 195px;margin-right: 5px;"
          />
          <a-button
            class="normal-btn"
            type="primary"
            @click="setingPrice"
            >Set</a-button
          >
        </a-form-item>

        <a-form-item label="setRentLock" name="lock">
          <a-input
            v-model:value="lentSettingFormState.lock"
            placeholder="true or false"
            style="width: 195px;margin-right: 5px;"
          />
          <a-button
            class="normal-btn"
            type="primary"
            @click="setingLock"
            >Set</a-button
          >
        </a-form-item>

        <a-form-item label="setMaxRentTime" name="maxRentTime">
          <a-date-picker
            v-model:value="lentSettingFormState.maxRentTime"
            show-time
            type="date"
            allowClear="false"
            placeholder="Pick a date"
            style="width: 195px;margin-right: 5px;"
            :disabledMinutes="true"
          />
          <a-button
            class="normal-btn"
            type="primary"
            @click="setingMaxRentTime"
            >Set</a-button
          >
        </a-form-item>
      </a-form>
    </a-modal>

    <!-- withdraw -->
    <a-modal
      :visible="modalWithdrawVisible"
      title="withdraw"
      style="top: 20px"
      :footer="false"
      :closable="false"
      width="400px"
      @cancel="modalWithdrawVisible = false"
    >
      <a-form :model="withdrawFormState">
        <a-form-item label="how many you want withdraw" name="number">
          <a-input
            v-model:value="withdrawFormState.number"
            type="number"
            placeholder="inputing"
            style="width: 100%"
          />
        </a-form-item>
      </a-form>
      <div class="footer">
        <a-button
          class="normal-btn"
          type="primary"
          :loading="withdrawLoading"
          @click="withdrawConfirm(id, index)"
          >confirm</a-button
        >
      </div>
    </a-modal>
  </div>
</template>

<script>
import { reactive, toRefs, onMounted } from 'vue';
import { message } from 'ant-design-vue';
import {getUser, setUser } from '../store/user';
import { LogoutOutlined } from '@ant-design/icons-vue';
import {GET_USEFUL_IDS} from './js/GET_USEFUL_IDS';
import {GET_IDS_WITH_PRICE} from './js/GET_IDS_WITH_PRICE';
import {GET_BLOCK} from './js/GET_BLOCK';
import {RENT} from './js/RENT';
import {getUser1} from './js/getUser1';
import {LIST_FOR_SALE} from './js/LIST_FOR_SALE';
import {getLendIDs} from './js/getLendIDs';
import {GET_EXPIRED} from './js/GET_EXPIRED';

// import {CREATE_USER_COLLECTION} from './js/CREATE_USER_COLLECTION';
const limitNum =9998;
const addressList = ['0x3e36cb2e9c3b4539','0x0721be347a6c778a','0xb096b656ab049551'];
import { formatDate } from './abi/util';
import * as fcl from "@onflow/fcl"
export default {
  components: {
    LogoutOutlined
  },
  setup() {
    const state = reactive({
      tab: 'rent',
      isLogin: false,
      user: null,
      sort: '1',
      dashtab:'mylend',
      ntfsFilterItems:[],
      ntfsFilterItems2:[
        {name:'ETH',desc:'ETH'},
        {name:'USDT',desc:'USDT'}
      ],
      ntfsItems:[],
      searchNtf:'',
      walletProfit:0,
      nowNtfName:'',
      Factory: '0xDa4696D9503C20A2aE212254c27276646744f3d2',
      nowNtfName2:'SELECT PAY TOKEN',
      _searchDicshow:false,
      _searchDicshow2:false,
      drawerVisible: false,
      expandIconPosition: 'right',
      provider: null,
      modalVisible: false,
      _lendLoading: false,
      _rentLoading: false,
      _dashLoading:false,
      modalLentSettingVisible:false,
      confirmLentSettingLoading:false,
      modalWithdrawVisible:false,
      lentSettingFormState:{
        price:1,
        lock:'true',
        maxRentTime:null
      },
      withdrawFormState:{
        number:1,
      },
      withdrawLoading: false,
      lendFormState: {
        lendTime: null,
        lendMoney: null
      },
      modalRentVisible: false,
      rentFormState: {
        rentDay: null
      },
      biteBi:[
        require('./../assets/eth.png'),
        require('./../assets/usdc.png'),
        require('./../assets/usdt.png'),
      ],
      sortArr: [
        { value: '1', label: 'PRICE: HIGH TO LOW' },
        { value: '2', label: 'PRICE: LOW TO HIGH' }
      ],
      poolAddress: [
        '0x6Af04c4dd5BC159fDD3C8e3Ef8E5C1766A185a4c', //AXS USDT
        '0xcB3004c42187c124D6903eD002a949Fc5266A34D', //SAND USDT
        '0xB563dD0A6790DFBaDbD687988E8628EA32EE44D4'  //MANA USDT
      ],
      rentImg: [
        '../assets/icon1.jpeg',
        '../assets/icon2.jpeg',
        '../assets/icon3.jpeg'
      ],
      rentList: [{ id: 1 }, { id: 2 }, { id: 3 }],
      /* lend 信息  */

      rentGameList: null,
      NFTAddress: [
        '0x9a8E2c523D76151b4514281FeCe5E28439B3cCFe', //AXS
        '0x344AC4347cAFAE359962188fBe4aE16f738Cca46', //SAND
        '0xa0C86f099676e6642CFcB1e52fC95cC6C0E0AC3e' //MANA
      ],
      lendGameList: null,
      lendGameImg: [
        require('./../assets/lend_game.jpeg'),
        require('./../assets/lend_game2.jpeg'),
        require('./../assets/lend_game3.jpeg')
      ],
      lendImg: [
        require('../assets/lend_icon_1.jpeg'),
        require('../assets/lend_icon_2.jpeg'),
        require('../assets/lend_icon_3.jpeg'),
        require('../assets/lend_icon_4.jpeg'),
        require('../assets/lend_icon_5.jpeg'),
        require('../assets/lend_icon_6.jpeg'),
        require('../assets/lend_icon_7.jpeg'),
        require('../assets/lend_icon_8.jpeg'),
        require('../assets/lend_icon_9.jpeg'),
        require('../assets/lend_icon_10.jpeg')
      ],
      lendDetails: null,
      rentDetails: null,
      rentAddressIndex: null,
      lendAddressIndex: null,
      confirmLendLoading: false,
      approveLendDisabled: false,
      approveLendLoading: false,
      confirmLendDisabled: false,
      lendInfo: {},
      axsNFTContractMap:{},
      confirmRentLoading: false,
      approveRentDisabled: false,
      approveRentLoading: false,
      confirmRentDisabled: false,
      rentInfo: {},
      USDTContractMap:{},

      dashGameList:[],
      dashDetails: null,
      gamePoolArr:[],
      gameNFTArr:[],
      _dashDetailsLoading:false,
      dashGamepool:null
    });

    onMounted(() => {
      fcl.currentUser().subscribe(authUser);
      fcl.config()
      .put("accessNode.api", "https://access-testnet.onflow.org")
      .put("discovery.wallet", "https://fcl-discovery.onflow.org/testnet/authn")
      init();
    });

    //const router = useRouter();
    // get started
    const goTo = () => {
      fcl.authenticate();
    }
    const authUser = async(n) => {
      state.user = getUser();
      console.log(state.user);
      if (!n.loggedIn) {
        state.isLogin = false;
      } else {
        setUser(n);
        state.isLogin = true;
      }
    };
    const switchTo = t => {
      state.tab = t;
      console.log(t);
      init();
    };

    const init = async() => {
      /* let res = await fcl.mutate({cadence: CREATE_USER_COLLECTION,limit:limitNum});
      console.log(res); */

      fcl.currentUser().subscribe(authUser);
      if (state.tab == 'lend') {
        lendInit();
      } else if (state.tab == 'rent') {
        rentInit();
      } else if (state.tab == 'dash') {
        switchDashTo('mylend');
      }
    };

    const searchOnChange = ()=>{
      const arr = state.ntfsItems.filter(item=>{
        return item._name.indexOf(state.searchNtf) >-1;
      })
      state.ntfsFilterItems = arr;
    }
    const closeSearch = (event) => {
       if (event.target == document.getElementById('searchMask')) {
        state._searchDicshow = false;
      }
    }
    const closeSearch2 = (event) => {
       if (event.target == document.getElementById('searchMask2')) {
        state._searchDicshow2 = false;
      }
    }

    const searchDicshow = () => {
      state._searchDicshow = true;
      state.searchNtf = '';
      state.ntfsFilterItems = state.ntfsItems;
    }

    const searchDicshow2 = () => {
      state._searchDicshow2 = true;
      state.searchNtf2 = '';
    }

    const confirmSearch = (ntf)=>{
      state.nowNtfName = ntf._name;
      if(state.tab=='lend') {
        console.log('showLendGameDeail');
      }else if(state.tab =='rent') {
        console.log('1');
      }else if(state.tab =='dash') {
        console.log('showDashGameDeail(ntf, 0);');
      }
      state._searchDicshow = false;
    }

    const handleSortChange = (value, option) => {
      console.log(value);
      console.log(option);
      console.log(state.sort);
    };
    const showDrawer = () => {
      state.drawerVisible = true;
    };
    const onClose = () => {
      state.drawerVisible = false;
    };

    const rentInit = async () => {
      console.log(state.user);
      state._rentLoading = true;
      state.rentDetails = [];
      let res1 = await fcl.query({cadence: GET_IDS_WITH_PRICE,args: (arg,t) => [arg(addressList[0],t.Address)]});
      let res2 = await fcl.query({cadence: GET_IDS_WITH_PRICE,args: (arg,t) => [arg(addressList[1],t.Address)]});
      let res3 = await fcl.query({cadence: GET_IDS_WITH_PRICE,args: (arg,t) => [arg(addressList[2],t.Address)]});
      let blockNum= await fcl.query({cadence: GET_BLOCK});
      console.log(blockNum);

      console.log(res1);
      state._rentLoading = false;
      Object.keys(res1).forEach(id=>{
        state.rentDetails.push({
          id:id,
          price: Object.keys(res1[id])[0],
          maxRentTime:formatDate(new Date(Math.round((res1[id][Object.keys(res1[id])[0]] - blockNum)) *1000 +new Date().getTime()), 'yyyy-MM-dd hh:mm'),
          fromAddress:addressList[0]
        })
      })
      Object.keys(res2).forEach(id=>{
        state.rentDetails.push({
          id:id,
          price: Object.keys(res2[id])[0],
          maxRentTime:formatDate(new Date(Math.round((res2[id][Object.keys(res2[id])[0]] - blockNum)) *1000 +new Date().getTime()), 'yyyy-MM-dd hh:mm'),
          fromAddress:addressList[1]
        })
      })
      Object.keys(res3).forEach(id=>{
        state.rentDetails.push({
          id:id,
          price: Object.keys(res3[id])[0],
          maxRentTime:formatDate(new Date(Math.round((res3[id][Object.keys(res3[id])[0]] - blockNum)) *1000 +new Date().getTime()), 'yyyy-MM-dd hh:mm'),
          fromAddress:addressList[2]
        })
      })
      console.log(state.rentDetails);
    };
    // now
    const lendInit = async () => {
      let res = await fcl.query({cadence: GET_USEFUL_IDS ,args: (arg,t) => [arg(state.user.addr,t.Address)]});
      let res2 = await fcl.query({cadence: GET_IDS_WITH_PRICE,args: (arg,t) => [arg(state.user.addr,t.Address)]});
      res2 = Object.keys(res2).map(id=>Number(id));
      console.log(res);
      console.log(res2);
      const arr = [];
      res.forEach(id=>{
        if(res2.indexOf(id) > -1) {
          arr.push({id:id,desc:'已置入市场'})
        }else{
          arr.push({id:id})
        }
      });
      console.log(arr);
      state.lendDetails = arr;
    };
    const lendItem = async(id) => {
      console.log(id); // 10002
      state.lendInfo = id;
      state.lendFormState.lendTime = null;
      state.lendFormState.lendMoney = 0;
      state.modalVisible = true;
      state.confirmLendLoading = false;
    };

    const confirmLend = async() => {
      state.confirmLendLoading = true;
      console.log(state.lendFormState);
      const time = state.lendFormState.lendTime.valueOf();
      const difTime = Math.round((time - new Date().getTime())/1000);
      console.log(difTime);
      try {
        let res1= await fcl.query({cadence: GET_BLOCK});
        console.log(res1);
        const expired = difTime + res1;
        let res2 = await fcl.mutate({cadence: LIST_FOR_SALE,args: (arg,t) => [arg(state.lendInfo,t.UInt64), arg(state.lendFormState.lendMoney,t.UFix64), arg(expired,t.UInt64)],limit:limitNum});
        console.log(res2);
        let res3 = await fcl.tx(res2).onceSealed();
        console.log(res3);
        if(res3.statusString == 'SEALED') {
          lendInit();
          state.modalVisible = false;
          message.success('Rent success!');
        }else{
          state.modalVisible = false;
          state.confirmLendLoading = false;
          message.error('Rent error!');
        }
      } catch (error) {
        console.error(error);
        state.confirmLendLoading = false;
      }
    };

    const rentItem = t => {
      state.rentInfo = t;
      state.rentFormState.rentDay = 1;
      state.modalRentVisible = true;
      state.confirmRentLoading = false;
    };

    const confirmRent = async () =>{
      console.log(state.rentInfo);
      try {
        state.confirmRentLoading = true;
        let res1= await fcl.query({cadence: GET_BLOCK});
        const expired = state.rentFormState.rentDay*86400 + res1;
        console.log(res1);
        let res2 = await fcl.mutate({cadence: RENT,args: (arg,t) => [arg(Number(state.rentInfo.id),t.UInt64), arg(state.rentInfo.price,t.UFix64), arg(expired,t.UInt64), arg(state.rentInfo.fromAddress,t.Address)],limit:limitNum});
        console.log(res2);
        let res3 = await fcl.tx(res2).onceSealed();
        console.log(res3);
        if(res3.statusString == 'SEALED') {
          rentInit();
          state.modalRentVisible = false;
          message.success('Rent success!');
        }else{
          state.modalRentVisible = false;
          state.confirmRentLoading = false;
          message.error('Rent error!');
        }
      } catch (error) {
        console.error(error);
        state.confirmRentLoading = false;
        message.error('Rent error!');
      }
    };


    const logOut = () => {
      sessionStorage.removeItem('__account');
      sessionStorage.removeItem('CURRENT_USER');
      state.drawerVisible = false;
      window.location.reload()
    };

    const switchDashTo = async (t)=>{
      state.dashtab = t;
      state._dashLoading = true;
      if(t == 'mylend') {
        initMylend();
      }else if(t == 'myrent') {
        initMyrent();
      }
    }

    const initMylend = async () =>{
      console.log('initMylend');
      state._dashLoading = true;
      let res = await fcl.query({cadence: getLendIDs ,args: (arg,t) => [arg(state.user.addr,t.Address)]});
      const dashDetails1 = res.map(id=>{
        return{
          id: id,
          desc:'已租出'
        }
      });
      let res2 = await fcl.query({cadence: GET_IDS_WITH_PRICE,args: (arg,t) => [arg(state.user.addr,t.Address)]});
      console.log(res2);
      let res3= await fcl.query({cadence: GET_BLOCK});


      const dashDetails2 = Object.keys(res2).map(id=>{
        const t = (res2[id][Object.keys(res2[id])[0]] - res3) * 1000 +new Date().getTime();
        return {id:id,endTime:formatDate(new Date(t), 'yyyy-MM-dd hh:mm')}
      });
      state.dashDetails = [...dashDetails1,...dashDetails2];
      state._dashLoading = false;
    }

    const initMyrent = async () =>{ // dashboard rent 列表查询lent 信息
      state._dashLoading = true;
      let res = await fcl.query({cadence: getUser1 ,args: (arg,t) => [arg(state.user.addr,t.Address)]});
      console.log(res);
      const arr= [];
      Object.keys(res).forEach(key=>{
        arr.push(...res[key]);
      });
      console.log(arr);

      state.dashDetails = arr.map(id=>{
        return {id: id}
      });

      const proArr = [];
      state.dashDetails.forEach(item=>{
        const p = fcl.query({cadence: GET_EXPIRED ,args: (arg,t) => [arg(state.user.addr,t.Address), arg(item.id,t.UInt64)]});
        proArr.push(p);
      })
      const results = await Promise.all(proArr);
      console.log(results);
      results.forEach((time,index)=>{
        const t = new Date().getTime() + time *1000;
        state.dashDetails[index].endTime =  formatDate(new Date(t), 'yyyy-MM-dd hh:mm');
      });
      console.log(state.dashDetails);

      state.dashDetails.total = arr.length;
      state._dashLoading = false;
    }

    const settingItem = async (item)=>{
      state.tokenId = item.id;
      state.modalLentSettingVisible = true;
      state.confirmLentSettingLoading = false;
    }

    const setingPrice = async ()=>{
      console.log(state.tokenId)
      console.log(state.dashGamepool)
      if(state.lentSettingFormState.price <=0) return message.error('price error!');
      await state.dashGamepool.setPrice(state.tokenId,state.lentSettingFormState.price);
    }

    const setingLock = async ()=>{
      console.log(state.lentSettingFormState.lock);
      if(state.lentSettingFormState.lock!= 'true' && state.lentSettingFormState.lock!= 'false') return message.error('lock only true or false!');
      await state.dashGamepool.setRentLock(state.tokenId,state.lentSettingFormState.lock =='true'?true:false);
    }

    const setingMaxRentTime = async ()=>{
      console.log(state.lentSettingFormState.maxRentTime);
      if(!state.lentSettingFormState.maxRentTime) return message.error('maxRentTime error!');
      await state.dashGamepool.setMaxRentTime(state.tokenId,Math.round(state.lentSettingFormState.maxRentTime/1000));
    }

    const withDrawOpen = async ()=>{
      state.modalWithdrawVisible = true;
      state.withdrawLoading = false;
    }
    const withdrawConfirm = async ()=>{
      const index = state.gamePoolArr.findIndex(item=>item._name == state.nowNtfName);
      const gamepool = state.gamePoolArr[index];
      state.withdrawLoading = true;
      if(!state.withdrawFormState.number || state.withdrawFormState.number>state.walletProfit ||!state.walletProfit) {
        state.withdrawLoading = false;
        return  message.error('withdraw input error!');
      }
      await gamepool.withdrawBalance(state.withdrawFormState.number);
      state.modalWithdrawVisible = false;
      state.withdrawLoading = false;
    };
    

    return {
      ...toRefs(state),
      goTo,
      switchTo,
      switchDashTo,
      handleSortChange,
      searchDicshow,
      searchDicshow2,
      showDrawer,
      onClose,
      lendItem,
      confirmLend,
      rentItem,
      confirmRent,
      logOut,
      confirmSearch,
      searchOnChange,
      closeSearch,
      closeSearch2,
      settingItem,
      setingPrice,
      setingLock,
      setingMaxRentTime,
      withDrawOpen,
      withdrawConfirm
    };
  }
};
</script>

<style lang="less">
.container {
  .home {
    min-height: 100vh;
    background-image: rgb(255, 255, 255);
    box-sizing: border-box;
    display: flex;
    flex-direction: column;

    .header {
      box-shadow: rgb(4 17 29 / 25%) 0px 0px 8px 0px;
      max-width: 100vw;
      height: 72px;
      position: sticky;
      top: 0px;
      z-index: 110;
      transition: top 0.5s ease 0s;
      background-color: rgb(255, 255, 255);
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: rgba(4, 17, 29, 25%) 0px 0px 8px 0px;

      .title {
        font-size: 24px;
        font-weight: 600;
        display: flex;
        align-items: center;
        font-weight: 600;
        font-size: 30px;
        letter-spacing: 0px;
        color: rgb(4, 17, 29);

        img {
          height: 40px;
          margin-right: 6px;
          margin-left: 20px;
        }
      }

      .tabs {
        display: flex;
        flex: 1;
        margin-left: 4vw;

        .item {
          margin: 0 30px;
          transition: color 0.4s ease 0s, background-color 0.4s ease 0s;
          color: #707a83;
          font-size: 14px;
          font-weight: 600;
          position: relative;
          cursor: pointer;

          &.active {
            color: black;
            border-bottom: 2px solid #000000;
          }
        }
      }

      .select {
        flex: 0;
        margin-right: 20px;
        display: flex;

        .ntf-search {
          border: 1px solid #d9d9d9;
          width: 140px;
          margin-right: 10px;
          display: flex;
          align-items: center;
          color: rgba(0, 0, 0, 0.85);
          justify-content: space-between;
          padding: 0 10px;
          cursor: pointer;

          > span {
            flex:1;
          }

          > img {
            flex: 0 0 10px;
          }
        }
      }

      .contact {
        display: flex;
        font-size: 24px;

        > span,
        button,
        img {
          margin-right: 30px;
          cursor: pointer;
        }

        > img {
          height: 24px;
        }
      }
    }

    .collections {
      width: 100vw;
    }

    .content {
      flex: 0 0 35%;
      display: flex;
      flex-direction: column;
      justify-content: space-around;
      background: #F5F5F5;

      .detail_container {
        width: 90%;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 45px;
        margin-top: -171px;
        min-height: calc(100vh - 250px);
      }

      .ballet {
        display: flex;
        margin: 0 10%;
        margin-top: 10px;
        border: 1px solid #cbc7c7;
        border-radius: 6px;
        padding: 20px 20px;

        .info {
          flex: 1;
          text-align: center;
          display: flex;
          justify-content: center;
          align-items: center;
          font-weight: 600;
          color: #0fa377;
          font-size: 16px;

          span {
            margin-left: 5px;
            font-size: 12px;
          }
        }

        .myer {
          padding: 5px 10px;
          border: 1px solid rgba(78, 248, 93, 1);
          border-radius: 5px;
          font-size: 12px;
          font-weight: 700;
          cursor: pointer;
          background: linear-gradient(to right, #4DF75D, #0CA4C4);
          box-shadow: rgba(65, 231, 112, 30%) 0px 0px 4px 1px;
          border: none;
          color: #ffffff;
        }
      }


      .dashtab {
          display: flex;
          justify-content: center;
          align-items: center;
          height: 40px;
          margin-top: 20px;

        .mytab {
            height: 100%;
            border-top-left-radius: 10px;
            border-bottom-left-radius:10px;
            border: 1px solid #b5b2b2;
            line-height: 40px;
            font-size: 12px;
            width: 200px;
            text-align: center;
            font-weight: 600;
            cursor: pointer;

            &.tab2 {
              border-top-right-radius: 10px;
              border-bottom-right-radius: 10px;
              border-top-left-radius: 0;
              border-bottom-left-radius: 0;
              border-left: none;
            }

          &.active {
              background: linear-gradient(to right, #4DF75D, #0CA4C4);
              border: 1px solid #b5b2b2;
              border: none;
              color: #ffffff;
          }
        }
      }

      .nologin {
        text-align: center;
        font-weight: 600;
        font-size: 30px;
        letter-spacing: 0px;
        color: rgb(115 125 134);
        margin-top: 10%;
      }

      .title {
        margin-top: 40px;
        text-align: center;
        font-weight: 600;
        font-size: 22px;
        letter-spacing: 0px;
        color: rgb(4, 17, 29);
      }

      .sort {
        justify-content: end;
        display: flex;
        padding: 0 40px;
        margin-bottom: 20px;
      }

      .detail_total {
        text-align: left;
        padding-left: 4vw;
        font-size: 20px;
        color: #3f5e7a;
        margin-bottom: 10px;
      }

      .main-content {
        display: flex;
        flex-wrap: wrap;
        padding: 0 20px;
        justify-content: space-around;

        @media screen and (max-width: 1800px) {
          .item {
            flex: 0 0 30vw !important;
            height: 30vw !important;
          }
        }

        @media screen and (max-width: 1100px) {
          .item {
            flex: 0 0 46vw !important;
            height: 46vw !important;
          }
        }

        @media screen and (max-width: 800px) {
          .item {
            flex: 0 0 80vw !important;
            height: 80vw !important;
          }
        }

        .item {
          flex: 0 0 45vw;
          height: 45vw;
          border: 1px solid #e5e8eb;
          background-color: rgb(255, 255, 255);
          border-radius: 10px;
          cursor: pointer;
          margin-bottom: 20px;

          > img {
            width: 100%;
            height: 80%;
            object-fit: cover;
            border-radius: 10px;
          }

          .name {
            color: rgb(4, 17, 29);
            font-weight: 600;
            font-size: 16px;
            text-transform: none;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-top: 2px;
            text-align: center;
          }

          .name-by {
            margin-top: 2px;
            text-align: center;
            font-weight: 500;
            font-size: 14px;
            color: rgb(112, 122, 131);
          }

          .desc {
            margin: 20px 0px;
            text-align: center;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            padding: 0 10%;
            font-weight: 400;
            font-size: 16px;
            color: rgb(112, 122, 131);
          }
        }

        .min-item {
          flex: 0 0 200px;
          border: 1px solid #e5e8eb;
          background-color: rgb(255, 255, 255);
          border-radius: 10px;
          cursor: pointer;
          margin-bottom: 20px;
          box-shadow: #d8e7f5 0px 3px 10px 0px;
          position: relative;

          &:hover {
            transform: translate(0px, -5px);
            transition: 0.1s;
          }

          > img {
            width: 100%;
            object-fit: cover;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
          }

          .name {
            color: rgb(4, 17, 29);
            font-weight: 600;
            font-size: 16px;
            text-transform: none;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-top: 2px;
            text-align: center;
          }

          .tags {
            background-color: #f7f7f7;
            padding: 10px 0;

            .tag {
              display: flex;
              padding-left: 5px;
              color:#585656;
              font-weight: 600;
              font-size: 12px;
              justify-content: center;

              > div {
                color: #4caf50;
                font-weight: 500;
              }
            }
          }

          .desc {
            text-align: center;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            padding: 0 10%;
            font-weight: 400;
            font-size: 16px;
            color: rgb(112, 122, 131);
            margin-top: 10px;

            button {
              width: 100%;
            }
          }
        }
      }
    }
  }
}
.mask {
  background: rgba(0, 0, 0, 0.2);
  position: fixed;
  z-index: 999;
  height: 100vh;
  width: 100vw;
  right: 0;
  display: flex;
  flex-direction: column;
  top: 0;

  .search-form {
    width: 30vw;
    height: 34vw;
    transform: translate(-50%,-50%);
    position: absolute;
    left: 50%;
    top: 50%;
    background: #fff;
    border-radius: 2vw;
    padding: 2vw;

    .title {
      height: 5vw;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.6vw;
      font-weight: 500;
    }

    .input {
      width: 100%;
      background: #F0F0F0;
      border: navajowhite;
      line-height: 3;
      border-radius: 10px;
      height: 3vw;
    }

    .common-tips {
      display: flex;
      align-items: center;
      font-size: 16px;
      font-weight: 500;
      margin-top: 0.4vw;

      >img {
        height: 14px;
        width: 14px;
        margin-left: 2px;
      }

    }

    .common {
      display: flex;
      margin-top: 0.4vw;

      .item {
        border: 1px solid #F0F0F0;
        padding: 0px 8px;
        display: flex;
        align-items: center;
        font-size: 16px;
        border-radius: 5px;
        margin-right: 4px;
        font-weight: 500;
        cursor: pointer;

        > img {
          height: 20px;
          width: 20px;
          border-radius: 10px;
          margin-right: 6px;
        }
      }
    }

    .all-ntf {
      display: flex;
      flex-direction: column;
      margin-top: 1vw;
      cursor: pointer;

      .item {
        display: flex;
        align-items: center;
        padding: 5px 0;
        border-bottom: 1px solid #F0F0F0;

        &:hover {
          background: #f9f9f9;
        }

        >img {
          width: 20px;
          height: 20px;
          border-radius: 10px;
          margin-right: 10px;
        }

        .bag {
          .name {
            font-size: 16px;
            font-weight: 600;
          }
          
          .symbol {
            color: #7a7a7a;
            font-size: 12px;
          }
        }
      }

    }
  }

  .x {
    width: 36px;
    height: 36px;
    position: absolute;
    top: 44vw;
    left: 50%;
    font-weight: 500;
    border: 1px solid #ffffff;
    text-align: center;
    border-radius: 50%;
    color: #ffffff;
    font-size: 20px;
    line-height: 36px;
    transform: translate(-50%, -50%);
    cursor: pointer;
  }
}
.drawer {
  .title {
    display: flex;
    align-items: baseline;

    .online {
      margin-right: 8px;

      img {
        height: 30px;
        width: 30px;
        border-radius: 50%;
      }
    }

    a-collapse {
      flex: 1;
      width: 160px;
    }

    > .account {
      flex: 0 0 100px;
      white-space: nowrap;
      text-overflow: ellipsis;
      overflow: hidden;
      font-size: 14px;
      color: #707a83;
      margin-left: auto;
    }
  }

  .money {
    height: 120px;
    border: 1px solid #e5e8eb;
    border-radius: 10px;
    margin-top: 20px;
    overflow: hidden;

    .balance {
      height: 50px;
      font-weight: 500;
      font-size: 14px;
      color: rgb(112, 122, 131);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .btn {
      height: 70px;
      text-align: center;
      flex: 1;
      background: #2081e2;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
      font-weight: 600;
      color: #ffffff;
    }
  }
}

.footer {
  display: flex;
  justify-content: center;
  flex-direction: column;
  align-items: center;
  button {
    width: 240px !important;
    margin-bottom: 10px;
  }
}

.normal-btn {
  background-image: linear-gradient(to right, #4DF75D, #0CA4C4);
  border: none  !important;;
  border-radius: 4px !important;;
  box-shadow: rgba(65, 231, 112, 30%) 0px 0px 4px 1px;
  width: 80px !important;
  height: 30px !important;

  &:focus {
    background-image: linear-gradient(to right, #4DF75D, #0CA4C4);
    box-shadow: rgba(65, 231, 112, 30%) 0px 0px 4px 1px;
  }

  &:hover {
    background-image: linear-gradient(to right, #4DF75D, #0CA4C4);
    box-shadow: rgba(65, 231, 112, 30%) 0px 0px 4px 1px;
  }
}

.ant-btn-primary[disabled].normal-btn {
  background: #D6D5D5;
  box-shadow: none !important;
}

.ant-modal-content {
  border-radius: 15px !important;
}

.ant-modal-header {
  background: #0ca4c4 url('../assets/header_bg.png') no-repeat;
  background-size: 100% 100%;
  border-radius: 15px 15px 0 0;
  height: 120px;
  border-bottom: none;
}
.ant-modal-title {
  color: #ffffff;
  text-align: center;
  font-size: 24px;
  margin-top: 20px;
}
.ant-form-item-label {
  display: block;
  width: 80%;
  margin-left: 10%;
  text-align: left;

  label {
    font-weight: 600;
  }
}
.ant-form-item-control-wrapper {
  display: block;
  width: 80%;
  margin-left: 10%;

  input {
    border-radius: 6px;
  }
}
.ant-input:hover ,.ant-input:focus{
  border-color: #25c2a2 !important;
}

.ant-calendar-date {
  border-radius: 50%;
  border: none;
  color: #7C86A2;
  line-height: 24px;
}
.ant-calendar-selected-day .ant-calendar-date {
  background: linear-gradient(45deg, #0CA4C4, #4DF75D);
  color: #ffffff;
}
.ant-calendar-date-panel,.ant-calendar-month-select,.ant-calendar-year-select {
  color:#7C86A2;
}
.ant-calendar-today .ant-calendar-date {
  color: #0fa8bf;
}
.ant-calendar-input {
  color: #7C86A2;
}
.ant-calendar-footer-btn a {
  color: #0fa8bf;
}

.ant-calendar .ant-calendar-ok-btn {
  background: linear-gradient(to right, #4DF75D, #0CA4C4);
  border-radius: 4px !important;;
  box-shadow: rgba(65, 231, 112, 30%) 0px 0px 4px 1px;
  width: 60px !important;
  border: none;
  color: #ffffff;
}
.ant-calendar {
  font-size: 12px;
}
</style>
