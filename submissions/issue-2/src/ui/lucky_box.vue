<template>
    <LuckyWheel
      ref="myLucky"
      width="300px"
      height="300px"
      :prizes="prizes"
      :blocks="blocks"
      :buttons="buttons"
      @start="startCallback"
      @end="endCallback"
    />

</template>

<script>
import * as fcl from '@onflow/fcl'
import * as types from '@onflow/types'

fcl.config({
  'discovery.wallet': 'https://fcl-discovery.onflow.org/testnet/authn', // Endpoint set to Testnet
  'discovery.authn.endpoint': 'https://fcl-discovery.onflow.org/api/testnet/authn',
  'app.detail.title': 'FCL + VUE',
  '0x{CONTRACT_NAME}NFT': '{CONTRACT_ADDRESS}', //please revise here to your own address
  'app.detail.icon': 'http://placekitten.com/200/300',
  'accessNode.api': 'https://rest-testnet.onflow.org',
})


export default {
  data() {
    return {
      address:"",//用户地址
      item_prob:{ITEM_PROB_LIST},
      prizes: {PRIZES_LIST},
      buttons: [
        { radius: '50px', background: '#617df2' },
        { radius: '45px', background: '#afc8ff' },
        {
          radius: '40px', background: '#869cfa',
          pointer: true,
          fonts: [{ text: '开始\n抽奖', top: '-20px' }]
        },
      ],
    }
  },

  created() {
    fcl.authenticate()
    fcl.currentUser().subscribe(
      currentUser=> {
        this.address = currentUser.addr
      }
    )
  },

  methods: {
    // 点击抽奖按钮会触发star回调
    startCallback () {
      // 调用抽奖组件的play方法开始游戏
      this.result_form = {
        "block_id":"",
        "transaction_id":"",
        "nft_id":"",
        "prize_name":"",
      }

      this.$refs.myLucky.play()
      this.loading = true
      this.get_box().then(
        index=>{
          this.$refs.myLucky.stop(index)
          this.loading = false
        }
      )
    },

    // 抽奖结束会触发end回调
    endCallback (prize) {
      console.log(prize)
    },

    //获得抽奖
    async get_box() {
      const transactionId = await fcl.mutate({
        cadence: ` 
          import NonFungibleToken from  0x4c216d104db9ecbe
          import {CONTRACT_NAME}NFT from  {CONTRACT_ADDRESS}
          import MetadataViews from  0x4c216d104db9ecbe
          import FungibleToken from  0x4c216d104db9ecbe

          transaction(
              recipient_address: Address,
          ) {

              /// local variable for storing the minter reference
              let minter: &LucycanvasNFT.NFTMinter

              /// Reference to the receiver's collection
              let recipientCollectionRef: &{NonFungibleToken.CollectionPublic}

              prepare(signer: AuthAccount) {
                  //判断是否已经初始化过nft仓库
                   if signer.borrow<&LucycanvasNFT.Collection>(from: LucycanvasNFT.CollectionStoragePath) == nil {
                      // Create a new empty collection
                      let collection <- LucycanvasNFT.createEmptyCollection()

                      // save it to the account
                      signer.save(<-collection, to: LucycanvasNFT.CollectionStoragePath)

                      // create a public capability for the collection
                      signer.link<&{NonFungibleToken.CollectionPublic, LucycanvasNFT.LucycanvasNFTCollectionPublic, MetadataViews.ResolverCollection}>(
                          LucycanvasNFT.CollectionPublicPath,
                          target: LucycanvasNFT.CollectionStoragePath
                      )
                  }


                  // borrow a reference to the NFTMinter resource in storage
                  let public_signer =  getAccount({CONTRACT_ADDRESS})

                  self.minter = public_signer
                  .getCapability(LucycanvasNFT.MinterPublicPath)
                  .borrow<&LucycanvasNFT.NFTMinter>()
                      ?? panic("Account does not store an object at the specified path")

                  // Borrow the recipient's public NFT collection reference
                  self.recipientCollectionRef = getAccount(recipient_address)
                      .getCapability(LucycanvasNFT.CollectionPublicPath)
                      .borrow<&{NonFungibleToken.CollectionPublic}>()
                      ?? panic("Could not get receiver reference to the NFT Collection")
              }

              execute {
                  // Mint the NFT and deposit it to the recipient's collection
                  self.minter.mintNFT(
                      recipient: self.recipientCollectionRef,
                      user_address:recipient_address
                  )
              }
          }
          `,
        args: (arg, t) => [arg(this.address, types.Address)],
        limit: 1000
      })

      const transaction = await fcl.tx(transactionId).onceSealed()
      console.log(transaction) // The transactions status and events after being
      this.result_form.block_id = transaction.blockId
      var nft_id = 0
      for (var i=0;i<transaction.events.length;i++ ) {
        let item = transaction.events[i]
        if (1==i)
        {
          nft_id = item.data.id
          this.result_form.nft_id = nft_id
          this.result_form.transaction_id = item.transactionId
        }
      }
      console.log("nft_id", nft_id)

      //获得抽奖物品名称
      let name = await fcl.query({
        cadence: `
          import {CONTRACT_NAME}NFT from  {CONTRACT_ADDRESS}
          import MetadataViews from  0x4c216d104db9ecbe

          pub fun main(address: Address, id: UInt64): String {
              let account = getAccount(address)

              let collection = account
                  .getCapability(LucycanvasNFT.CollectionPublicPath)
                  .borrow<&{LucycanvasNFT.LucycanvasNFTCollectionPublic}>()
                  ?? panic("Could not borrow a reference to the collection")

              let nft = collection.borrowLucycanvasNFT(id: id)!

                  // Get the basic display information for this NFT
              let view = nft.resolveView(Type<MetadataViews.Display>())!

              let display = view as! MetadataViews.Display

              return display.name
           }
      `,
        args: (arg, t) => [arg(this.address, types.Address), arg(nft_id, types.UInt64)],

      })

      console.log("nft name", name)
      this.result_form.prize_name = name

      //根据name，获得 index
      var prize_index_dict = {PRIZE_INDEX}

      let lucky_index = prize_index_dict[name]
      console.log("lucky_index", lucky_index)
      return lucky_index
    },


  }
}
</script>