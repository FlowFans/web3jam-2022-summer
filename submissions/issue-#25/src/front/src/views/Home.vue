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
  <div class="home-container">
    <div class="left">
      <div class="logo"><img src="../assets/logo.svg" />BliTOK</div>
      <div v-show="isLogin" class="menu">
        <div
          @click="gotoMenu('home')"
          v-bind:class="[{ active: menuId == 'home' }]"
        >
          <img v-show="menuId == 'home'" src="../assets/home_active.svg" />
          <img v-show="menuId !== 'home'" src="../assets/home.svg" />
          Home
        </div>
        <div @click="gotoBinance">
          <img src="../assets/buy.svg" /> Buy BliTok
        </div>
        <div v-bind:class="[{ active: menuId == 'ngts' }]" @click="gotoMenu('ngts')">
          <img v-show="menuId == 'ngts'" src="../assets/swap_active.svg" />
          <img v-show="menuId !== 'ngts'" src="../assets/swap.svg" />
          Create Your NFT
        </div>
        <div
          @click="gotoMenu('profile')"
          v-bind:class="[{ active: menuId == 'profile' }]"
        >
          <img v-show="menuId == 'profile'" src="../assets/mining_active.svg" />
          <img v-show="menuId !== 'profile'" src="../assets/mining.svg" />
          View Profile
        </div>
        <div v-bind:class="[{ active: menuId == 'message' }]">
          <img
            v-show="menuId == 'message'"
            src="../assets/message_active.svg"
          />
          <img v-show="menuId !== 'message'" src="../assets/message.svg" />
          Message
        </div>
      </div>
      <div v-show="!isLogin" class="no-login">
        <div class="tip">
          Log in to follow the author, upvote and view comments.
        </div>
        <a-button class="login" type="default" @click="goTo">Log in</a-button>

        <div class="acount">
          <div class="title">Recommended account</div>
          <div class="recommend">
            <img src="../assets/account1.jpeg" />
            <div>
              <div>
                lisaandlena
                <img src="../assets/gou.svg" />
              </div>
              <div>🌸lisaandlena🌸</div>
            </div>
          </div>
          <div class="recommend">
            <img src="../assets/account2.jpeg" />
            <div>
              <div>
                __rl9
                <img src="../assets/gou.svg" />
              </div>
              <div>Robert Lewandowski</div>
            </div>
          </div>
          <div class="recommend">
            <img src="../assets/account3.jpeg" />
            <div>
              <div>
                sergioramos
                <img src="../assets/gou.svg" />
              </div>
              <div>Sergio Ramos</div>
            </div>
          </div>
          <div class="recommend">
            <img src="../assets/account4.jpeg" />
            <div>
              <div>
                💋dagibee
                <img src="../assets/gou.svg" />
              </div>
              <div>dagi</div>
            </div>
          </div>
          <div class="recommend">
            <img src="../assets/account5.jpeg" />
            <div>
              <div>
                capital🌻_🌻bra
                <img src="../assets/gou.svg" />
              </div>
              <div>Capital Bra</div>
            </div>
          </div>
          <div class="recommend" v-if="ifShowAll">
            <img src="../assets/account6.jpeg" />
            <div>
              <div>
                Monica❤️ sis
                <img src="../assets/gou.svg" />
              </div>
              <div>Monica</div>
            </div>
          </div>
          <div class="show-all" v-if="!ifShowAll" @click="showAll">
            Show all <img src="../assets/open.svg" />
          </div>
        </div>

        <div class="find">
          <div class="title">Found</div>
          <div class="item"># SoHot</div>
          <br />
          <div class="item"># CheeseLover</div>
          <br />
          <div class="item"># CarCrew</div>
          <br />
        </div>
      </div>
    </div>
    <div class="main-content">
      <com-trend
        v-if="menuId == 'home'"
        v-bind:videoList="videoList"
        @menu-change="parentClick"
      ></com-trend>
      <com-profile
        v-if="menuId == 'profile'"
      ></com-profile>
      <com-create
        v-if="menuId == 'ngts'"
      ></com-create>
    </div>
  </div>
</template>

<script>
import { reactive, toRefs, onMounted } from 'vue';
import { notification } from 'ant-design-vue';
import { useRouter } from 'vue-router';
import comProfile from './profile.vue';
import comTrend from './trend.vue';
import comCreate from './Create.vue';
import { getUser, setUser } from '../store/user';


