// import NonFungibleToken from "./NonFungibleToken.cdc"
// import MetadataViews from "./MetadataViews.cdc"


import NonFungibleToken from "./NonFungibleToken.cdc"

import MetadataViews from "./MetadataViews.cdc"

pub contract TicketNFT: NonFungibleToken{

    access(all) var tickeTemplate: {UInt64: TicketNFTtemplate}
    access(all) var tickeMapping:{UInt64:[UInt64]}
    access(all) var templateAddress: {Address:[UInt64]}
    access(all) var ticketAddress:{Address:[UInt64]}
    pub var middleOwner :{Address:Bool}
    pub var totalSupply: UInt64
    pub var nextTemplateID: UInt64
    //种类
    pub var classify: {String:UInt64}
    //按照时间
    pub var timeSorted: {UFix64:[UInt64]}
    //按照城市
    pub var citySorted: {String:[UInt64]}
    //类型
    pub var typeSorted: {String: [UInt64]}
    pub var fieldSorted: {String: [UInt64]}
    pub event ContractInitialized()
    pub event Withdraw(id: UInt64, from: Address?)
    pub event Deposit(id: UInt64, to: Address?)

    pub event TicketNFTMinted(id :UInt64,templateID: UInt64)
    

    pub event MinterCreated()

    pub let CollectionStoragePath: StoragePath
    pub let CollectionPublicPath: PublicPath
    pub let MinterStoragePath: StoragePath
    
    pub let MinterPrivatePath: PrivatePath

    pub let AdminStoragePath:StoragePath
    pub let AdminPrivatePath: PrivatePath

    pub let ProxyStoragePath: StoragePath

    pub let ProxyPublicPath: PublicPath

    pub let ProxyPrivatePath: PrivatePath

     pub struct TicketNFTtemplate {
        pub let id: UInt64
        pub let name: String
        pub let desc: String
        pub let ticketType: String
        pub let url: String
        pub let performDate: UFix64
        pub let artists: [String]
        pub let field: String
        pub let fieldImg: String
        pub let detailAddress: String
        pub let city: String

        init( 
        id: UInt64,
        name: String,
        desc: String,
        ticketType: String,
        url: String,
        performDate: UFix64,
        artists: [String],
        field: String,
        fieldImg: String,
        detailAddress: String,
        city: String,
        ){
            self.id=id
            self.name=name
            self.desc=desc
            self.ticketType=ticketType
            self.url=url
            self.performDate=performDate
            self.artists=artists
            self.field=field
            self.fieldImg=fieldImg
            self.detailAddress=detailAddress
            self.city=city
        }

    }


      pub struct TicketDetail {
        pub let id: UInt64
        pub let templateID: UInt64
        pub let typeID: UInt64
        pub let name: String
        pub let desc: String
        pub let ticketType: String
        pub let url: String
        pub let performDate: UFix64
        pub let artists: [String]
        pub let field: String
        pub let fieldImg: String
        pub let detailAddress: String
        pub let city: String
      

        init( 
        id: UInt64,
        templateID: UInt64,
        typeID: UInt64,
        name: String,
        desc: String,
        ticketType: String,
        url: String,
        performDate: UFix64,
        artists: [String],
        field: String,
        fieldImg: String,
        detailAddress: String,
        city: String,
        ){
            self.id=id
            self.templateID=templateID
            self.typeID=typeID
            self.name=name
            self.desc=desc
            self.ticketType=ticketType
            self.url=url
            self.performDate=performDate
            self.artists=artists
            self.field=field
            self.fieldImg=fieldImg
            self.detailAddress=detailAddress
            self.city=city
        }

    }


  

    pub resource NFT: NonFungibleToken.INFT, MetadataViews.Resolver {
         pub let id: UInt64
         pub let data: TicketNFTtemplate
         pub let templateID: UInt64
         pub let typeTicketID: UInt64

         init(tickeTemplateID: UInt64,totalTicket: UInt64){
            pre {
                TicketNFT.tickeTemplate[tickeTemplateID]!=nil: "Could not create ticketNFT: template does not exist."
            }
            self.templateID=tickeTemplateID
            self.typeTicketID=totalTicket
            let ticketNFT = TicketNFT.tickeTemplate[tickeTemplateID]!
            self.id = TicketNFT.totalSupply
            self.data=ticketNFT
            if TicketNFT.tickeMapping.containsKey(tickeTemplateID)!=nil{
                var arr :[UInt64]=[]
                if TicketNFT.tickeMapping[tickeTemplateID]!=nil{
                      arr=TicketNFT.tickeMapping[tickeTemplateID]!
                }
                
                arr.append(self.id)

                TicketNFT.tickeMapping[tickeTemplateID]=arr
            }else{
                TicketNFT.tickeMapping[tickeTemplateID]=[self.id]
            }
            TicketNFT.totalSupply = TicketNFT.totalSupply + 1

            emit TicketNFTMinted(id:self.id,templateID:tickeTemplateID)
         }

         pub fun description():String{
            return self.data.desc;
         }
         pub fun name():String  {
            return self.data.name
         }
        pub fun getViews(): [Type] {
            return [
                Type<MetadataViews.Display>(),
                Type<TicketNFTtemplate>(),
                Type<TicketDetail>()
            ]
        }


         pub fun resolveView(_ view: Type): AnyStruct? {
            switch view {
                case Type<MetadataViews.Display>():
                    return MetadataViews.Display(
                        name: self.name(),
                        description: self.description(),
                        thumbnail: MetadataViews.HTTPFile(url:self.data.url)
                    )
                 case Type<TicketNFTtemplate>():
                    return  TicketNFTtemplate(
                        id: self.id ,
                        name: self.data.name,
                        desc: self.data.desc,
                        ticketType: self.data.ticketType,
                        url: self.data.url,
                        performDate: self.data.performDate,
                        artists: self.data.artists,
                        field: self.data.field,
                        fieldImg: self.data.fieldImg,
                        detailAddress: self.data.detailAddress,
                        city: self.data.city
                    )
                case Type<TicketDetail>():
                    return  TicketDetail(
                            id: self.id ,
                            templateID: self.templateID,
                            typeID: self.typeTicketID,
                            name: self.data.name,
                            desc: self.data.desc,
                            ticketType: self.data.ticketType,
                            url: self.data.url,
                            performDate: self.data.performDate,
                            artists: self.data.artists,
                            field: self.data.field,
                            fieldImg: self.data.fieldImg,
                            detailAddress: self.data.detailAddress,
                            city: self.data.city
                        )
            }
            return nil
         }

    }

    pub resource interface TicketNFTCollectionPublic {
        pub fun deposit(token: @NonFungibleToken.NFT)
        pub fun getIDs(): [UInt64]
        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT
        pub fun batchDeposit(tokens: @NonFungibleToken.Collection)
        pub fun borrowTicketNFT(id: UInt64): &TicketNFT.NFT? {
            // If the result isn't nil, the id of the returned reference
            // should be the same as the argument to the function
            post {
                (result == nil) || (result?.id == id):
                    "Cannot borrow KittyItem reference: The ID of the returned reference is incorrect"
            }
        }
    }

    pub resource Collection: TicketNFTCollectionPublic, NonFungibleToken.Provider, NonFungibleToken.Receiver, NonFungibleToken.CollectionPublic, MetadataViews.ResolverCollection {
        pub var ownedNFTs: @{UInt64: NonFungibleToken.NFT}

        init () {
            self.ownedNFTs <- {}
        }

        // withdraw removes an NFT from the collection and moves it to the caller
        pub fun withdraw(withdrawID: UInt64): @NonFungibleToken.NFT {
            let token <- self.ownedNFTs.remove(key: withdrawID) ?? panic("missing NFT")

            emit Withdraw(id: token.id, from: self.owner?.address)

            return <-token
        } 

        pub fun batchWithdraw(ids: [UInt64]): @NonFungibleToken.Collection {
            // Create a new empty Collection
            var batchCollection <- create Collection()
            
            // Iterate through the ids and withdraw them from the Collection
            for id in ids {
                batchCollection.deposit(token: <-self.withdraw(withdrawID: id))
            }
            
            return <-batchCollection
        }
        

        // deposit takes a NFT and adds it to the collections dictionary
        // and adds the ID to the id array
        pub fun deposit(token: @NonFungibleToken.NFT) {
            let token <- token as! @TicketNFT.NFT

            let id: UInt64 = token.id

            // add the new token to the dictionary which removes the old one
            let oldToken <- self.ownedNFTs[id] <- token

            emit Deposit(id: id, to: self.owner?.address)

            destroy oldToken
        }

       

        pub fun batchDeposit(tokens: @NonFungibleToken.Collection) {

            // Get an array of the IDs to be deposited
            let keys = tokens.getIDs()

            // Iterate through the keys in the collection and deposit each one
            for key in keys {
                self.deposit(token: <-tokens.withdraw(withdrawID: key))
            }

            // Destroy the empty Collection
            destroy tokens
        }

       

        // getIDs returns an array of the IDs that are in the collection
        pub fun getIDs(): [UInt64] {
            return self.ownedNFTs.keys
        }

        // borrowNFT gets a reference to an NFT in the collection
        // so that the caller can read its metadata and call its methods
        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT {
            
            return (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
        }
 
        pub fun borrowTicketNFT(id: UInt64): &TicketNFT.NFT? {
            if self.ownedNFTs[id] != nil {
                // Create an authorized reference to allow downcasting
                let ref = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
                return ref as! &TicketNFT.NFT
            }

            return nil
        }
     
        pub fun borrowViewResolver(id: UInt64): &AnyResource{MetadataViews.Resolver} {
            let nft = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
            let TicketNFT = nft as! &TicketNFT.NFT
            return TicketNFT as &AnyResource{MetadataViews.Resolver}
        }

        destroy() {
            destroy self.ownedNFTs
        }
    }

    pub fun createEmptyCollection(): @NonFungibleToken.Collection {
        return <- create Collection()
    }

    pub resource NFTMinter{
        pub var totalTicket: UInt64
        pub fun createTicketTemplate(
            name: String,
            desc: String,
            ticketType: String,
            url: String,
            performDate: UFix64,
            artists: [String],
            field: String,
            fieldImg: String,
            detailAddress: String,
            city: String): UInt64 {
            pre{
                TicketNFT.minterProxyCapability.containsKey(self.owner!.address): "Could not minterProxyCapability does not exist."
            }
            let classify_name= fun (): String{
                var  i=0
                var  str =""
                while  i<artists.length{
                    str.concat(artists[i])
                    i=i+1
                }
                return  str.concat(performDate.toString())
            }
            let newID=TicketNFT.nextTemplateID
             if TicketNFT.templateAddress.containsKey(self.owner!.address)!=nil{
                var  arr :[UInt64]=[]
                if TicketNFT.templateAddress[self.owner!.address]==nil{
                    arr.append(newID)
                    TicketNFT.templateAddress[self.owner!.address]=arr
                }else{
                    arr=TicketNFT.templateAddress[self.owner!.address]!
                    arr.append(newID)
                    TicketNFT.templateAddress[self.owner!.address]=arr
                }
                
            }else{
                TicketNFT.templateAddress[self.owner!.address]=[newID]
            }
            TicketNFT.tickeTemplate[newID]=TicketNFTtemplate(id: newID,
                                                                name: name,
                                                                desc: desc,
                                                                ticketType: ticketType,
                                                                url: url,
                                                                performDate: performDate,
                                                                artists: artists,
                                                                field: field,
                                                                fieldImg: fieldImg,
                                                                detailAddress: detailAddress,
                                                                city: city)
            TicketNFT.setField(tickeTemplateID:newID)
            TicketNFT.setCity(tickeTemplateID:newID)
            TicketNFT.setField(tickeTemplateID:newID)
            TicketNFT.settype(tickeTemplateID:newID)
            //]
            TicketNFT.nextTemplateID=TicketNFT.nextTemplateID+1
            return newID
         }

         pub fun mintTicketNFT(tickeTemplateID: UInt64): @NFT{
            pre {
                TicketNFT.minterProxyCapability.containsKey(self.owner!.address): "Could not minterProxyCapability does not exist."
                TicketNFT.tickeTemplate.containsKey(tickeTemplateID): "Could not mint dappy: template does not exist."   
            }
            
            let newTicketID=self.totalTicket
            self.totalTicket=self.totalTicket+1
            return <- create NFT(tickeTemplateID:tickeTemplateID,totalTicket:newTicketID) 
         }

        pub fun mintMutiTicketNFT(tickeTemplateID: UInt64,quantity: UInt64): @Collection{
                pre {
                    TicketNFT.minterProxyCapability.containsKey(self.owner!.address): "Could not minterProxyCapability does not exist."
                    TicketNFT.tickeTemplate.containsKey(tickeTemplateID): "Could not mint dappy: template does not exist."  
                }
                let newCollection <- create Collection()

                var i: UInt64 = 0
                while  i< quantity{
                    newCollection.deposit(token:<-self.mintTicketNFT(tickeTemplateID:tickeTemplateID))
                    i=i+1
                }
                return <- newCollection
         }
         init(){
            self.totalTicket=0
         }

    }

    access(self) fun setCity(tickeTemplateID: UInt64){
        pre {
            TicketNFT.tickeTemplate.containsKey(tickeTemplateID)!=nil: ""
        }
         let data =  TicketNFT.tickeTemplate[tickeTemplateID]!
         var arr :[UInt64]=[]
         if TicketNFT.citySorted[data.city]==nil{
            arr=[]
         }else{
            arr=TicketNFT.citySorted[data.city]!
         }
 
         if TicketNFT.citySorted.containsKey(data.city)!=nil&& TicketNFT.findValue(arr:arr,value:tickeTemplateID)==false {
                arr.append(tickeTemplateID)
                TicketNFT.citySorted[data.city]=arr
        }else{
             if TicketNFT.citySorted.containsKey(data.city)==nil{
                TicketNFT.citySorted[data.city]=[tickeTemplateID]
            }
        }
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

     access(self) fun setTime(tickeTemplateID: UInt64){
        pre {
            TicketNFT.tickeTemplate.containsKey(tickeTemplateID)!=nil: ""
        }
        let data =  TicketNFT.tickeTemplate[tickeTemplateID]!
        var arr:[UInt64]=[] 
        if TicketNFT.timeSorted[data.performDate]==nil{
            arr=[]
        }else{
            arr=TicketNFT.timeSorted[data.performDate]!
        }
         if TicketNFT.timeSorted.containsKey(data.performDate)!=nil&& TicketNFT.findValue(arr:arr,value:tickeTemplateID)==false{
                arr.append(tickeTemplateID)
                TicketNFT.timeSorted[data.performDate]=arr
        }else{
            if TicketNFT.timeSorted.containsKey(data.performDate)==nil{
                TicketNFT.timeSorted[data.performDate]=[tickeTemplateID]
            }
        }
    }


    access(self) fun setField(tickeTemplateID: UInt64){
        pre {
            TicketNFT.tickeTemplate.containsKey(tickeTemplateID)!=nil: ""
        }
        //let data
        //TicketNFT.TicketNFTtemplate
        let data =  TicketNFT.tickeTemplate[tickeTemplateID]!
        var arr :[UInt64]=[]
        if TicketNFT.fieldSorted[data.field]==nil{
            arr=[]
        }else{
            arr=TicketNFT.fieldSorted[data.field]!
        }

        if TicketNFT.fieldSorted.containsKey(data.field)!=nil && TicketNFT.findValue(arr:arr,value:tickeTemplateID)==false{
                arr.append(tickeTemplateID)
                TicketNFT.fieldSorted[data.field]=arr
        }else{
            if TicketNFT.fieldSorted.containsKey(data.field)==nil{
                    TicketNFT.fieldSorted[data.field]=[tickeTemplateID]
            }
        }
    }


    access(self) fun settype(tickeTemplateID: UInt64){
        pre {
            TicketNFT.tickeTemplate.containsKey(tickeTemplateID)!=nil: ""
        }
        let data =  TicketNFT.tickeTemplate[tickeTemplateID]!
         var arr:[UInt64]=[] 
        if TicketNFT.typeSorted[data.ticketType]==nil{
            arr=[]
        }else{
            arr=TicketNFT.typeSorted[data.ticketType]!
        }
         if TicketNFT.typeSorted.containsKey(data.ticketType)!=nil && TicketNFT.findValue(arr:arr,value:tickeTemplateID)==false{
                arr.append(tickeTemplateID)
                TicketNFT.typeSorted[data.ticketType]=arr
        }else{
            if TicketNFT.typeSorted.containsKey(data.ticketType)==nil{
                    TicketNFT.typeSorted[data.ticketType]=[tickeTemplateID]
            }
        }
    }

    pub  resource MinterProxy {
        pub fun setNFTMinter():@NFTMinter{
            pre {
                TicketNFT.minterProxyCapability.containsKey(self.owner!.address): "Could not minterCapability does not exist."    
            }
            return <- create NFTMinter()
        }        
    }
    pub fun createMinterProxy(): @MinterProxy {   
        return <- create MinterProxy()
    }
   
    access(self)  var minterProxyCapability: {Address:Bool}
 
    pub resource Administrator {
        pub fun createNewMinter(): @NFTMinter {
             emit MinterCreated()
            return <- create NFTMinter()
        }
        pub fun setMinterCapability(addr: Address) {
            TicketNFT.middleOwner[addr]=true
            TicketNFT.minterProxyCapability[addr]=true
        }
        pub fun removeMinterCapability(addr: Address):Bool?{
            TicketNFT.middleOwner[addr]=false
            return TicketNFT.minterProxyCapability.remove(key: addr)
        }
        pub fun judgeCapabilityExist(addr: Address):Bool{
            return TicketNFT.minterProxyCapability.containsKey(addr)
        }

    }


    init(){
        self.totalSupply=0
        self.nextTemplateID=0
        self.classify={}
        self.middleOwner={}
        self.tickeTemplate={}
        self.tickeMapping={}
        self.timeSorted={}
        self.citySorted={}
        self.typeSorted={}
        self.fieldSorted={}
        self.ticketAddress={}
        self.templateAddress={}
        self.minterProxyCapability={}
        self.CollectionStoragePath = /storage/ticketNFTCollection
        self.CollectionPublicPath = /public/ticketNFTCollection
        self.MinterStoragePath = /storage/ticketNFTMinter
        self.MinterPrivatePath= /private/ticketNFTMinter
        self.AdminStoragePath= /storage/AdminStoragePath
        self.AdminPrivatePath= /private/ticketNFTAdminPrivatePath
        self.ProxyStoragePath=/storage/ticketNFTProxyStoragePath
        self.ProxyPublicPath=/public/ticketNFTProxyPublicPath
        self.ProxyPrivatePath=/private/ticketNFTProxyPrivatePath
        


        let admin <- create Administrator()
        self.account.save(<-admin, to: self.AdminStoragePath)

        // let minter <- create NFTMinter()

        // self.account.save(<-minter, to: self.MinterStoragePath)
        // self.account.link<&NFTMinter>(self.MinterPrivatePath,target: self.MinterStoragePath)



        emit  ContractInitialized()

    }

  


}

