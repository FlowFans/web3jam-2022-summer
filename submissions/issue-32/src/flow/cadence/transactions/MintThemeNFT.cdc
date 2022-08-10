import Racenumber from 0xf8d6e0586b0a20c7

transaction(hostAddr:Address, gameUId:UInt64,background:String) {

  prepare(acct: AuthAccount) {
    if !acct.getCapability<&Racenumber.ThemeCollection{Racenumber.ThemeCollectionPublic}>(Racenumber.ThemeNFTCollectionPublicPath).check(){
        let collection <- Racenumber.createEmptyThemeCollection()   
        acct.save<@Racenumber.ThemeCollection>(<- collection, to: Racenumber.ThemeNFTCollectionStoragePath)
        acct.link<&Racenumber.ThemeCollection{Racenumber.ThemeCollectionPublic}>(Racenumber.ThemeNFTCollectionPublicPath, target:Racenumber.ThemeNFTCollectionStoragePath)
        log("Theme Collection created!")
    }
    let hostAcct = getAccount(hostAddr)
    let gamesRef = acct.getCapability<&Racenumber.Games{Racenumber.GamesPublic}>(Racenumber.GamesPublicPath).borrow() ?? panic("Events resource not found")
    let gameRef = gamesRef.borrowPublicGameRef(GameUId: gameUId)
    let collectionCap = acct.getCapability<&Racenumber.Collection{Racenumber.CollectionPublic}>(Racenumber.NumberNFTCollectionPublicPath)
    let collectionRef = collectionCap.borrow() ?? panic("Your address hasn't initialized game collection!")
    let numberNFT = collectionRef.borrowNumberNFT(id:gameUId) 
    let themeCollectionRef = acct.getCapability<&Racenumber.ThemeCollection{Racenumber.ThemeCollectionPublic}>(Racenumber.ThemeNFTCollectionPublicPath).borrow() ?? panic("Theme Collection not found")
    gameRef.mintTheme(collectionCap:collectionCap,gameRef:gameRef,num:numberNFT.num,background:background, recipient: themeCollectionRef)
    log("Them NFT minted!")
  }

  execute {
    
  }
}