export default {
  name: 'home',
  components: {
    comProfile,
    comTrend,
    comCreate
  },
  setup() {
    const router = useRouter();
    const state = reactive({
      menuId: 'home',
      text: 'It looks fantastic bro!',
      isLogin: false,
      ifShowAll: false,
      account: null,
      user:null,
      videoList: [
        {
          url:
            'http://video.jishiyoo.com/3720932b9b474f51a4cf79f245325118/913d4790b8f046bfa1c9a966cd75099f-8ef4af9b34003bd0bc0261cda372521f-ld.mp4', //视频源
          cover: 'http://oss.jishiyoo.com/images/file-1575341210559.png', //封面
          tag_image: 'http://oss.jishiyoo.com/images/file-1575343508574.jpg', //作者头像
          fabulous: false, //是否赞过
          tagFollow: false, //是否关注过该作者
          videoId: 'a123123', //视频id唯一
          author_name: 'desirork_007', //作者昵称
          author: 'Desi Rock', //作者真实姓名
          author_id: 'asd80123hk132asd', //作者身份标识
          text: 'Given caption 🙏 <b>#foryou #foruoupage</b>', //作者发布的推文内容,
          voteNumber: '22.3k', //点赞数
          comment: '23.1k', //评论数
          awayNumber: '20.1k' //转发数
        }
      ]
    });
    state.user = getUser();
    
    onMounted(() => {
      //authUser();
      //init();
    });
    
    const authUser = () => {
      console.log('-----authUser--------')
      console.log(state.user);
      if (!state.user.accountId) {
        state.user.isLogin = false;
        state.isLogin = false;
        router.replace({ path: '/home', query: { menuId: 'home' } });
      }/*  else if (
        !state.user.authTime ||
        new Date().getTime() - state.user.authTime > 15 * 60 * 1000
      ) {
        notification.warning({
          message: 'Authentication status timeout, please login again! '
        });
        resetUser();
        state.isLogin = false;
        router.replace({ path: '/home', query: { menuId: 'home' } });
      }  */else {
        setUser({authTime:new Date().getTime(),isLogin:true})
        state.isLogin = true;
      }
    };

    const goTo = async () => {
      if (typeof window.ethereum != 'undefined') {
        const accounts = await window.ethereum.request({
          method: 'eth_requestAccounts'
        });
        setUser({accountId:accounts[0],authTime:new Date().getTime(),isLogin:true});
        state.isLogin = true;
        init();
      } else {
        notification.warning({
          message: 'You should install MetaMask first! '
        });
      }
    };
    const init = () => {
      const menuId = router.currentRoute.value.query.menuId;
      state.menuId = menuId || 'home';
    };
    const showAll = async () => {
      state.ifShowAll = true;
    };

    const gotoBinance = () => {
      window.open('https://app.uniswap.org/#/swap');
    };

    const gotoMenu = t => {
      if(!state.isLogin) return;
      state.menuId = t;
      router.replace({ path: '/home', query: { menuId: t } });
    };
    const parentClick = t => {
      state.menuId = 'profile';
      router.replace({
        path: '/home',
        query: { menuId: 'profile', accountId: t }
      });
    };
    return {
      ...toRefs(state),
      showAll,
      init,
      goTo,
      gotoBinance,
      gotoMenu,
      parentClick,
      authUser
    };
  }
};
</script>

<style lang="less">
.van-uploader__upload {
  width: 60px;
  height: 60px;
  margin: 0 5px;
}
.van-uploader__preview {
  display: none;
}
.home-container {
  display: flex;
  width: 100%;
  min-height: 100%;
  padding: 4% 6%;

  .left {
    flex: 0 0 300px;
    .logo {
      font-size: 40px;
      font-weight: 900;
      color: #004b68;
      display: flex;

      img {
        height: 50px;
        width: auto;
      }
    }

    .menu {
      margin-top: 70px;

      > div {
        display: flex;
        font-size: 24px;
        font-weight: 700;
        cursor: pointer;
        margin-bottom: 40px;
        &.active {
          color: #148d96;
        }

        img {
          width: 30px;
          height: 30px;
          margin-right: 16px;
        }
      }
    }

    .no-login {
      .tip {
        width: 160px;
        color: #9d9fa3;
        font-size: 12px;
        font-weight: 500;
        margin-bottom: 10px;
        margin-top: 30px;
        text-align: center;
      }

      .login {
        width: 160px;
        border-color: #148d96;
        color: #148d96;
        font-weight: 500;
        height: 39px;
      }
    }

    .acount {
      .title {
        border-top: 0.5px solid #e7e8ea;
        color: #8d8e90;
        font-weight: 600;
        font-size: 14px;
        margin-top: 20px;
        padding-top: 16px;
      }

      .recommend {
        display: flex;
        margin: 10px 0px;

        > img {
          height: 40px;
          width: 40px;
          border-radius: 50%;
        }

        > div {
          margin-left: 12px;

          > div:first-child {
            font-weight: 700;
            font-size: 16px;
            color: #2c3e50;
          }
          > div {
            font-size: 14px;
            color: #898a90;

            > img {
              width: 16px;
              height: 16px;
              vertical-align: middle;
            }
          }
        }
      }

      .show-all {
        display: flex;
        align-items: center;
        font-weight: 600;
        color: #148d96;
        font-size: 14px;
        cursor: pointer;

        img {
          width: 20px;
          height: 20px;
        }
      }
    }

    .find {
      margin-top: 15px;
      .title {
        font-size: 16px;
        font-weight: 500;
        color: #8d8e90;
      }

      .item {
        display: inline-block;
        border: 1px solid #d6d6d6;
        border-radius: 20px;
        padding: 0 10px;
        margin: 5px 0;
      }
    }
  }

  .main-content {
    padding-top: 60px;
    flex: 1;
    min-height: 100%;
    margin-left: 60px;
    overflow: hidden;
  }
}
</style>
