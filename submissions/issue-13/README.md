# Project Name
SwiftTickets

ticketNFT 
    Testnet 合约[地址](https://flow-view-source.com/testnet/account/0xa3c1282a571e9c9e)
    deployed to Testnet address: 0xa3c1282a571e9c9e
Marketplace 
    Testnet 合约[地址](https://flow-view-source.com/testnet/account/0xa49ebdc0c27b0aa8)
    deployed to Testnet address: 0xa49ebdc0c27b0aa8

## Track

- [ ] NFT x DAO/Tools
- [ ] NFT x Game/Entertainment
- [x] NFT x Life/Metaverse

### Project Description:

Create a purchase tool based on dynamic ticket viewing for organizations or individuals


### Project background

SwiftTickets is designed to solve the existing problems of traditional ticketing as much as possible. It can be reflected through the complaints in the consumer protection institutions that consumers must trust a third party when buying tickets in the secondary market, so they are faced with the risk of buying fake or invalid tickets. The two-dimensional code or bar code does not encrypt the information, which is not enough to make the ticket truly tamper resistant. In addition, consumers cannot verify whether the bar code on the ticket is valid. Therefore, we hope that by combining with NFT, we can design a ticket system with NFT characteristics, including but not limited to digitization, security, verifiability, transparency and ticket value-added. 


### Product scheme

_ Introduction_

Create an account by connecting to the blocto wallet to register an account. The account is divided into a user account and a merchant account. The user account is limited to viewing the ticketing of the purchase market and viewing on the flow chain after the purchase. The merchant account enters the white list, which can be used to create ticketing and put on and off the shelf ticketing.


The front-end dynamic NFT displays ticketing, and the NFT itself carries ticketing information.


_ Technical architecture_

Cadence is used to implement all product logics. The TicketNFT contract implements the standard NFT contract, and realizes the separation of the permissions of ticketing providers and users through the form of white list. The Marketplace contract manages the loading and unloading of ticketing through the permissions of TicketNFT.

_ Product logo_
![](https://www.storswift.com/img/logo_b.png)



## What was done during Web3 Jam
_Cadence contract_

- [x] NFT standard contract implementation

- [x] ticket agent white list form authorization (Mint)

- [x] ticketing business white list on and off the shelves

- [x] contract user case unit test 

_Client end_

- [x] UI design
- [x] FCL integrate
- [x] Create Streaming payment UI
- [x] Streaming info/note display UI

_Service end_

- [x] UI design

- [x] FCL access

## Team

| 姓名 Name  | 角色 Role      | 个人经历 Bio   | 联系方式 Contact                           |
| --------- | -------------  | ------------   | -------------------------------------------  |
| Tang      | Fullstack dev  | ...            | github:TANG0615 / email: vanilla_t21@163.com |
