import TicketNFT from 0xTicketNFT



transaction(cancelAddress: Address){
    let admin: &TicketNFT.Administrator
    let minterCap: Capability<&TicketNFT.NFTMinter>
    prepare(acc: AuthAccount){
        self.admin=acc.borrow<&TicketNFT.Administrator>(from: TicketNFT.AdminStoragePath) ?? panic("Account does not store an object(Administrator) at the specified path")
        self.minterCap=acc.getCapability<&TicketNFT.NFTMinter>(TicketNFT.MinterPrivatePath)
    }
    execute{
        self.admin.removeMinterCapability(addr:cancelAddress)
    }
}
