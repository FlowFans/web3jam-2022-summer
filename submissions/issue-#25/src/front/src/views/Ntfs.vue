<template>
  <div class="com_ntfs">
    <div class="list">
      <div class="ntfs title"><span>721 NFT</span></div>
      <div class="ntfs" v-for="(n, index) in ntfs721List" :key="index">
        <span>{{ n.ntfs }}</span>
        <a-button
          size="omitted"
          class="give"
          type="danger"
          @click="giveAway721(index)"
        >
          <RetweetOutlined />
          转让
        </a-button>
      </div>
      <div v-if="!ntfs721List || ntfs721List.length == 0">
        You don't have 721 NFTs
      </div>
      <div class="ntfs title"><span>1155 NFT</span></div>
      <div
        class="ntfs"
        v-for="(n, index) in nft1155list"
        :key="index"
      >
        <span>{{ n.name }}</span>
        <span>{{ n.number }}</span>
        <a-button
          size="omitted"
          class="give"
          type="danger"
          @click="giveAway1155(index)"
        >
          <RetweetOutlined />
          转让
        </a-button>
      </div>
      <div v-if="!nft1155list || nft1155list.length == 0">
        You don't have 1155 NFTs
      </div>
    </div>

    <a-modal
      style="position:relative"
      title="Transfer"
      :visible="visible"
      :confirm-loading="confirmLoading"
      @ok="handleOk"
      @cancel="handleCancel"
    >
      <a-input
        style="margin-bottom: 10px;"
        v-model:value="userId"
        placeholder="Enter the ID of the person you want to transfer "
      ></a-input>
      <a-input
        v-model:value="number"
        v-if="type == 1155"
        placeholder="Enter number you want to transfer "
      >
      </a-input>
    </a-modal>
  </div>
</template>

<script>
import { defineComponent, onMounted } from 'vue';
import { reactive, toRefs } from 'vue';
import { RetweetOutlined } from '@ant-design/icons-vue';
import { getUser } from '../store/user';
import { notification } from 'ant-design-vue';
import { utils } from 'web3';
import { ethers } from 'ethers';

