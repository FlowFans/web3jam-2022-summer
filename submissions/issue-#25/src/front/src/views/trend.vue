<template>
  <div class="trend">
    <div class="upload">
      <textarea class="text" v-model="text"></textarea>
      <van-uploader
        class="video_file"
        :preview-image="false"
        accept=".mp4"
        v-model="videoFile"
        :after-read="afterRead"
      />
    </div>
    <div class="content" v-for="(video, index) in videoList" :key="index">
      <img
        class="toast"
        :src="video.tag_image"
        @click="herfToDetail(video.author)"
      />
      <div class="main">
        <div class="title" @click="herfToDetail(video.author)">
          <span class="name">{{ video.author_name + index }}</span>
          <img src="../assets/gou.svg" />
          <span class="sub-name">{{ video.author }}</span>
        </div>
        <div class="message" v-html="video.text"></div>
        <div class="music">
          <b>🎵 original sound - Desi Rock</b>
        </div>
        <com-video :video="video" :showTips="true"></com-video>
      </div>
      <div class="follow" v-if="user.isLogin">
        <a-button
          type="default"
          class="notfollow"
          @click="followUper(true, index)"
          v-show="!video.tagFollow"
          >Follow</a-button
        >
        <a-button
          type="danger"
          class="followed"
          @click="followUper(false, index)"
          v-show="video.tagFollow"
          >Followed</a-button
        >
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue';
import { onMounted, reactive, toRefs } from 'vue';
import comVideo from './Video.vue';
import axios from '../utils/axios';
import { notification } from 'ant-design-vue';
import { getUser } from '../store/user';

export default defineComponent({
  name: 'comTrend',
  data() {
    return {
      name: 'Wise.Wrong'
    };
  },
  components: {
    comVideo
  },
  setup(props, { emit }) {
    const state = reactive({
      videoFile: [],
      isVideoShow: true,
      playOrPause: true,
      iconPlayShow: true,
      videoProcessInterval: null,
      videoProcess: 0, //视频播放进度
      iconMultShow: false,
      videoList: [],
      user: null,
      text: ''
      //videoList:JSON.parse(JSON.stringify(props.videoList))
    });
    state.user = getUser();
    onMounted(async () => {
      getVideoList();
    });
    const getVideoList = async () => {
      const userId =
        state.user.accountId || '0x4b266b649aa79d53f9e70c4e734a59227fb581af';
      axios
        .get(`blitok/getVideoList?userId=${userId}`)
        .then(result => {
          console.log(result);
          if (result.success) {
            state.videoList = result.data;
          } else {
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

    const afterRead = () => {
      console.log(state.videoFile);
    };

    const followUper = (flag, index) => {
      axios
        .get(
          `/blitok/follow?userId=${state.user.accountId}&author_id=${state.videoList[index].author}&follow=${flag}`
        )
        .then(res => {
          state.videoList[index].tagFollow = flag;
          console.log(res);
        })
        .catch(err => {
          notification.error({
            message: 'follow upper error',
            description: err
          });
        });
    };

    const herfToDetail = author_id => {
      emit('menu-change', author_id);
    };

    return {
      ...toRefs(state),
      afterRead,
      followUper,
      herfToDetail
    };
  }
});
</script>
<style lang="less">
.trend {
  .upload {
    display: flex;
    margin-bottom: 40px;
    display: flex;
    align-items: flex-end;
    margin-left: 60px;
    .video_file {
    }
    .text {
      border-color: #e0e0e0;
      width: 60%;
      height: 100px;
      font-size: 16px;
      padding: 10px;
    }
  }

  .content {
    display: flex;
    margin-top: 80px;

    > img {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      flex: 0 0 50px;
      cursor: pointer;
    }

    .main {
      margin: 1px;
      margin-left: 20px;
      font-size: 16px;
      flex: 1;

      .title {
        display: flex;
        align-items: center;

        .name {
          font-size: 18px;
          font-weight: 700;
        }

        > img {
          width: 18px;
          height: 18px;
          margin: 0 4px;
        }

        .sub-name {
          font-size: 16px;
        }
      }

      .com_video {
        width: 250px;
        height: 440px;
      }
    }

    .follow {
      flex: 0;

      .notfollow {
        height: 30px;
        width: 85px;
        border-color: #fd4469;
        color: #fd4469;
      }

      .followed {
        height: 30px;
        width: 85px;
        border-color: #fd4469;
      }
    }
  }
}
</style>
