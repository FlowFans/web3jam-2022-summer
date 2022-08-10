<template>
  <div class="com_video">
    <video
      class="video_box"
      webkit-playsinline="true"
      x5-video-player-type="h5-page"
      x5-video-player-fullscreen="true"
      playsinline
      preload="auto"
      :poster="video.cover"
      :src="video.url"
      :playOrPause="playOrPause"
      @click="pauseVideo()"
       :ref="dom"
    ></video>
    <!-- 封面 -->
    <img
      class="cover_img"
      v-show="isVideoShow"
      @click="playvideo"
      :src="video.cover"
    />
    <!-- 播放暂停按钮 -->
    <img
      v-show="iconPlayShow"
      class="icon_play"
      @click="playvideo"
      src="../assets/play.svg"
    />
    <img
      v-show="!iconMultShow"
      class="icon_sound"
      @click="multVideo(true)"
      src="../assets/soundopen.svg"
    />
    <img
      v-show="iconMultShow"
      class="icon_sound"
      @click="multVideo(false)"
      src="../assets/soundclose.svg"
    />

    <div class="upvote" @click="upvoteVideo()" v-if="showTips">
      <div class="image">
        <img
          src="../assets/upvote_grey.svg"
          v-show="!video.fabulous"
          class="grey"
        />
        <img
          src="../assets/upvote_red.svg"
          v-show="video.fabulous"
          class="red"
        />
      </div>
      <div class="number">{{ video.voteNumber }}</div>
    </div>
    <div class="upvote secend" v-if="showTips">
      <div class="image">
        <img src="../assets/comment.svg" />
      </div>
      <div class="number">{{ video.comment }}</div>
    </div>
    <div class="upvote third" v-if="showTips">
      <div class="image">
        <img src="../assets/away.svg" />
      </div>
      <div class="number">{{ video.awayNumber }}</div>
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue';
import { onMounted, reactive, toRefs } from 'vue';
import axios from '../utils/axios'
import { notification } from 'ant-design-vue';
import { getUser} from '../store/user';

export default defineComponent({
  name: 'comVideo',
  data() {
    return {
      name: 'Wise.Wrong',
    };
  },
  props: { video: { type: Object } ,showTips:{type:Boolean}},
  setup(props) {
    let refs;
    const state = reactive({
      iconMultShow: false,
      playOrPause: true,
      iconPlayShow: true,
      isVideoShow:true,
      user:null,
      video:JSON.parse(JSON.stringify(props.video)),
    });
    state.user = getUser();
    onMounted(() => {
    });
    const multVideo = flag => {
      //暂停\播放
      try {
        refs.muted = flag;
        state.iconMultShow = flag;
      } catch (e) {
        alert(e);
      }
    };

    const pauseVideo = () => {
      //暂停\播放
      try {
        //let video = document.querySelectorAll('video')[0];
        state.playOrPause = true;
        refs.pause();
        state.iconPlayShow = true;
      } catch (e) {
        alert(e);
      }
    };

    const playvideo = () => {
      //let video = document.querySelectorAll('video')[0];
      state.isVideoShow = false;
      state.iconPlayShow = false;
      state.playOrPause =false;
      refs.play();
    };
    const upvoteVideo = ()=> {
      axios.post('/blitok/upvote',{
        userId:state.user.accountId,
        videoId:props.video.videoId,
        fabulous: !state.video.fabulous, //点赞传true，取消点赞传false
      }).then(()=>{
        state.video.fabulous = !state.video.fabulous;
      }).catch(err=>{
        notification.error({
          message: 'upvote video error',
          description:err.messages
        });
      })
    }
    const dom = el => {
       refs= el;
     }

    return {
      ...toRefs(state),
      multVideo,
      pauseVideo,
      playvideo,
      dom,
      upvoteVideo,
    };
  },
  components: {}
});
</script>
<style lang="less">
.com_video {
  position: relative;
  width: 100%;
  height: 100%;

  .video_box {
    width: 100%;
    height: 100%;
    border-radius: 4px;
    background: black;
    cursor: pointer;
  }
  .cover_img {
    position: absolute;
    margin: auto;
    top: 50%;
    left: 0;
    z-index: 999;
    width: 100%;
    height: auto;
    border-radius: 4px;
    transform: translate(0, -50%);
  }

  .icon_play {
    position: absolute;
    margin: auto;
    top: 50%;
    left: 50%;
    z-index: 999;
    height: 60px;
    width: 60px;
    transform: translate(-50%, -50%);
    cursor: pointer;
  }

  .icon_sound {
    position: absolute;
    margin: auto;
    bottom: 10px;
    right: 10px;
    z-index: 999;
    height: 20px;
    width: 20px;
    cursor: pointer;
  }

  .upvote {
    position: absolute;
    margin: auto;
    bottom: 140px;
    right: -60px;
    z-index: 999;
    cursor: pointer;

    &.secend {
      bottom: 70px;
    }

    &.third {
      bottom: 0px;
    }

    .image {
      background: #f0f0f0;
      width: 40px;
      height: 40px;
      text-align: center;
      border-radius: 50%;
      img {
        width: 20px;
        margin-top: 10px;
        height: 20px;
      }
    }

    .number {
      font-size: 12px;
      width: 40px;
      text-align: center;
      font-weight: 500;
      margin-top: 2px;
    }
  }
}
</style>
