import SoulMadeMain from 0x76b2527585e45db4
import SoulMadeComponent from 0x76b2527585e45db4
import SoulMadePack from 0x76b2527585e45db4

transaction {
  prepare(acct: AuthAccount) {
    // acct.unlink(SoulMadeMain.CollectionPublicPath)
    // acct.unlink(SoulMadeComponent.CollectionPublicPath)
    // acct.unlink(SoulMadePack.CollectionPublicPath)
    // acct.link<&SoulMadeMain.Collection{SoulMadeMain.CollectionPublic}>(SoulMadeMain.CollectionPublicPath, target: SoulMadeMain.CollectionStoragePath)
    // acct.link<&SoulMadeComponent.Collection{SoulMadeComponent.CollectionPublic}>(SoulMadeComponent.CollectionPublicPath, target: SoulMadeComponent.CollectionStoragePath)
    // acct.link<&SoulMadePack.Collection{SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionPublicPath, target: SoulMadePack.CollectionStoragePath)
    // if acct.borrow<&SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath) != nil {
    //     destroy acct.load<@SoulMadeComponent.Collection>(from: SoulMadeComponent.CollectionStoragePath)
    // }
    //
    // if acct.borrow<&SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath) != nil {
    //     destroy acct.load<@SoulMadeMain.Collection>(from: SoulMadeMain.CollectionStoragePath)
    // }
    
    // if acct.borrow<&SoulMadePack.Collection>(from: SoulMadePack.CollectionStoragePath) != nil {
      // destroy acct.load<@SoulMadePack.Collection>(from: SoulMadePack.CollectionStoragePath)
    // }

  }
}