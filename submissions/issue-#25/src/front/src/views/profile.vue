<template>
  <div class="profile">
    <div class="user">
      <img src="../assets/account4.jpeg" class="toast" />
      <div class="info">
        <div class="name">Kimos Li</div>
        <div class="real-name">李波</div>
        <div class="id" v-if="!accountId || user.accountId == accountId">
          {{ user.accountId }}
        </div>
      </div>
    </div>
    <div class="relation">
      <div>
        <span class="number">0</span>
        <span class="type">Following</span>
      </div>
      <div>
        <span class="number">0</span>
        <span class="type">Followed</span>
      </div>
      <div>
        <span class="number">0</span>
        <span class="type">Likes</span>
      </div>
    </div>
    <div class="account">No bio yet.</div>

    <a-tabs
      class="tabs"
      v-model:activeKey="activeKey"
      @change="tabChange(activeKey)"
    >
      <a-tab-pane key="1">
        <template #tab>
          <span>
            Videos
          </span>
        </template>
        <div class="video-list">
          <com-video
            v-for="(vd, index) in videoList"
            :key="index"
            :video="vd"
            :showTips="false"
          ></com-video>
          <div class="account" v-if="videoList.length==0">No video yet.</div>
        </div>
      </a-tab-pane>
      <a-tab-pane key="2">
        <template #tab>
          <span>
            <LockOutlined />
            Liked
          </span>
        </template>
        No Likes yet.
      </a-tab-pane>
      <a-tab-pane key="3" v-if="!accountId || user.accountId == accountId">
        <template #tab>
          <span>
            NFTS
          </span>
        </template>
        <com-dfts :list="ntfsList"></com-dfts>
      </a-tab-pane>
    </a-tabs>
  </div>
</template>

<script>
import { defineComponent } from 'vue';
import { onMounted, reactive, toRefs } from 'vue';
import { LockOutlined } from '@ant-design/icons-vue';
import comVideo from './Video.vue';
import comDfts from './Ntfs.vue';
import { getUser } from '../store/user';
import axios from '../utils/axios'
import { notification } from 'ant-design-vue';

export default defineComponent({
  name: 'comProfile',
  data() {
    return {
      name: 'Wise.Wrong'
    };
  },
  setup() {
    const state = reactive({
      text: 'It looks fantastic bro!',
      user: null,
      ntfsList: [],
      accountId: null, //不一定是本人的
      videoList: []
    });
    state.user = getUser();
    onMounted(() => {
      /* setTimeout(() => {
        state.accountId = router.currentRoute.value.query.accountId;
      }, 0);
      getVideoList(); */
    });
    const getVideoList = async () => {
        const userId = state.user.accountId || '0x4b266b649aa79d53f9e70c4e734a59227fb581af';
        axios
          .get(`blitok/getVideoList?userId=${userId}`)
          .then(result => {
            if(result.success) {
              state.videoList = result.data;
            }else{
              state.videoList = [];
            }
          })
          .catch(err => {
            state.videoList = [];
            console.log(err);
            notification.error({
              message: 'getVideoList error',
              description: err
            });
          });
      };

    const tabChange = key => {
      console.log(key);
    };

    return {
      ...toRefs(state),
      tabChange,
      getVideoList
    };
  },
  components: {
    LockOutlined,
    comVideo,
    comDfts,
  }
});
</script>
<style lang="less">
.ant-tabs-nav .ant-tabs-tab-active {
  color: black !important;
}
.ant-tabs-nav .ant-tabs-tab {
  color: #7d7d7d;

  &:hover {
    color: black;
  }
}
.ant-tabs-ink-bar {
  background: black;
}
.profile {
  min-height: 100%;
  .user {
    display: flex;

    img {
      height: 100px;
      margin-right: 20px;
      border-radius: 50%;
    }

    .info {
      display: flex;
      flex-direction: column;
      justify-content: space-around;
      .name {
        font-size: 24px;
        font-weight: 700;
      }
      .real-name {
        font-size: 18px;
        font-weight: 600;
      }
      .id {
        font-size: 14px;
        font-weight: 400;
      }
    }
  }

  .relation {
    display: flex;
    margin-top: 20px;
    font-size: 14px;

    > div {
      margin-right: 20px;

      .number {
        margin-right: 5px;
        font-weight: 700;
      }
    }
  }

  .account {
    margin-top: 20px;
    font-weight: 500;
  }

  .tabs {
    margin-top: 20px;
    .ant-tabs-nav .ant-tabs-tab {
      font-size: 18px;
      text-align: center;
      padding: 0 50px;
    }
  }

  .video-list {
    display: flex;

    .com_video {
      margin-right: 10px;
      width: 150px;
      max-height: 264px;
    }
  }
}
</style>
