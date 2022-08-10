import TicketNFT from 0xTicketNFT
import Marketplace from 0xMarketplace

pub var nftarr:{UInt64:NFT}={}

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


pub fun main(cityName:String,typeName:String,left:UFix64,right:UFix64):[NFT]{

    let ids=getDataMessage(cityName:cityName,typeName:typeName,left:left,right:right)

    return getNFTdetails(ids:ids)
}

pub fun getNFTdetails(ids :[UInt64]): [NFT] {
    let arr :[NFT]=[]
     let keys=TicketNFT.middleOwner.keys
     var i=0
     while  i<keys.length {
        if TicketNFT.templateAddress.containsKey(keys[i]){
            let value=TicketNFT.templateAddress[keys[i]]!
            let res=getMixed(arr1: value, arr2: ids)
            var j =0
              while j<res.length {
                if get_detail_message(sellerAddress:keys[i],templateID:res[j])!=nil{
                    arr.append(get_detail_message(sellerAddress:keys[i],templateID:res[j])!)
                }
                j=j+1
              } 
        }
        i=i+1
     }
    return arr
}
access(self) fun getDataMessage(cityName:String,typeName:String,left:UFix64,right:UFix64):[UInt64]{
    var cityids:[UInt64]=[]
    var typeids:[UInt64]=[]
    var timeids:[UInt64]=[]
    if cityName==""{
        cityids=getAllCityIds()
    }else{
        cityids=getCityIds(cityName:cityName)
    }
    if typeName==""{
        typeids=getAllTypeIds()
    }else{
        typeids=getTypeIds(typeName:typeName)
    }
    if left==0.0 && right==0.0{
        timeids=getAllTimeIds()
    }else{
        timeids=getTime(left:left,right:right)
    }
    let result1=getMixed(arr1:cityids,arr2:typeids)
    let result2=getMixed(arr1:result1,arr2:timeids)
    return result2
}

access(self) fun getCityIds(cityName:String):[UInt64]{
    var cityids:[UInt64]=[]

    if TicketNFT.citySorted[cityName]==nil {
        cityids=[]
    }else{
        cityids=TicketNFT.citySorted[cityName]!
    }
    return cityids
}
access(self) fun getAllCityIds():[UInt64]{
    let cityids=TicketNFT.citySorted.keys
    var i=0
    var res:[UInt64]=[]
    while i<cityids.length{
        if getCityIds(cityName:cityids[i]).length>=0{
             res.appendAll(getCityIds(cityName:cityids[i]))
        }
        i=i+1  
    }
    return res
}
access(self) fun getAllTypeIds():[UInt64]{
    let typeids=TicketNFT.typeSorted.keys
    var i=0
    var res:[UInt64]=[]
    while i<typeids.length{
        if getTypeIds(typeName:typeids[i]).length>=0{
             res.appendAll(getTypeIds(typeName:typeids[i]))
        }
        i=i+1  
    }
    return res
}
access(self) fun getTypeIds(typeName:String):[UInt64]{
    var typeids:[UInt64]=[]

    if TicketNFT.typeSorted[typeName]==nil{
        typeids=[]
    }else{
        typeids=TicketNFT.typeSorted[typeName]!
    }
    return typeids
}

access(self) fun getAllTimeIds():[UInt64]{
    let timeids=TicketNFT.timeSorted.keys
    if timeids.length<=0{
        return []
    }
    let restime=bubble_sort2(arr: timeids)
    let left=restime[0]
    let right=restime[timeids.length-1]
   return getTime(left:left,right:right)
     
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

access(self) fun getTime(left:UFix64,right:UFix64):[UInt64]{
    //var timeArr:[UFix64]=[]
   let timeArr= TicketNFT.timeSorted.keys
    let restime=bubble_sort2(arr: timeArr)

    if timeArr.length<=0{
        return []
    }

    return calcTimeSort(arr:restime,left:left,right:right)
  
}
access(self) fun calcTimeSort(arr: [UFix64],left:UFix64,right:UFix64):[UInt64]{
    let  two_index=getRange(arr: arr, left: left, right: right)
    let total_sum=fromTimeSortGetData(arr:arr,range:two_index)
    return total_sum
}

access(self) fun fromTimeSortGetData(arr: [UFix64],range:[UInt64]):[UInt64]{
    if range.length<2{
        return []
    }
    var left=range[0]
    var right=range[1]
    var res :[UInt64]=[]

    while left<=right {
        if TicketNFT.timeSorted.containsKey(arr[left]){
             res.appendAll(TicketNFT.timeSorted[arr[left]]!)
        }
        left=left+1
    }
    return res
}
access(self) fun getRange(arr: [UFix64],left:UFix64,right:UFix64):[UInt64]{
    if arr.length<=0{
        return []
    }
    var l=0
    var r=arr.length-1
    while l<r{
            var mid=(l+r)>>1
            if arr[mid]>=left{
                r=mid
            }else{
                l=mid+1
            }
    }
    var temp=l
     l=r
     r=arr.length-1
    while l<r{
        var mid=(l+r+1)>>1
        if arr[mid]<=right{
            l=mid
        }else{
            r=mid-1
        }
    }
   return [UInt64(temp),UInt64(r)]
}


access(self) fun findMaxCollection(arr: [UFix64],left: UFix64,right:UFix64):[UInt64]{
        var i=0
        var res:[UInt64]=[]
        TicketNFT.timeSorted.keys
        while i<arr.length {
            if arr[i]>=left &&  arr[i]<=right{
                res.appendAll(TicketNFT.timeSorted[arr[i]]!)
            }
            i=i+1
        }
        return res
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

pub fun  bubble_sort2(arr:[UFix64]):[UFix64]{
    var n= arr.length
    var  temp: UFix64=0.0
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