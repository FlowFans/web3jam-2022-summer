import TicketNFT from 0xTicketNFT
import Marketplace from 0xMarketplace


transaction(setAddress: Address){
    let admin: &TicketNFT.Administrator
    prepare(acc: AuthAccount){
        assert(getAccount(setAddress).getCapability<&Marketplace.SaleCollection{Marketplace.SalePublic}>(Marketplace.MarketplacePublicPath).borrow()!=nil)
        self.admin=acc.borrow<&TicketNFT.Administrator>(from: TicketNFT.AdminStoragePath) ?? panic("Account does not store an object(Administrator) at the specified path")
    }
    execute{
        self.admin.setMinterCapability(addr:setAddress)
    }
}
