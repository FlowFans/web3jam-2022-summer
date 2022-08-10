import TicketNFT from 0xTicketNFT



transaction(setAddr: Address){

    let privMinter: Capability<&TicketNFT.NFTMinter>
    let proxy: &TicketNFT.MinterProxy
    let admin: &TicketNFT.Administrator
    let adminCap: Capability<&TicketNFT.Administrator>
    prepare(account :AuthAccount){

        self.admin=account.borrow<&TicketNFT.Administrator>(from: TicketNFT.AdminStoragePath) ?? panic("Account does not store an object(Administrator) at the specified path")
        self.adminCap=account.getCapability<&TicketNFT.Administrator>(TicketNFT.AdminPrivatePath)
        if account.borrow<&TicketNFT.MinterProxy>(from: TicketNFT.ProxyStoragePath)==nil{

        }

        self.proxy=account.borrow<&TicketNFT.MinterProxy>(from: TicketNFT.ProxyStoragePath) ?? panic("Account does not store an object(MinterProxy) at the specified path")

         self.privMinter=account.getCapability<&TicketNFT.NFTMinter>(TicketNFT.MinterPrivatePath)

    }
    execute{

        //self.proxy.setMinterCapability(addr:setAddr,capability:self.privMinter)

    }

}