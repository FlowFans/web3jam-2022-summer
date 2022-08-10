<template>
  <div class="com_create">
    <div class="title">Create Your NFT</div>
    <p>
      Create your NFT (your own storefronts), upload digital creations,
      configure your royalty, and sell NFTs to your fans - all.
    </p>

    <div class="content">
      <div class="create nft">
        <CodeSandboxOutlined style="fontSize:40px;color:rgb(187, 187, 187);" />
        <span class="text">Create your 721 NFT</span>
        <a-button type="primary" @click="showForm(721)">Create</a-button>
      </div>
      <div class="nft create">
        <ChromeOutlined style="fontSize:40px;color:rgb(187, 187, 187);" />
        <span class="text">Create your 1155 NFT</span>
        <a-button type="primary" @click="showForm(1155)">Create</a-button>
      </div>
    </div>
    <a-modal
      title="Create"
      :visible="visible"
      :footer="null"
      @cancel="handleCancel"
    >
      <a-form
        class="create-form"
        :model="form"
        @submit.prevent="submitForm"
        :label-col="{ span: 5 }"
        :wrapper-col="{ span: 18 }"
      >
        <a-form-item label="Name">
          <a-input
            v-model:value="form.name"
            placeholder="Example: Treasures of the Sea"
          >
          </a-input>
        </a-form-item>
        <!-- <a-form-item label="Description">
          <a-textarea
            v-model:value="form.desc"
            placeholder="Provide a description for your store. Markdown syntax is supported."
          >
          </a-textarea>
        </a-form-item> -->
        <a-form-item :wrapper-col="{ span: 6, offset: 18 }">
          <a-button type="primary" :loading="loading" html-type="submit"
            >Submit</a-button
          >
        </a-form-item>
      </a-form>
    </a-modal>
  </div>
</template>

<script>
import { defineComponent, onMounted } from 'vue';
import { reactive, toRefs } from 'vue';
import { getUser } from '../store/user';
import { CodeSandboxOutlined, ChromeOutlined } from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';
import { notification } from 'ant-design-vue';
import { utils } from 'web3';
import { ethers } from 'ethers';

export default defineComponent({
  name: 'comDfts',
  data() {
    return {};
  },
  //props: { list: { type: Object } },
  setup() {
    const state = reactive({
      user: null,
      visible: false,
      loading: false,
      type: null,
      form: {
        name: ''
      }
    });
    state.user = getUser();

    onMounted(() => {});

    const showForm = type => {
      state.visible = true;
      state.type = type;
      state.form.name = '';
      state.loading = false;
    };
    const submitForm = () => {
      const { name } = state.form;
      if (name.trim() == '') {
        return message.warning('please enter user name！');
      } else if (name.length > 32) {
        return message.warning('name cannot exceed 32 characters');
      } else if (/^[0-9]*$/.test(name)) {
        return message.warning('name cannot be a pure number');
      } else if (!/^[a-zA-Z0-9]+$/g.test(name)) {
        return message.warning('name can only consist of letters and numbers');
      }
      if (state.type == '721') {
        create721(name);
      } else if (state.type == '1155') {
        create1155(name);
      }
    };
    const handleCancel = () => {
      state.visible = false;
    };
    const create1155 = name => {
      const hexName = utils.toHex(String(name));
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
      state.loading = true;
      blitok1155_rw
        .createrBliTok1155(hexName)
        .then(() => {
          state.loading = false;
          state.visible = false;
          notification.success({
            message: 'create success!',
            description: `create nft ${name} success! you can see your new nft in your profile.`
          });
        })
        .catch(err => {
          state.loading = false;
          state.visible = false;
          notification.error({
            message: 'create error',
            description: err
          });
        });
    };
    const create721 = name => {
      let hexName = utils.toHex(String(name));
      let hexNameLengNum = utils.toHex(String(name.length));

      hexName = hexName.slice(2, hexName.length); //去除开头的0x
      hexNameLengNum = hexNameLengNum.slice(2, hexNameLengNum.length); //去除开头的0x
      let data;
      if (hexNameLengNum.length == 1) {
        data =
          '0xb69a30ca0000000000000000000000000000000000000000000000000000000000000020000000000000000000000000000000000000000000000000000000000000000' +
          hexNameLengNum;
      } else if (hexNameLengNum.length == 2) {
        data =
          '0xb69a30ca000000000000000000000000000000000000000000000000000000000000002000000000000000000000000000000000000000000000000000000000000000' +
          hexNameLengNum;
      } else {
        console.error(hexNameLengNum + 'error');
      }

      const num = hexName.length;
      let str =
        '0000000000000000000000000000000000000000000000000000000000000000';
      str = str.slice(num, str.length);
      str = `${hexName}${str}`;
      data = `${data}${str}`;
      state.loading = true;

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
          state.loading = false;
          state.visible = false;
          notification.success({
            message: 'create success!',
            description: `create nft ${name} success! you can see your new nft in your profile.`
          });
        })
        .catch(err => {
          state.loading = false;
          state.visible = false;
          notification.error({
            message: 'create error',
            description: err
          });
        });
    };
    return {
      ...toRefs(state),
      showForm,
      submitForm,
      handleCancel
    };
  },

  components: {
    CodeSandboxOutlined,
    ChromeOutlined
  }
});
</script>
<style lang="less">
.com_create {
  .title {
    font-size: 26px;
    font-weight: 500;
    margin-top: 16px;
    margin-right: 32px;
    margin-bottom: 6px;
  }

  > p {
    font-size: 15px;
    font-weight: 400;
    color: rgb(53, 56, 64);
  }
  .content {
    display: flex;

    .create {
      padding-top: 60px;
    }

    .nft {
      background-color: rgb(255, 255, 255);
      border-radius: 4px;
      box-shadow: rgba(0, 0, 0, 14%) 0px 2px 2px 0px,
        rgba(0, 0, 0, 12%) 0px 1px 5px 0px, rgba(0, 0, 0, 20%) 0px 3px 1px -2px;
      height: 300px;
      margin: 7.5px 20px;
      max-width: calc(50% - 6px);
      max-height: 250px;
      overflow: hidden;
      transition: all 0.1s ease-out 0s;
      width: 250px;
      text-align: center;
      margin-right: 16px;
      display: flex;
      flex-direction: column;
      align-items: center;

      &:hover {
        box-shadow: rgba(0, 0, 0, 14%) 0px 2px 2px 0px,
          rgba(0, 0, 0, 12%) 0px 1px 5px 0px, rgba(0, 0, 0, 30%) 0px 3px 5px 0px;
      }

      .text {
        font-size: 16px;
        font-weight: 600;
        margin: 12px 0px 24px;
      }
    }
  }
}
.create-form {
  .ant-form-item {
    margin-bottom: 5px !important;

    textarea {
      height: 100px !important;
    }
  }

  .ant-form-item-control-wrapper {
    flex: 1;
  }
}
</style>
