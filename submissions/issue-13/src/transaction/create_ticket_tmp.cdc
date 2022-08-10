// createTicketTemplate


import TicketNFT from 0xTicketNFT

import NonFungibleToken from 0xNonFungibleToken


transaction(recipient:Address,name: String,desc:String,ticketType: String, url: String, performDate: UFix64, artists: [String],
field: String,fieldImg: String,detailAddress: String,city: String){
    

    let  proxy :&TicketNFT.MinterProxy
    let ownerMinter :&TicketNFT.NFTMinter
    prepare(account: AuthAccount){
        assert(TicketNFT.middleOwner.containsKey(account.address), message: "You are not authorized as MinterProxy")
        if account.borrow<&TicketNFT.MinterProxy>(from: TicketNFT.ProxyStoragePath) == nil {
            let collection <- TicketNFT.createMinterProxy() as! @TicketNFT.MinterProxy
            account.save(<-collection, to: TicketNFT.ProxyStoragePath)
        }
        self.proxy=account.borrow<&TicketNFT.MinterProxy>(from :TicketNFT.ProxyStoragePath) ?? panic("Account does not store an object(MinterProxy) at the specified path")
        if account.borrow<&TicketNFT.NFTMinter>(from: TicketNFT.MinterStoragePath)==nil{
            let minter<-self.proxy.setNFTMinter() 
            account.save(<-minter, to: TicketNFT.MinterStoragePath)
        }
        self.ownerMinter=account.borrow<&TicketNFT.NFTMinter>(from: TicketNFT.MinterStoragePath)??panic("Account does not store an object(MinterStoragePath) at the specified path")

        if account.borrow<&TicketNFT.Collection>(from: TicketNFT.CollectionStoragePath) == nil {

            // create a new TopShot Collection
            let collection <- TicketNFT.createEmptyCollection() as! @TicketNFT.Collection

            // Put the new Collection in storage
            account.save(<-collection, to: /storage/TicketNFTCollection)

            // create a public capability for the collection
            account.link<&{NonFungibleToken.CollectionPublic, TicketNFT.TicketNFTCollectionPublic}>(TicketNFT.CollectionPublicPath, target: TicketNFT.CollectionStoragePath)
        }

        

    }

    execute{

        let templateID=self.ownerMinter.createTicketTemplate(name: name,
                                                                desc: desc,
                                                                ticketType: ticketType,
                                                                url: url,
                                                                performDate: performDate,
                                                                artists: artists,
                                                                field: field,
                                                                fieldImg: fieldImg,
                                                                detailAddress: detailAddress,
                                                                city: city)

        let ticketNFT<-self.ownerMinter.mintTicketNFT(tickeTemplateID:templateID)
        let receiverRef =  getAccount(recipient).getCapability(TicketNFT.CollectionPublicPath).borrow<&{TicketNFT.TicketNFTCollectionPublic}>()
            ?? panic("Cannot borrow a reference to the recipient's moment collection")
           receiverRef.deposit(token: <-ticketNFT)
    }
}

