# PunStar 
## Index
* [Front Website](#front-website)
* [Prepare](#prepare)
    * [Wallet](#register)
    * [Faucet](#faucet)
* [Register](#register)
* [Continue to register](#continue-to-register)
* [Basic operations](#basic)
    * [Create Duanji](#createduanji)
    * [Follow](#follow)
    * [Like or commend](#like-or-commend)
* [Advanced operations](#advanced)
    * [Funny Index](#funny-index)
    * [Advertising](#advertising)
    * [Duanji Transfering](#duanji-transfering)
    * [Punster Transfering](#punster-transfering)
* [Multi-Ecosystem Extension](#multi-ecosystem-extension)


## Front Website

* [Front UI](http://punster.stonelens.com/)
* Note that we recommand using `Lilico` wallet to do the operations.

## Prepare
### Register
![image](https://user-images.githubusercontent.com/83746881/183075498-ebb66a67-3de0-44f1-a6be-02594884b597.png)
* The first time clicking `Login/Register` you will see two wallets. We choose `Lilico` and here are the [details](https://outblock.notion.site/Lilico-Support-User-FAQs-fc26f774ad514439a11c5c7cb255d1ec).
* Add `Lilico` wallet extension into your browser
* Create a Flow account if there's no one
* Turn on `Developer mode` and switch to `Testnet` in wallet `Lilico`
![image](https://user-images.githubusercontent.com/83746881/183077422-96cd1327-f8a1-4e69-8efc-4757d4ee27f3.png)
### Faucet
* Apply for test `Flow` to pay for gas and storage [here](https://testnet-faucet.onflow.org/fund-account)
![image](https://user-images.githubusercontent.com/83746881/183597445-f409fe26-4950-40d5-bdc3-ba5849b5a719.png)

### Continue to register
![image](https://user-images.githubusercontent.com/83746881/183079767-d3b0cf45-2cb1-40b3-adb6-05ea33db4fdc.png)
* `Submit` to finish register. This 

## Basic
### When finished your registering, you will see:  
![image](https://user-images.githubusercontent.com/83746881/183080618-12f24188-54c0-4b24-972e-06f79a885c09.png)
### Follow
* You can `Follow` to others to get the most recently update of someone  
![image](https://user-images.githubusercontent.com/83746881/183080798-8b8dbc78-65d2-4bda-b2b3-819e45ae3600.png)
### CreateDuanji
* You can `CreateDuanji` to share funny things so that punsters who following you will see it as soon as possible. This operation creates a new `Duanji`, which is a standard NFT on Flow.
![image](https://user-images.githubusercontent.com/83746881/183084113-b719739b-9175-45a7-a6a5-c0a29d91734a.png)
### Like or commend
* You can support the `Duangji` by click 😊 button or commending to it
![image](https://user-images.githubusercontent.com/83746881/183084772-48eee46b-43c6-4b2a-a784-5800ba66c772.png)

## Advanced
### Funny Index
* `Funny index` is used to describe how funny a `Duanji` is, which is calculated according to the count of commends the `Duanji` got and the time the `Duanji` was created.
* `Funny index` is also used to describe how funny a punster is. A punster's funny index depends on the sum of all his `Duanji`s' funny index.

### Advertising 
* You can publish an advertisement and punsters who following you will see it immediately. There will be a `Cooling time` for publishing advertisements, that is, you cannot publish again until the `cooling time` ends
![image](https://user-images.githubusercontent.com/83746881/183088057-701cb143-500e-431d-8bff-1093a5819c07.png)  
* ADs are something as below:
![image](https://user-images.githubusercontent.com/83746881/183090043-601c74b4-13ca-4227-94c6-b261c14b47d8.png)

### `Duanji` transfering
* The funny things can be *assetization*, that is, as `Duanji` is a standard NFT on Flow, it can be traded. The reality value is supported by the content of the funny thing, and also be supported by the commends it gets, which will make the `cooling time` of advertising of a punster shorter.   
![image](https://user-images.githubusercontent.com/83746881/183116625-776d056e-6961-4bd5-810f-74f928320953.png)

### `Punster` transfering: 
* The `social relationship` established by `Following` mechanisms can be *assetization*, and also the reality functions like advertising. Punster is also an NFT on Flow, it can be traded too. The reality value is supported by all the `Duanji` a punster published, and real abilities like advertising, based on which we may construct e-commerce abilities in the future.  
* Note that in this version one account can only hold on `Punster` 
* You can transfer `Punster` to others, and this operation will transfer all the static and dynamic features of `Punster` NFT, which is very different from NFT on other public chains.  
![image](https://user-images.githubusercontent.com/83746881/183297210-975fa812-b758-4eb1-84dc-e2a00c61584e.png)
* Enter the target address and click `Ok` to submit the transferring. If the account of the target address has no `Punster` yet and it has already created a `StarPort`, the transferring will success. 
![image](https://user-images.githubusercontent.com/83746881/183297251-95e2c5ba-b110-4144-a79b-9e57e331e487.png)

* Before the receiver gets `Punster` from others, it needs to create `StarPort` first, which is used for receiving the resource of `Punster` like any token vault on Flow does. Or the `Transfer` above will fail:  
![image](https://user-images.githubusercontent.com/83746881/183297124-b96ce7b5-e514-4789-b7c0-66fcd69835a5.png)
* If one account receives a transferring of `Punster` for others, the account needs to execute `Receive Punster` mannually before `CreateDuanji` at this version. This operation *moves* the instance of `Punster` resource from resource `StarPort` to the accout's storage path. This is a special feature on Flow
![image](https://user-images.githubusercontent.com/83746881/183298100-e15979b9-8db5-4402-9eec-c721f7b2bb1d.png)

  

## Multi-Ecosystem Extension
We provide the ability that user can transfer their `Duanji` NFT and `Punster` NFT to *Rinkeby(Testnet of Ethereum)*, which makes the NFTs of PunStar could be exhibited and exchanged on *Opensea*.  
Unfortunately we have not enough time to integrate it in to the front UI. But we have prepared related transactions and scripts, and you can find the [detailed tutorial](./Multi-Ecosystems.md) of how to operate `Multi-Ecosystem` ablities.

