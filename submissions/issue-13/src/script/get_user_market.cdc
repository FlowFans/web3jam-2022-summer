// import TicketNFT from "../cadence/TicketNFT.cdc"
// import Marketplace from "../cadence/Marketplace.cdc"
import TicketNFT from 0xTicketNFT
import Marketplace from 0xMarketplace
// import TicketNFT from 0xMetadataViews
// import Marketplace from 0xMarketplace
pub struct NFT {
        pub let ids: [UInt64]
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
        pub let owner: Address
        pub let price: UFix64?
        pub let number: UInt64
    init(
        ids: [UInt64],
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
        owner:Address,
        price : UFix64?,
        number: UInt64
        ){
            self.ids=ids
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
            self.owner=owner
            self.price=price
            self.number=number
        }
}
pub fun main(owner: Address): [NFT] {
    let arr :[NFT]=[]
     let keys=TicketNFT.middleOwner.keys
     var i=0
     while  i<keys.length {
        if TicketNFT.templateAddress.containsKey(keys[i]){
            let value=TicketNFT.templateAddress[keys[i]]!
             var j =0
              while j<value.length {
                if get_detail_message(sellerAddress:owner,templateID:value[j])!=nil{
                    arr.append(get_detail_message(sellerAddress:owner,templateID:value[j])!)
                }
                j=j+1
              } 
        }
        i=i+1
     }
    return arr
}
pub fun get_detail_message(sellerAddress: Address, templateID: UInt64): NFT? {
//TicketNFT.CollectionPublicPath
    let collectionRef= getAccount(sellerAddress).getCapability(Marketplace.MarketplacePublicPath)
        .borrow<&{Marketplace.SalePublic}>()
        ?? panic("Could not get public sale reference")
    let listArr=collectionRef.getIDs()

    var arr :[UInt64]=[]
    if TicketNFT.tickeMapping[templateID]==nil{
        arr=[]
    }else{
        arr=TicketNFT.tickeMapping[templateID]!
    }
    let res=getMixed(arr1:arr,arr2:listArr)

    if res.length<=0{
        return nil
    }

     let nft =collectionRef.borrowTicketNFT(id: res[0]) ?? panic("Could not borrow a reference to the specified ticket")
    let TicketNFTView = nft.resolveView(Type<TicketNFT.TicketDetail>())!

    let TicketNFTDisplay =TicketNFTView as! TicketNFT.TicketDetail
    let price= Marketplace.ItemsPrice[res[0]]
   
    return NFT(
        ids: res,
        templateID: TicketNFTDisplay.templateID,
        typeID: TicketNFTDisplay.typeID,
        name: TicketNFTDisplay.name,
        desc: TicketNFTDisplay.desc,
        ticketType: TicketNFTDisplay.ticketType,
        url: TicketNFTDisplay.url,
        performDate: TicketNFTDisplay.performDate,
        artists: TicketNFTDisplay.artists,
        field: TicketNFTDisplay.field,
        fieldImg: TicketNFTDisplay.fieldImg,
        detailAddress: TicketNFTDisplay.detailAddress,
        city: TicketNFTDisplay.city,
        owner:sellerAddress,
        price:price,
        number:UInt64(res.length)
    )
    
}


  
 pub fun getMixed(arr1:[UInt64],arr2:[UInt64]):[UInt64]{
    var arr1=bubble_sort(arr:arr1)
    var arr2=bubble_sort(arr:arr2)
    var i=0
    var j=0
    var res:[UInt64]=[]
    while(i < arr1.length && j < arr2.length) {
            if(arr1[i] < arr2[j]) {
                i=i+1
            }
            else if(arr1[i] > arr2[j]) {
                j=j+1
            }
            else {
                if(!findValue(arr:res,value:arr1[i])) {
                    res.append(arr1[i])
                }
                i=i+1
                j=j+1
            }
        }
    return res
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



pub fun  bubble_sort(arr:[UInt64]):[UInt64]{
    var n= arr.length
    var  temp: UInt64=0
    while (n>0){
        var j=0
        while(j<n-1)
            {
            if(arr[j]>arr[j+1])
            {
                temp=arr[j]
                arr[j]=arr[j+1];
                arr[j+1]=temp;
            }
            j=j+1
            }

        n=n-1
    }
    return arr
}


