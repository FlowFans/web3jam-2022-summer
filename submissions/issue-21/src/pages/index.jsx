import styles from './index.less';
import {Button,Layout,Space,Menu, List,Image } from 'antd';
import * as fcl from "@onflow/fcl";
import { useState,useEffect } from 'react';
import GETBALANCE from "../cadence/scripts/get_balance.cdc"


export default function IndexPage() {
  const [user, setUser] = useState({loggedIn: null})
  const { Header, Footer, Sider, Content } = Layout;
  useEffect(
    () => fcl.currentUser.subscribe(setUser), [])
  //登录，登出
 async function clickWallet()
  {

    console.log("hello world");
    //测试钱包地址
    fcl.config({
      "accessNode.api": "https://rest-testnet.onflow.org",
      "discovery.wallet": "https://fcl-discovery.onflow.org/testnet/authn", // Endpoint set to Testnet
    })
    //并且登录
    fcl.authenticate();

    const currentUser = await fcl.currentUser.snapshot();
    console.log("The Current User", currentUser);
    fetchAccountItems();
  }

  async function fetchAccountItems() {
    // prettier-ignore
  var  a=  await fcl.query({
      cadence: GETBALANCE,
    
    })
    console.log(a); // 13
  }

 async  function fclquery() {
    const result = await fcl.query({
      cadence: `
      pub fun main(a: Int, b: Int): Int {
        
        return a + b
      }
    `,
      args: (arg, t) => [
        arg(7, t.Int), // a: Int
        arg(6, t.Int), // b: Int
      ],
    });
    console.log(result); // 13
  }

  const data = [
    {
      title: '凌云木主题赛',
      description:"有机会获取凌云木NFT",
      Image:'https://zos.alipayobjects.com/rmsportal/jkjgkEfvpUPVyRjUImniVslZfWPnJuuZ.png',
    },
    {
      title: 'BAYC主题赛',
      description:"有机会获取BAYC",
      Image:'https://zos.alipayobjects.com/rmsportal/jkjgkEfvpUPVyRjUImniVslZfWPnJuuZ.png',
    }
  ];

 async function getBlance()
 {

   var response= await fcl.query(
    {
      cadence: script  
    },
  );
  console.log(response)
 }


  async function fclmutate() {
    const transactionId = await fcl.mutate({
      cadence: `
        import  HelloWorld from 0xb51a64d391859f6d
        
        transaction {

          prepare(acct: AuthAccount) {}
        
          execute {
            log(HelloWorld.hello())
          }
        }
      `,
      proposer: fcl.currentUser,
      payer: fcl.currentUser,
      authorizations: [fcl.currentUser],
      limit: 50,
      args: () => []
    });
    const transaction = await fcl.tx(transactionId).onceSealed()
    console.log(transaction) // The transactions status and events after being sealed
  }
  async function fclmutate2() {
    var testUser=fcl.currentUser;
    const transactionId = await fcl.mutate({
      cadence: `
        transaction {
          execute {
            log("Hello from execute")
          }
        }
      `,
    proposer: fcl.currentUser,
  payer: fcl.currentUser,
  authorizations: [],
      limit:50
    })
    
    const transaction = await fcl.tx(transactionId).onceSealed()
    console.log(transaction) // The transactions status and events after being sealed
  }


  const AuthedState = () => {
    return (
      <div>
        <div>Address: {user?.addr ?? "No Address"}</div>
        <button onClick={fcl.unauthenticate}>Log Out</button>
      </div>
    )
  }
  const UnauthenticatedState = () => {
    return (
      <div>
       
        <button onClick={fcl.signUp}>Sign Up</button>
      </div>
    )
  }
  //获取钱包关联的资金
  function getWalletNFTAndFt()
  {

    console.log("hello world");
    //测试钱包地址
    // fcl.config({
    //   "discovery.wallet": "https://fcl-discovery.onflow.org/testnet/authn", // Endpoint set to Testnet
    // })
    fcl.config({
      "accessNode.api": "https://rest-testnet.onflow.org", // Mainnet: "https://rest-mainnet.onflow.org"
      "discovery.wallet": "https://fcl-discovery.onflow.org/testnet/authn",  // Mainnet: "https://fcl-discovery.onflow.org/authn"
      "0xProfile": "0xba1132bc08f82fe2" // The account address where the Profile smart contract lives on Testnet
    })
    //并且登录
    fcl.authenticate();
  }

  const sendQuery = async () => {
    const profile = await fcl.query({
      cadence: `
        import Profile from 0xProfile

        pub fun main(address: Address): Profile.ReadOnly? {
          return Profile.read(address)
        }
      `,
      args: (arg, t) => [arg(user.addr, t.Address)]
    })

    setName(profile?.name ?? 'No Profile')
  }

  const initAccount = async () => {
    const transactionId = await fcl.mutate({
      cadence: `
        import Profile from 0xProfile
  
        transaction {
          prepare(account: AuthAccount) {
            // Only initialize the account if it hasn't already been initialized
            if (!Profile.check(account.address)) {
              // This creates and stores the profile in the user's account
              account.save(<- Profile.new(), to: Profile.privatePath)
  
              // This creates the public capability that lets applications read the profile's info
              account.link<&Profile.Base{Profile.Public}>(Profile.publicPath, target: Profile.privatePath)
            }
          }
        }
      `,
      payer: fcl.authz,
      proposer: fcl.authz,
      authorizations: [fcl.authz],
      limit: 50
    })
  
    const transaction = await fcl.tx(transactionId).onceSealed()
    console.log(transaction)
  }
  //购买盲盒
  function buyblindbox()
  {


  }
  //铸造NFT
  function CreateNFT()
  {}
  //开始游戏
  function StartGame()
  {}

  function GameSettlement()
  {}

  return (
    <div className={styles.bg}>
       <div className={styles.titlespace1}></div>
     <div className={styles.normal}>
      <div className={styles.titlespace}></div>
<h1 className={styles.title}>NFTHunter</h1>
<Button  className={styles.wallet} onClick={clickWallet}>连接钱包</Button>
      </div>
      <Menu className={styles.normalMenu} mode="horizontal" defaultSelectedKeys={['mail']}>
    <Menu.Item key="mail" >
      首页
    </Menu.Item>
    <Menu.Item key="mail4" >
      个人属性
    </Menu.Item>
    <Menu.Item key="mail1" >
     铸造NFT
    </Menu.Item>
    <Menu.Item key="mail2" >
     奖池页面
    </Menu.Item>
    </Menu>
    <div className={styles.gameDiv}>
    <div className={styles.gameIntroduce}>游戏介绍
    <div className={styles.gameIntroduceContentnormal}>  产品初衷：
NFTHunter以互动游戏的形式打破商家进入元宇宙的门槛，作为商家和元宇宙的桥梁之一
，初步的设想是基于挖矿类游戏进行拓展，结合凌云木的主题方案设计美术效果
初步功能：
抓取凌云木，通过一定的关卡可以获得一定的FT代币，并在某些关卡有宝箱功能，能获取NFT抽奖券进行商家的主题抽奖。
未来的完善：
讲特例变成通例，基于挖矿游戏进行拓展宇宙新生态。
完善商家的NFT铸造功能
完善抽奖功能
完善主题赛设置功能
完善多人合作和竞赛功能</div>

      
</div>
<div className={styles.gameList}>
<List className="demo-loadmore-list"
      itemLayout="horizontal"
      dataSource={data}
      renderItem={item => (
        <List.Item >
          <div className={styles.gameListItem}>
          <div className={styles.Item.Meta }>{item.title}</div>
          <div className={styles.Item.Meta }>{item.description}</div>
          <img 
          className={styles.gameImage}
          src='https://zos.alipayobjects.com/rmsportal/jkjgkEfvpUPVyRjUImniVslZfWPnJuuZ.png'/>
        
             <Button className={styles.gamePlay}>play</Button>    
             </div>
        </List.Item>
      )}>
      </List>

      
    </div>


    </div>
  



      <div>Profile Name: {name ?? "--"}</div> {/* NEW */}
      <button onClick={fclquery}>Send Query</button>
      <button onClick={fclmutate}>fclmutate</button>
      {user.loggedIn
        ? <AuthedState />
        : <UnauthenticatedState />
      }
    </div>
  );
}
