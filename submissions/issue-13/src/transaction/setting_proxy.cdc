import TicketNFT from 0xTicketNFT



transaction(){
    let  proxy :&TicketNFT.MinterProxy
    prepare(account :AuthAccount){

        if account.borrow<&TicketNFT.MinterProxy>(from: TicketNFT.ProxyStoragePath)==nil{
            let proxy <- TicketNFT.createMinterProxy()
            account.save(<-proxy,to:TicketNFT.ProxyStoragePath)
            account.link<&TicketNFT.MinterProxy>(TicketNFT.ProxyPublicPath, target: TicketNFT.ProxyStoragePath)
        }
        self.proxy=account.borrow<&TicketNFT.MinterProxy>(from :TicketNFT.ProxyStoragePath) ?? panic("Account does not store an object(MinterProxy) at the specified path")
    }
    execute{


    }

}