export default defineComponent({
  name: 'comDfts',
  data() {
    return {
      name: 'Wise.Wrong'
    };
  },
  setup() {
    const state = reactive({
      iconMultShow: false,
      user: null,
      visible: false,
      userId: '',
      confirmLoading: false,
      giveNtfs: '',
      nft1155list: [],
      ntfs721List: [],
      number: '',
      type: ''
    });
    state.user = getUser();

    onMounted(() => {
      get721nfts();
      get1155nfts();
    });

    const getNftsFinalData = str => {
      return window.ethereum.request({
        method: 'eth_call',
        params: [
          {
            to: '0x89f24318b625f77643346fa5acc7c09a12adc891',
            data: '0x8d5b527a' + str
          }
        ]
      });
    };

    const get721nfts = async () => {
      try {
        const res = await window.ethereum.request({
          method: 'eth_call',
          params: [
            {
              to: '0x89f24318b625f77643346fa5acc7c09a12adc891',
              data:
                '0xc5da2a52000000000000000000000000' +
                state.user.accountId.slice(2)
            }
          ]
        });
        if (res.length <= 130) {
          state.ntfs721List = [];
          return;
        }
        const _ntfsList = [];
        let nftsStr = res.slice(130, res.length);
        const ntfs16Arr = [];
        while (nftsStr.length >= 64) {
          _ntfsList.push({ ntfs16str: nftsStr.slice(0, 64) });
          ntfs16Arr.push(nftsStr.slice(0, 64));
          nftsStr = nftsStr.slice(64, nftsStr.length);
        }
        const pArr = [];
        ntfs16Arr.forEach(item => {
          pArr.push(getNftsFinalData(item));
        });
        const allResult = await Promise.all(pArr);

        allResult.forEach((res, index) => {
          const number = utils.toDecimal('0x' + res.slice(129, 194));
          const str = res.slice(194, 194 + number * 2);
          _ntfsList[index].ntfs = utils.toAscii('0x' + str);
        });
        console.log(_ntfsList);
        state.ntfs721List = _ntfsList;
      } catch (error) {
        notification.error({
          message: 'fetch nfts data error',
          description: error.message
        });
      }
    };

    const get1155nfts = async () => {
      try {
        const provider = new ethers.providers.Web3Provider(window.ethereum);
        //const signer = provider.getSigner();
        const abi = [
          'function name() public view returns (string memory)',
          'function blitok_vb(uint id) public view returns(uint,bytes)',
          'function createrBliTok1155(bytes memory data) public',
          'function balanceOf(address account, uint256 id) public view returns (uint256)',
          'function transfer(address to,uint256 id,uint256 amount,bytes memory data) public'
        ];
        const address = '0xa393533064297f0878c850f532a305ce0d3d49aa';
        const blitok1155 = new ethers.Contract(address, abi, provider);
        const pArr = [];
        const _nft1155list = [];

        /* for (let i = 0; i <= 20; i++) {
        const numberRes = await blitok1155.balanceOf(state.user.accountId, i);
        const number = utils.toDecimal(numberRes._hex);
        if (number > 0) {
          const nameRes = await blitok1155.blitok_vb(i);
          arr.push({
            index: i,
            number: number,
            name: utils.toAscii(nameRes[1])
          });
        }
      }
      console.log(arr); */

        for (let i = 0; i <= 30; i++) {
          pArr.push(blitok1155.balanceOf(state.user.accountId, i));
        }
        const allResult = await Promise.all(pArr);
        console.log(allResult);
        allResult.forEach((item, index) => {
          const number = utils.toDecimal(item._hex);
          if (number > 0) {
            _nft1155list.push({ index: index, number: number });
          }
        });
        const pArr2 = [];
        for (let j = 0; j < _nft1155list.length; j++) {
          pArr2.push(blitok1155.blitok_vb(_nft1155list[j].index));
        }
        const allResult2 = await Promise.all(pArr2);
        console.log(allResult2);
        allResult2.forEach((item, index) => {
          _nft1155list[index].name = utils.toAscii(item[1]);
        });
        console.log(_nft1155list);
        state.nft1155list = _nft1155list;
      } catch (error) {
        console.error(error);
        notification.error({
          message: 'fetch nfts data error',
          description: error.message
        });
      }
    };
    const giveAway721 = index => {
      state.visible = true;
      state.type = 721;
      state.userId = '';
      state.number = '';
      state.confirmLoading = false;
      state.giveNtfs = state.ntfs721List[index].ntfs16str;
    };
    const giveAway1155 = index => {
      state.visible = true;
      state.type = 1155;
      state.userId = '';
      state.number = '';
      state.confirmLoading = false;
      state.giveNtfs = state.nft1155list[index];
    };
    const handleCancel = () => {
      state.visible = false;
    };
    const handleOk = () => {
      if (!state.userId) {
        notification.error({
          message: 'Enter a ID of the person you want to transfer.'
        });
        return;
      }
      state.userId = state.userId.toLowerCase();
      if (!utils.isAddress(state.userId)) {
        notification.error({
          message:
            'The ID format is incorrect. It should be hex format, you can see it in your profile.'
        });
        return;
      }
      if (state.userId.indexOf('0x') == 0) {
        state.userId = state.userId.slice(2, state.userId.length);
      }
      if (state.type == 721) {
        handle721();
      } else if (state.type == 1155) {
        handle1155();
      }
    };
    const handle721 = async () => {
      state.visible = false;
      const data = `0xa9059cbb000000000000000000000000${state.userId}${state.giveNtfs}`;
      state.confirmLoading = true;
      window.ethereum
        .request({
          method: 'eth_sendTransaction',
          params: [
            {
              from: state.user.accountId,
              to: '0x89f24318b625f77643346fa5acc7c09a12adc891',
              data: data
            }
          ]
        })
        .then(() => {
          state.confirmLoading = false;
          state.visible = false;
          notification.success({
            message: 'transfer success!',
            description: 'It will take some time for NFTs to arrive。'
          });
        })
        .catch(err => {
          state.confirmLoading = false;
          state.visible = false;
          notification.error({
            message: 'transfer error',
            description: err
          });
        });
    };
    const handle1155 = async () => {
      state.visible = false;
      if (!state.number) {
        notification.error({
          message: 'Enter number you want to transfer.'
        });
        return;
      }
      state.confirmLoading = true;
      const provider = new ethers.providers.Web3Provider(window.ethereum);
      const signer = provider.getSigner();
      const abi = [
        'function name() public view returns (string memory)',
        'function blitok_vb(uint id) public view returns(uint,bytes)',
        'function createrBliTok1155(bytes memory data) public',
        'function balanceOf(address account, uint256 id) public view returns (uint256)',
        'function transfer(address to,uint256 id,uint256 amount,bytes memory data) public'
      ];
      const address = '0xa393533064297f0878c850f532a305ce0d3d49aa';
      const blitok1155_rw = new ethers.Contract(address, abi, signer);
      blitok1155_rw
        .transfer(state.userId, state.giveNtfs.index, state.number, '0x00')
        .then(() => {
          state.confirmLoading = false;
          state.visible = false;
          notification.success({
            message: 'transfer success!',
            description: 'It will take some time for NFTs to arrive。'
          });
        })
        .catch(err => {
          state.confirmLoading = false;
          state.visible = false;
          notification.error({
            message: 'transfer error',
            description: err
          });
        });
    };

    return {
      ...toRefs(state),
      giveAway721,
      giveAway1155,
      handleCancel,
      handleOk
    };
  },

  components: {
    RetweetOutlined
  }
});
</script>
<style lang="less">
.com_ntfs {
  .list {
    .ntfs {
      height: 60px;
      padding: 10px;
      font-size: 18px;
      line-height: 40px;
      cursor: pointer;
      //border-bottom: 1px solid #f0f0f0;
      display: flex;
      align-items: center;

      &.title {
        text-align: center;
        color: grey;
        border-bottom: 1px solid #f0f0f0;
        background: #f0f0f0;

        &:hover {
          border: none;
          background: none;
          background: #f0f0f0;
        }
      }

      &:hover {
        border: 1px solid #f0f0f0;
        background: #fdfcfc;
      }

      span {
        flex: 1;
      }

      .give {
        width: 80px;
        flex: 0 0 80px;
        font-size: 12px;
        font-weight: 500;
        height: 24px;
      }
    }
  }
}
</style>
