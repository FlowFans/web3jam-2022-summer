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
            v-bind:class="[{ active: tab == 'dashboard' }]"
            @click="switchTo('dashboard')"
          >
            MY DASHBOARD
          </div>
        </div>
        <div class="contact">
          <a-button v-if="!isLogin" class="login" type="primary" @click="goTo"
            >Connect wallet</a-button
          >
          <!-- <img v-if="isLogin" src="../assets/person.svg"/> -->
          <img v-if="isLogin" src="../assets/wallet.svg" @click="showDrawer" />
        </div>
      </div>
      <img class="collections" src="./../assets/collections.webp" />
      <div class="content" v-if="tab == 'rent'">
        <div class="nologin" v-if="!isLogin">
          You don't have any NFTs to rent
        </div>
        <div v-if="isLogin">
          <div class="title">Explore Rent Collections</div>
          <div class="main-content" v-if="!rentDetails && !_rentLoading">
            <div
              v-for="(item, index) in rentGameList"
              :key="index"
              class="item"
              @click="showRentGameDeail(item._address, index)"
            >
              <img :src="lendGameImg[index]" />
              <div class="name">{{ item._name }}</div>
              <div class="name-by">
                <span style="color: #2081e2;">{{ item._symbol }}</span>
              </div>
            </div>
          </div>
          <div class="detail_bag" v-if="rentDetails && !_rentLoading">
            <div class="detail_total">{{ rentDetails.length }} results</div>
            <div class="main-content">
              <div
                v-for="(id, index) in rentDetails"
                :key="index"
                class="min-item"
              >
                <img :src="lendImg[index]" />
                <div class="name">{{ id }}</div>
                <div class="desc">
                  <a-button class="btn" type="primary" @click="rentItem(id, index)"
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
        <div v-if="isLogin">
          <div class="title">Explore Lend Collections</div>
          <div class="main-content" v-if="!lendDetails && !_lendLoading">
            <div
              v-for="(item, index) in lendGameList"
              :key="index"
              class="item"
              @click="showLendGameDeail(item, index)"
            >
              <img :src="lendGameImg[index]" />
              <div class="name">{{ item._name }}</div>
              <div class="name-by">
                <span style="color: #2081e2;">{{ item._symbol }}</span>
              </div>
            </div>
          </div>
          <div class="detail_bag" v-if="lendDetails && !_lendLoading">
            <div class="detail_total">{{ lendDetails.length }} results</div>
            <div class="main-content">
              <div
                v-for="(id, index) in lendDetails"
                :key="index"
                class="min-item"
              >
                <img :src="lendImg[index]" />
                <div class="name">{{ id }}</div>
                <div class="desc">
                  <a-button class="btn" type="primary" @click="lendItem(id, index)"
                    >Lend</a-button
                  >
                </div>
              </div>
            </div>
          </div>
          <div style="text-align: center;padding: 40px 0;" v-if="_lendLoading">
            <a-spin />
          </div>
        </div>
      </div>
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
      :confirmLoading="confirmRentLoading"
      @cancel="modalRentVisible = false"
    >
      <a-form :model="rentFormState">
        <a-form-item label="how many day you want rent" name="rentDay">
          <a-input
            v-model:value="rentFormState.rentDay"
            type="number"
            placeholder="fill nft price"
          />
        </a-form-item>
      </a-form>
      <div class="footer">
        <a-button
          :disabled="approveRentDisabled"
          type="primary"
          :loading="approveRentLoading"
          @click="approveRent(id, index)"
          >Approve</a-button
        >
        <a-button
          :disabled="confirmRentDisabled"
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
          />
        </a-form-item>
        <a-form-item label="lend how many nft a day" name="lendMoney">
          <a-input
            v-model:value="lendFormState.lendMoney"
            placeholder="fill nft price"
          />
        </a-form-item>
      </a-form>
      <div class="footer">
        <a-button
          :disabled="approveLendDisabled"
          type="primary"
          :loading="approveLendLoading"
          @click="approveLend(id, index)"
          >Approve</a-button
        >
        <a-button
          :disabled="confirmLendDisabled"
          type="primary"
          :loading="confirmLendLoading"
          @click="confirmLend(id, index)"
          >Confirm</a-button
        >
      </div>
    </a-modal>
  </div>
</template>

