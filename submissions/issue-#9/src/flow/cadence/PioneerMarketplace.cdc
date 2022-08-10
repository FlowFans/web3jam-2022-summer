 import FungibleToken from 0x9a0766d93b6608b7
 import NonFungibleToken from 0x631e88ae7f1d7c20
import PioneerNFT from 0x2a9b2f3682e65464
 import FlowToken from 0x7e60df042a9c0868


// import FungibleToken from "./FungibleToken.cdc"
// import NonFungibleToken from "NonFungibleToken.cdc"
// import PioneerNFT from "./PioneerNFT.cdc"
// import FlowToken from "./FlowToken.cdc"

pub contract PioneerMarketplace{

    pub let StorefrontStoragePath: StoragePath
    pub let StorefrontPublicPath: PublicPath
    
    pub let StorefrontActivityStoragePath: StoragePath
    pub let StorefrontActivityPublicPath: PublicPath


    pub event NFTStorefrontInitialized()
    pub event StorefrontInitialized(storefrontResourceID: UInt64)
    pub event StorefrontDestroyed(storefrontResourceID: UInt64)
    
    pub event ListingAvailable(
        storefrontAddress: Address,
        listingResourceID: UInt64,
        nftType: Type,
        nftID: UInt64,
        ftVaultType: Type,
        price: UFix64?,
        minPrice: UFix64?
    )
    pub event ListingCompleted(
        listingResourceID: UInt64, 
        storefrontResourceID: UInt64, 
        purchased: Bool,
        nftType: Type,
        nftID: UInt64
    )
    pub event ActivityTargetReached(activityID: UInt64,taragetAmount: UFix64)
    pub event ActivityCreated(activityID: UInt64)

    pub event UserBid(activityID: UInt64,ticketAmount: UFix64,currentAmount: UFix64)

    // pub event ItemGivenParted(activeID: UInt64,nftID:UInt64,nftType:Type,partAmount: UInt64,limitAmount: UFix64)
    pub var nextActivityID: UInt64

    //0 是活动未开始(nft闲置状态) 1 掘金中，2掘金活动成功，3掘金活动失败（超时）4（NFT在售卖）
    pub var itemStatus: UInt8


    access(all) var activityUserTicket: {UInt64:{Address:UFix64}}
    // 记录用户参与过哪些活动
    pub var userActivity:{Address:[UInt64]}

    //生成活动id和创建的活动detail的mapping
    pub var activityMapping: {UInt64:Activity}
    
    //活动ID=>{用户地址：用户贡献}
    access(all) var activityUserAmount: {UInt64:{Address:UFix64}}

    pub struct Activity{
        pub let id: UInt64
        pub let name: String
        pub let description: String
        pub let url: String
        pub(set) var activeStatus: UInt8
        pub(set) var winBider:Address?
        pub let nftType: Type
        pub let nftID: UInt64
        pub let creator: Address
        pub let createTime: UFix64
        pub let targetAmount: UFix64
        pub(set) var currentAmount: UFix64
        pub let minPartAmount: UFix64
        pub let divisionCount: UInt64
        pub let startTime: UFix64
        pub let endTime: UFix64
        pub let difference: UFix64
        pub let receiver: Capability<&{FungibleToken.Provider,FungibleToken.Receiver}>
        pub let externalUrl: String
         init( 
        id: UInt64,
        name: String,
        description: String,
        url: String,
        activeStatus: UInt8,
        winBider:Address?,
        nftType: Type,
        nftID: UInt64,
        creator: Address,
        createTime: UFix64,
        targetAmount: UFix64,
        currentAmount: UFix64,
        minPartAmount: UFix64,
        divisionCount: UInt64,
        startTime: UFix64,
        endTime: UFix64,
        difference: UFix64,
        receiver: Capability<&{FungibleToken.Provider,FungibleToken.Receiver}>,
        externalUrl: String
       ){
            self.id=id
            self.name=name
            self.description=description
            self.url=url
            self.activeStatus=activeStatus
            self.winBider=winBider
            self.nftType=nftType
            self.nftID=nftID
            self.creator=creator
            self.createTime=createTime
            self.targetAmount=targetAmount
            self.currentAmount=currentAmount
            self.minPartAmount=minPartAmount
            self.divisionCount=divisionCount
            self.startTime=startTime
            self.endTime=endTime
            self.difference=difference
            self.receiver=receiver
            self.externalUrl=externalUrl
        }
    }


  //判定活动是否成功
    pub fun judgeActivity(id:UInt64):Bool{
        let activity=PioneerMarketplace.getActivity(id:id)
        if activity.currentAmount>=activity.targetAmount{
            return true
        }
        return false  
    }

    //true PastExpiry 判断活动是否到期
    pub fun judgePastExpiry(id :UInt64):Bool{
        let activityDetail=PioneerMarketplace.getActivity(id:id)
        let isPastExpiry: Bool = getCurrentBlock().timestamp >= activityDetail.endTime
        return !isPastExpiry
    } 
    
    //最后重新设计    产生掘金王
    pub fun selectWinner(id:UInt64, randomSeed: UInt): Address{

        let hash =PioneerMarketplace.activityUserTicket[id]!

        let all_address=hash.keys

        let  index= Int(UInt(getCurrentBlock().timestamp)+randomSeed)%all_address.length-1

        return all_address[index]
    }
    //活动详情
   pub struct ListingDetails { 
        pub var storefrontID: UInt64
        //0 是活动未开始(nft闲置状态),1 掘金中，2掘金活动成功，3掘金活动失败（超时），4（NFT在售卖）
        pub(set) var itemStatus: UInt8
        pub var activeID: UInt64?
        pub var purchased: Bool
        /// The Type of the NonFungibleToken.NFT that is being listed.
        pub let nftType: Type
        /// The ID of the NFT within that type.
        pub let nftID: UInt64
        /// The Type of the FungibleToken that payments must be made in.
        pub let salePaymentVaultType: Type
        /// The amount that must be paid in the specified FungibleToken.
        pub let salePrice: UFix64?

        pub let minPrice: UFix64?
        /// This specifies the division of payment between recipients.
        pub let receiver: Capability<&{FungibleToken.Provider,FungibleToken.Receiver}>

        /// setToPurchased
        /// Irreversibly set this listing as purchased.
        ///
        access(contract) fun setToPurchased() {
            self.purchased = true
        }



        init (
            nftType: Type,
            nftID: UInt64,
            salePaymentVaultType: Type,
            receiver: Capability<&{FungibleToken.Provider,FungibleToken.Receiver}>,
            storefrontID: UInt64,
            salePrice: UFix64?,
            minPrice: UFix64?,
            itemStatus: UInt8,
            activeID: UInt64?
        ) {
            self.storefrontID = storefrontID
            self.purchased = false
            self.nftType = nftType
            self.nftID = nftID
            self.salePaymentVaultType = salePaymentVaultType
            self.activeID=activeID
            if self.activeID==nil{
                self.itemStatus=itemStatus
            }else{
                self.itemStatus=PioneerMarketplace.getActivity(id:activeID!).activeStatus
            }
            
            self.receiver = receiver   

            if activeID==nil{
                 self.salePrice = salePrice
                 self.minPrice=nil
                 assert(salePrice!>0.0, message:"Listing must have non-zero price")
                
            }else{
                 self.salePrice = nil
                 self.minPrice=minPrice
            }       
        }
    }
     pub resource interface ListingPublic {
       
        pub fun borrowNFT(): &NonFungibleToken.NFT

        pub fun purchase(payment: @FungibleToken.Vault): @NonFungibleToken.NFT

        pub fun bid(activeID: UInt64,bidAddr:Address, payment: @FungibleToken.Vault,ticketAmount: UFix64): Bool

        pub fun selectWinner(activeID: UInt64):Bool

        pub fun getDetails(): ListingDetails

        pub fun withdrawBids(activeID:UInt64):Bool

    }

    access(self) fun findValue(arr: [UInt64],value: UInt64):Bool{
        var i=0
        while  i<arr.length {
            if arr[i]==value{
                return true
            }
            i=i+1
        }
        return false
    }

    pub fun getActivity(id: UInt64):Activity{
        pre {
            PioneerMarketplace.activityMapping.containsKey(id):"The activity does not exist, please enter the correct activity ID"
        }
        return PioneerMarketplace.activityMapping[id]!
    }
 
     pub resource Listing: ListingPublic {
        access(self) let details: ListingDetails

        access(contract) let nftProviderCapability: Capability<&{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>

        pub fun borrowNFT(): &NonFungibleToken.NFT {
            let ref = self.nftProviderCapability.borrow()!.borrowNFT(id: self.getDetails().nftID)
            //- CANNOT DO THIS IN PRECONDITION: "member of restricted type is not accessible: isInstance"
            //  result.isInstance(self.getDetails().nftType): "token has wrong type"
            assert(ref.isInstance(self.getDetails().nftType), message: "token has wrong type")
            assert(ref.id == self.getDetails().nftID, message: "token has wrong ID")
            return (ref as &NonFungibleToken.NFT?)!
        }
  
        pub fun getDetails(): ListingDetails {
            return self.details
        }
        access(self) fun setAmountForActivity(id: UInt64,user: Address, amount: UFix64){
            pre {
                PioneerMarketplace.activityUserAmount.containsKey(id):"The activity does not exist, please enter the correct activity ID"
            }

             let useraddr=PioneerMarketplace.activityUserAmount[id]!
             let result=useraddr[user]!
             useraddr.insert(key:user,result+amount)
        }

        access(self) fun  setTicketForActivity(id: UInt64,user: Address, amount: UFix64){
             pre {
                PioneerMarketplace.activityUserTicket.containsKey(id):"The activity does not exist, please enter the correct activity ID"
            }

             let useraddr=PioneerMarketplace.activityUserTicket[id]!
             let result=useraddr[user]!
             useraddr.insert(key:user,result+amount)
        } 
         pub fun bid(activeID: UInt64,bidAddr:Address, payment: @FungibleToken.Vault,ticketAmount: UFix64): Bool {
            //get what activities a bidAddr address has participated in
            let id_keys=PioneerMarketplace.userActivity[bidAddr]!
            let is_exist=PioneerMarketplace.findValue(arr:id_keys,value:activeID)
            //If you have not participated in this activity before
            if !is_exist{
                 id_keys.append(activeID) 
                 PioneerMarketplace.userActivity[bidAddr]=id_keys
            }
            //get a activity details
            let activityDetail=PioneerMarketplace.getActivity(id:activeID)
            // 
            let isPastExpiry: Bool = getCurrentBlock().timestamp >= activityDetail.endTime
            if isPastExpiry{
                panic("The activity has timed out")
            }
            let bidAmount=payment.balance
            if activityDetail.currentAmount>=activityDetail.targetAmount{
                 panic("Fund raising has reached the target")
            }
            if bidAmount%activityDetail.minPartAmount!=ticketAmount{
                panic("buyTokens amount wrong")
            }
            let surplus : UFix64=activityDetail.targetAmount-activityDetail.currentAmount

            self.setAmountForActivity(id:activeID,user:bidAddr,amount:bidAmount)
            self.setTicketForActivity(id:activeID,user:bidAddr,amount:ticketAmount)
            
            assert(activityDetail.receiver != nil, message: "No valid payment receivers")

            activityDetail.receiver.borrow()!.deposit(from: <-payment)
         
            activityDetail.currentAmount=activityDetail.currentAmount+bidAmount

            emit UserBid(activityID: activeID,ticketAmount: ticketAmount,currentAmount: activityDetail.currentAmount)
            
            if activityDetail.currentAmount>=activityDetail.targetAmount{
                emit ActivityTargetReached(activityID:activeID,taragetAmount:activityDetail.targetAmount)
            }
            return true
        }

        pub fun selectWinner(activeID: UInt64):Bool{
            pre {
                self.details.purchased == false: "listing has already been purchased"
                PioneerMarketplace.judgeActivity(id:activeID)==false: "activity is amount "
                PioneerMarketplace.judgePastExpiry(id:activeID)==false: "activity is amount "
            }
            self.details.itemStatus=2
            let randomSeed =UInt(getCurrentBlock().timestamp)
            let bidAddr=PioneerMarketplace.selectWinner(id:activeID, randomSeed: randomSeed)
            let nft <-self.nftProviderCapability.borrow()!.withdraw(withdrawID: self.details.nftID)
            let recipientCollectionRef=getAccount(bidAddr)
                .getCapability(PioneerNFT.CollectionPublicPath)
                .borrow<&{NonFungibleToken.CollectionPublic}>()
                ?? panic("Could not get bidAddr reference to the NFT Collection")

                 recipientCollectionRef.deposit(token: <-nft)
             let activityDetail=PioneerMarketplace.getActivity(id:activeID)
             activityDetail.winBider=bidAddr
             activityDetail.activeStatus=2
            return true
        }
        pub fun withdrawBids(activeID:UInt64):Bool{

            pre{
                PioneerMarketplace.judgePastExpiry(id:activeID)==true: "activity is amount "
                PioneerMarketplace.judgeActivity(id:activeID)==false: "activity is amount "
            }
            self.details.itemStatus=3
            let activityDetail=PioneerMarketplace.getActivity(id:activeID)

            let userarr=PioneerMarketplace.activityUserAmount[activeID]!
            let userAddr=userarr.keys
            var i=0
            activityDetail.activeStatus=2
            while  i<userAddr.length {
                 let bidbalance=userarr[userAddr[i]]!
                 if bidbalance<0.001{
                    continue
                 }
                 let recipientCollectionRef=getAccount(userAddr[i])
                .getCapability(/public/flowTokenReceiver)
                .borrow<&{FungibleToken.Receiver}>()
                ?? panic("Could not get bidAddr reference to the NFT Collection")

                let payment<- activityDetail.receiver.borrow()!.withdraw(amount:bidbalance)

                recipientCollectionRef.deposit(from:<-payment)
                i=i+1
            }
            return true
        }

        pub fun purchase(payment: @FungibleToken.Vault): @NonFungibleToken.NFT {
            pre {
                self.details.purchased == false: "listing has already been purchased"
                payment.isInstance(self.details.salePaymentVaultType): "payment vault is not requested fungible token"
                payment.balance == self.details.salePrice: "payment vault does not contain requested price"
            }
            self.details.setToPurchased()

            let nft <-self.nftProviderCapability.borrow()!.withdraw(withdrawID: self.details.nftID)
            
            assert(nft.isInstance(self.details.nftType), message: "withdrawn NFT is not of specified type")
            assert(nft.id == self.details.nftID, message: "withdrawn NFT does not have specified ID")

       
            var residualReceiver: &{FungibleToken.Receiver}? = nil

            let receiver = self.details.receiver.borrow()

            residualReceiver=receiver

            assert(residualReceiver != nil, message: "No valid payment receivers")


            residualReceiver!.deposit(from: <-payment)
            self.details.itemStatus=0
            emit ListingCompleted(
                listingResourceID: self.uuid,
                storefrontResourceID: self.details.storefrontID,
                purchased: self.details.purchased,
                nftType: self.details.nftType,
                nftID: self.details.nftID
            )
            return <-nft
        }
        /// destructor
        ///
        destroy () {
            // If the listing has not been purchased, we regard it as completed here.
            // Otherwise we regard it as completed in purchase().
            // This is because we destroy the listing in Storefront.removeListing()
            // or Storefront.cleanup() .
            // If we change this destructor, revisit those functions.
            if !self.details.purchased {
                emit ListingCompleted(
                    listingResourceID: self.uuid,
                    storefrontResourceID: self.details.storefrontID,
                    purchased: self.details.purchased,
                    nftType: self.details.nftType,
                    nftID: self.details.nftID
                )
            }
        }
        init (
            nftProviderCapability: Capability<&{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>,
            nftType: Type,
            nftID: UInt64,
            salePaymentVaultType: Type,
            receiver: Capability<&{FungibleToken.Provider,FungibleToken.Receiver}>,
            storefrontID: UInt64,
            salePrice: UFix64?,
            minPrice: UFix64?,
            itemStatus:UInt8,
            activeID: UInt64?
        ) {
            // Store the sale information
            self.details = ListingDetails(
                nftType: nftType,
                nftID: nftID,
                salePaymentVaultType: salePaymentVaultType,
                receiver:receiver,
                storefrontID: storefrontID,
                salePrice:salePrice,
                minPrice:minPrice,
                itemStatus:itemStatus,
                activeID: activeID
            )

            // Store the NFT provider
            self.nftProviderCapability = nftProviderCapability

            // Check that the provider contains the NFT.
            // We will check it again when the token is sold.
            // We cannot move this into a function because initializers cannot call member functions.
            let provider = self.nftProviderCapability.borrow()
            assert(provider != nil, message: "cannot borrow nftProviderCapability")

            // This will precondition assert if the token is not available.
            let nft = provider!.borrowNFT(id: self.details.nftID)
            assert(nft.isInstance(self.details.nftType), message: "token is not of specified type")
            assert(nft.id == self.details.nftID, message: "token does not have specified ID")
        }
    }

    pub resource interface StorefrontManager {
        /// createListing
        /// Allows the Storefront owner to create and insert Listings.
        ///
       
        pub fun removeListing(listingResourceID: UInt64)
    }

    // StorefrontPublic
    // An interface to allow listing and borrowing Listings, and purchasing items via Listings
    // in a Storefront.
    //
    pub resource interface StorefrontPublic {
        pub fun getListingIDs(): [UInt64]
        pub fun borrowListing(listingResourceID: UInt64): &Listing{ListingPublic}?
        pub fun cleanup(listingResourceID: UInt64)
         pub fun createActivity(
            name: String,
            description: String,
            url: String,
            activeStatus: UInt8,
            nftType: Type,
            nftID: UInt64,
            creator: Address,
            createTime: UFix64,
            targetAmount: UFix64,
            currentAmount: UFix64,
            minPartAmount: UFix64,
            divisionCount: UInt64,
            startTime: UFix64,
            endTime: UFix64,
            receiver: Capability<&{FungibleToken.Provider,FungibleToken.Receiver}>,
            externalUrl: String
         ):UInt64

         pub fun createListing(
            nftProviderCapability: Capability<&{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>,
            nftType: Type,
            nftID: UInt64,
            salePaymentVaultType: Type,
            receiver: Capability<&{FungibleToken.Provider,FungibleToken.Receiver}>,
            salePrice: UFix64?,
            minPrice: UFix64?,
            itemStatus:UInt8,
            activeID: UInt64?
         ): UInt64 
        /// removeListing
        /// Allows the Storefront owner to remove any sale listing, acepted or not.
        ///
        pub fun removeListing(listingResourceID: UInt64)
   }

    /// Storefront
    /// A resource that allows its owner to manage a list of Listings, and anyone to interact with them
    /// in order to query their details and purchase the NFTs that they represent.
    ///
    pub resource Storefront:StorefrontManager,StorefrontPublic {
        /// The dictionary of Listing uuids to Listing resources.
        access(self) var listings: @{UInt64: Listing}
        //新增活动 记录到activityMapping
         pub fun createActivity(
            name: String,
            description: String,
            url: String,
            activeStatus: UInt8,
            nftType: Type,
            nftID: UInt64,
            creator: Address,
            createTime: UFix64,
            targetAmount: UFix64,
            currentAmount: UFix64,
            minPartAmount: UFix64,
            divisionCount: UInt64,
            startTime: UFix64,
            endTime: UFix64,
            receiver: Capability<&{FungibleToken.Provider,FungibleToken.Receiver}>,
            externalUrl: String
         ):UInt64{
            let activeID=PioneerMarketplace.nextActivityID
            PioneerMarketplace.nextActivityID=PioneerMarketplace.nextActivityID+1
            let activitydetail=Activity(
                id: activeID,
                name: name,
                description: description,
                url: url,
                activeStatus: activeStatus,
                winBider:nil,
                nftType: nftType,
                nftID: nftID,
                creator: creator,
                createTime: createTime,
                targetAmount: targetAmount,
                currentAmount: currentAmount,
                minPartAmount: minPartAmount,
                divisionCount: divisionCount,
                startTime: startTime,
                endTime: endTime,
                difference: endTime-startTime,
                receiver: receiver,
                externalUrl: externalUrl
            )
            emit ActivityCreated(activityID:activeID)
            PioneerMarketplace.activityMapping[activeID]=activitydetail
            return activeID
         }
         //活动列表
        pub fun createListing(
            nftProviderCapability: Capability<&{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>,
            nftType: Type,
            nftID: UInt64,
            salePaymentVaultType: Type,
            receiver: Capability<&{FungibleToken.Provider,FungibleToken.Receiver}>,
            salePrice: UFix64?,
            minPrice: UFix64?,
            itemStatus:UInt8,
            activeID: UInt64?
         ): UInt64 {
            var status :UInt8=0
            var  listingPrice :UFix64?=nil
            if activeID==nil{
                status=4 //for sale
            }else{
                status=PioneerMarketplace.getActivity(id:activeID!).activeStatus
            }
            
            let listing <- create Listing(
                nftProviderCapability: nftProviderCapability,
                nftType: nftType,
                nftID: nftID,
                salePaymentVaultType: salePaymentVaultType,
                receiver:receiver,
                storefrontID: self.uuid,
                salePrice:salePrice,
                minPrice:minPrice,
                itemStatus:status,
                activeID: activeID
            )

            let listingResourceID = listing.uuid
              if activeID==nil{
                listingPrice = listing.getDetails().salePrice       
              }else{
                listingPrice=PioneerMarketplace.getActivity(id:activeID!).minPartAmount
             }
           

            // Add the new listing to the dictionary.
            let oldListing <- self.listings[listingResourceID] <- listing
            // Note that oldListing will always be nil, but we have to handle it.

            destroy oldListing

            emit ListingAvailable(
                storefrontAddress: self.owner?.address!,
                listingResourceID: listingResourceID,
                nftType: nftType,
                nftID: nftID,
                ftVaultType: salePaymentVaultType,
                price: listingPrice,
                minPrice:listingPrice
            )

            return listingResourceID
        }
       

        pub fun updateStatus(listingResourceID: UInt64,status: UInt8):UInt8{


            return 0
        }
        

        /// removeListing
        /// Remove a Listing that has not yet been purchased from the collection and destroy it.
        ///
        pub fun removeListing(listingResourceID: UInt64) {
            let listing <- self.listings.remove(key: listingResourceID)
                ?? panic("missing Listing")
    
            // This will emit a ListingCompleted event.
            destroy listing
        }

        /// getListingIDs
        /// Returns an array of the Listing resource IDs that are in the collection
        ///
        pub fun getListingIDs(): [UInt64] {
            return self.listings.keys
        }
          
        /// borrowSaleItem
        /// Returns a read-only view of the SaleItem for the given listingID if it is contained by this collection.
        ///
        pub fun borrowListing(listingResourceID: UInt64): &Listing{ListingPublic}? {
            if self.listings[listingResourceID] != nil {
                return &self.listings[listingResourceID] as! &Listing{ListingPublic}?
            } else {
                return nil
            }
        }

        /// cleanup
        /// Remove an listing *if* it has been purchased.
        /// Anyone can call, but at present it only benefits the account owner to do so.
        /// Kind purchasers can however call it if they like.
        ///
        pub fun cleanup(listingResourceID: UInt64) {
            pre {
                self.listings[listingResourceID] != nil: "could not find listing with given id"
            }

            let listing <- self.listings.remove(key: listingResourceID)!
            assert(listing.getDetails().purchased == true, message: "listing is not purchased, only admin can remove")
            destroy listing
        }

        /// destructor
        ///
        destroy () {
            destroy self.listings

            // Let event consumers know that this storefront will no longer exist
            emit StorefrontDestroyed(storefrontResourceID: self.uuid)
        }

        /// constructor
        ///
        init () {
            self.listings <- {}

            // Let event consumers know that this storefront exists
            emit StorefrontInitialized(storefrontResourceID: self.uuid)
        }
    }

    pub fun createStorefront(): @Storefront {
        return <-create Storefront()
    }
    init(){
        self.nextActivityID=1
        self.itemStatus=0
        self.activityMapping={}
        self.activityUserAmount={}
        self.activityUserTicket={}
        self.userActivity={}
        self.StorefrontStoragePath = /storage/NFTStorefront
        self.StorefrontPublicPath = /public/NFTStorefront
        self.StorefrontActivityStoragePath= /storage/NFTActivityStorefront
        self.StorefrontActivityPublicPath= /public/NFTActivityStorefront
        emit NFTStorefrontInitialized()

    }
}