<script>
import { reactive, toRefs, onMounted } from 'vue';
import detectEthereumProvider from '@metamask/detect-provider';
import { message } from 'ant-design-vue';
import { getUser, setUser, resetUser } from '../store/user';
import { ethers } from 'ethers';
import { LogoutOutlined } from '@ant-design/icons-vue';
import { axsAbi } from './abi/cryptoSharing';
import { axsLendAbi } from './abi/ERC9999EnumerableAbi';
import { USDTAbi } from './abi/ERC20Abi';
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
      drawerVisible: false,
      expandIconPosition: 'right',
      provider: null,
      modalVisible: false,
      _lendLoading: false,
      _rentLoading: false,
      lendFormState: {
        lendTime: null,
        lendMoney: null
      },
      modalRentVisible: false,
      rentFormState: {
        rentDay: null
      },
      sortArr: [
        { value: '1', label: 'PRICE: HIGH TO LOW' },
        { value: '2', label: 'PRICE: LOW TO HIGH' }
      ],
      axsRentAddress: [
        '0x15bEFCB648Ff05a79F7c8568975d57b518898284',
        '0xD5329adD2dC1F13A8a6bc2f91642e85051D5C9A8',
        '0x130B122201e374E4CC37B77C3D6d12BC5d98d50f'
      ],
      rentImg: [
        '../assets/icon1.jpeg',
        '../assets/icon2.jpeg',
        '../assets/icon3.jpeg'
      ],
      rentList: [{ id: 1 }, { id: 2 }, { id: 3 }],
      /* lend 信息  */

      rentGameList: null,
      axsLendNFTAddress: [
        '0xd12cD2beB0E819f6c4c9f535f5Be8811056c3b4f',
        '0x1519E71F0dD0AE9E02141822c7b28d5eDEF3A995',
        '0x6d6053E4E08E3B67306f379736933e1035863025'
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
    });

    onMounted(() => {
      state.provider = new ethers.providers.Web3Provider(window.ethereum);
      authUser();
      init();
      onNetworkChange();
      window.ethereum.on('accountsChanged', handleAccountsChanged);
    });
    const onNetworkChange = ()=> {

    };
    const handleAccountsChanged = (accounts) => {
      console.log(accounts);
      if(accounts && accounts.length>0) {
        message.info(`Your account changed to ${accounts[0]}`);
        const accountId = accounts[0];
        state.user.accountId = accountId;
        init();
      }
    }

    //const router = useRouter();
    // get started
    const goTo = async () => {
      const provider = await detectEthereumProvider();
      console.log(provider);
      if (provider) {
        const accounts = await window.ethereum.request({
          method: 'eth_requestAccounts'
        });
        const accountId = accounts[0];
        console.log(accountId);
        setUser({ accountId, authTime: new Date().getTime(), isLogin: true });
        authUser();
      } else {
        message.warning('You should install MetaMask first! ');
      }
    };

    const authUser = async () => {
      state.user = getUser();
      console.log('-----authUser--------');
      console.log(state.user);
      if (!state.user.accountId) {
        state.user.isLogin = false;
        state.isLogin = false;
      } else {
        setUser({ authTime: new Date().getTime(), isLogin: true });
        state.isLogin = true;
      }
    };
    const switchTo = t => {
      state.tab = t;
      console.log(t);
      init();
    };

    const init = () => {
      if (state.tab == 'lend') {
        lendInit();
      } else if (state.tab == 'rent') {
        rentInit();
      }
    };

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
      state.rentDetails = null;
      state._rentLoading = true;
      state.rentGameList = null;
      const gameArr = state.axsRentAddress.map(addr => {
        const provider = new ethers.providers.Web3Provider(window.ethereum);
        const providerSign = provider.getSigner();
        return new ethers.Contract(addr, axsLendAbi, providerSign);
      });
      const gameResults = await Promise.all(gameArr);
      for (let i = 0; i < gameResults.length; i++) {
        gameResults[i]._address = state.axsRentAddress[i];
        gameResults[i]._name = await gameResults[i].name();
        gameResults[i]._symbol = await gameResults[i].symbol();
      }
      state.rentGameList = gameResults;
      state._rentLoading = false;
      console.log(gameResults);
    };
    const showRentGameDeail = async (_address, index) => {
      state.rentAddressIndex = index;
      state._rentLoading = true;
      state.rentDetails = null;
      state.lendInfo = {};
      const provider = new ethers.providers.Web3Provider(window.ethereum);
      console.log(_address);
      const providerSign = provider.getSigner();
      const axsContract = new ethers.Contract(_address, axsAbi, providerSign);
      const total = (await axsContract.totalSupply()).toNumber();
      console.log(total);
      const pArr = [];
      for (let i = 0; i < total; i++) {
        pArr.push(axsContract.tokenByIndex(i));
      }
      console.log(pArr);
      const idResults = await Promise.all(pArr);
      console.log(idResults);
      const ids = idResults.map(item => item.toNumber());
      console.log(ids);
      state.rentDetails = ids;
      state._rentLoading = false;
    };
    // now
    const lendInit = async () => {
      state.lendDetails = null;
      state._lendLoading = true;
      state.lendGameList = null;
      const gameArr = state.axsLendNFTAddress.map(addr => {
        const provider = new ethers.providers.Web3Provider(window.ethereum);
        return new ethers.Contract(addr, axsLendAbi, provider);
      });
      const gameResults = await Promise.all(gameArr);

      for (let i = 0; i < gameResults.length; i++) {
        gameResults[i]._address = state.axsLendNFTAddress[i];
        gameResults[i]._name = await gameResults[i].name();
        gameResults[i]._symbol = await gameResults[i].symbol();
        //gameResults[i]._totalSupply =  (await gameResults[i].totalSupply()).toNumber();
      }
      state.lendGameList = gameResults;
      state._lendLoading = false;
      console.log(gameResults);
    };
    const showLendGameDeail = async (axsContract, index) => {
      console.log(index);
      state._lendLoading = true;
      state.lendDetails = null;
      state.lendAddressIndex = index;
      state.lendInfo = {};
      console.log(axsContract);
      console.log(state.user.accountId);
      await updateAccount();
      const totalRes = await axsContract.balanceOf(state.user.accountId); // 游戏里有几个物品
      const total = totalRes.toNumber();
      console.log(total);
      const pArr = [];
      for (let i = 0; i < total; i++) {
        pArr.push(axsContract.tokenOfOwnerByIndex(state.user.accountId, i));
      }
      console.log(pArr);
      const idResults = await Promise.all(pArr);
      console.log(idResults);
      const ids = idResults.map(item => item.toNumber());
      console.log(ids); //  nft 的 id */
      state.lendDetails = ids;
      state._lendLoading = false;
    };
    const lendItem = (tokenId) => {
      console.log(tokenId); // 10002
      state.tokenId = tokenId;
      state.lendFormState.lendTime = null;
      state.lendFormState.lendMoney = 0;
      state.modalVisible = true;
      state.confirmLendDisabled = true;
      state.approveLendDisabled = false;
      state.approveLendLoading = false;
      state.confirmLendLoading = false;
    };
    const approveLend = async () => {
      const now = new Date().getTime();
      const lendTime = state.lendFormState.lendTime.valueOf();
      if (lendTime <= now)
        return message.error('lend date must be greater than now!');
      if (!state.lendFormState.lendMoney)
        return message.error('price require!');
      const lendMoney = Math.round(state.lendFormState.lendMoney / 86400) + 1;
      const provider = new ethers.providers.Web3Provider(window.ethereum);
      const providerSign = provider.getSigner();

      const axsAddress = state.axsRentAddress[state.lendAddressIndex]; // "0x20366D9EEDFFFA1aF4f7Cf0394D77c772258a8D0";
      const axsNFTAddress = state.axsLendNFTAddress[state.lendAddressIndex]; //  "0x2f359E67aFe0b0A11D7715bDDe889211C518828f"

      const axsContract = new ethers.Contract(axsAddress, axsAbi, providerSign);
      const axsNFTContract = new ethers.Contract(
          axsNFTAddress,
          axsLendAbi,
          providerSign
      );
      if(state.axsNFTContractMap[axsNFTAddress]) {
        console.log(axsNFTAddress+' axsNFTContract 已存在');
      }else{
        const filterFrom = axsNFTContract.filters.Approval(providerSign.address);
        axsNFTContract.on(filterFrom, (from/* from, to, amount, event */) => {
          console.log(from);
          state.approveLendLoading = false;
          state.approveLendDisabled = true;
          state.confirmLendDisabled = false;
          message.success('Approve success, please confirm lend!');
          state.lendInfo[state.tokenId].tokenId = state.tokenId;
          state.lendInfo[state.tokenId].lendTime = Math.round(lendTime / 1000);
          state.lendInfo[state.tokenId].lendMoney = lendMoney;
        });
        state.lendInfo[state.tokenId] ={axsContract:axsContract,axsNFTContract:axsNFTContract};
        state.axsNFTContractMap[axsNFTAddress] = axsNFTContract;
      }
      state.approveLendLoading = true;
      axsNFTContract
        .approve(axsAddress, state.tokenId)
        .then(result => {
          console.log(result);
        })
        .catch(err => {
          console.error(err);
          state.approveLendLoading = false;
          state.modalVisible = false;
          message.error('Approve error!');
        });
    };

    const confirmLend = () => {
      state.confirmLendLoading = true;
      state.lendInfo[state.tokenId].axsContract
        .lendNFT(
          state.lendInfo[state.tokenId].tokenId,
          state.lendInfo[state.tokenId].lendTime,
          state.lendInfo[state.tokenId].lendMoney
        )
        .then(res => {
          console.log(res);
          state.confirmLendLoading = false;
          state.modalVisible = false;
          message.success('lend success!');
        })
        .catch(err => {
          state.modalVisible = false;
          console.error(err);
          message.error('lend error!');
        });
    };

    const rentItem = tokenId => {
      state.tokenId = tokenId;
      state.rentFormState.rentDay = 1;
      state.modalRentVisible = true;
      state.confirmRentDisabled = true;
      state.approveRentDisabled = false;
      state.approveRentLoading = false;
      state.confirmRentLoading = false;
    };

    const approveRent = async () =>{
      if (!state.rentFormState.rentDay || state.rentFormState.rentDay <= 0)
        return message.error('rent day error!');
      const rentDay = state.rentFormState.rentDay;
      const USDT = '0x535312676a7867D958826960B82Dc1C6F326FC74';
      const provider = new ethers.providers.Web3Provider(window.ethereum);
      const providerSign = provider.getSigner();
      const USDTContract = new ethers.Contract(USDT, USDTAbi, providerSign);
      console.log(state.rentAddressIndex);
      const axsContract = new ethers.Contract(
        state.axsRentAddress[state.rentAddressIndex],
        axsAbi,
        providerSign
      );
      console.log(state.rentInfo);

      if(state.USDTContractMap[USDT]) {
        console.log(USDT+' USDTContract 已存在');
      }else{
        const filterFrom = USDTContract.filters.Approval(providerSign.address);
        USDTContract.on(filterFrom, (from/* from, to, amount, event */) => {
          console.log(from);
          state.approveRentLoading = false;
          state.approveRentDisabled = true;
          state.confirmRentDisabled = false;
          message.success('Approve success, please confirm rent!');
          state.rentInfo[state.tokenId].nowtime = Math.floor(new Date().getTime() / 1000) - 60;
          state.rentInfo[state.tokenId].rentDay = rentDay;
          state.USDTContractMap[USDT] = USDTContract;

        });
        state.rentInfo[state.tokenId] ={axsContract:axsContract,USDTContract:USDTContract};
      }

      const _price = (await axsContract._prices(state.tokenId)).toNumber();
      const price = _price * rentDay * 3600 * 24;
      state.approveRentLoading = true;
      USDTContract.approve(state.axsRentAddress[state.rentAddressIndex], price)
        .then(result => {
          console.log(result);
        })
        .catch(err => {
          state.approveLendLoading = false;
          state.modalRentVisible = false;
          console.error(err);
          message.error('approve error!');
        });

    };
    const confirmRent = async () =>{
      const time = state.rentInfo[state.tokenId].nowtime + state.rentInfo[state.tokenId].rentDay * 3600 * 24;
      state.confirmRentLoading = true;
      state.rentInfo[state.tokenId].axsContract
              .rentNFT(state.tokenId, time)
              .then(res => {
                console.log(res);
                state.confirmRentLoading = false;
                message.success('rent success!');
                state.modalRentVisible = false;
              })
              .catch(err => {
                state.confirmRentLoading = false;
                state.modalRentVisible = false;
                console.error(err);
                message.error('rent error!');
              });
    };


    const logOut = () => {
      resetUser();
      state.user = null;
      state.isLogin = false;
      state.drawerVisible = false;
    };

    const updateAccount = async () => {
      const accounts = await window.ethereum.request({
        method: 'eth_requestAccounts'
      });
      const accountId = accounts[0];
      console.log(accountId);
      state.user.accountId = accountId;
    };

    return {
      ...toRefs(state),
      goTo,
      switchTo,
      handleSortChange,
      showDrawer,
      onClose,
      showLendGameDeail,
      lendItem,
      confirmLend,
      showRentGameDeail,
      rentItem,
      confirmRent,
      logOut,
      approveLend,
      approveRent
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

        .item {
          padding: 0 30px;
          height: 72px;
          line-height: 72px;
          transition: color 0.4s ease 0s, background-color 0.4s ease 0s;
          color: #707a83;
          font-size: 15px;
          font-weight: 600;
          position: relative;
          cursor: pointer;

          &.active {
            color: black;
          }

          &.active::after {
            background-color: rgb(32, 129, 226);
            bottom: 0%;
            content: '';
            display: block;
            height: 4px;
            left: 0px;
            position: absolute;
            width: 100%;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
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
        font-size: 40px;
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
          height: 260px;
          border: 1px solid #e5e8eb;
          background-color: rgb(255, 255, 255);
          border-radius: 10px;
          cursor: pointer;
          margin-bottom: 20px;
          box-shadow: #d8e7f5 0px 3px 10px 0px;

          &:hover {
            transform: translate(0px, -5px);
            transition: 0.1s;
          }

          > img {
            width: 100%;
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

            button {
              width: 100%;
            }
          }
        }
      }
    }
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
  justify-content: end;
  button {
    margin: 0 10px;
  }
}

.btn {
  background-image: linear-gradient(to right, #4DF75D, #0CA4C4) !important;
  border: none  !important;;
  border-radius: 6px !important;;
  box-shadow: rgb(65 231 112 / 30%) 0px 0px 4px 1px !important;;
}
</style>
