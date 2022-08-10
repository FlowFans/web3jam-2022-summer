import Racenumber from 0xf8d6e0586b0a20c7
transaction(gameUId:UInt64,imageHash:String,templateType:String, gameType:String,slogan:String) {

  prepare(acct: AuthAccount) {
    let gamesref = acct.borrow<&Racenumber.Games>(from: Racenumber.GamesStoragePath) ?? panic("Games resource not found")
    let gameRef = gamesref.borrowGameRef(GameUId:gameUId)
    gameRef.setImgAndTypes(imageHash:imageHash,templateType:templateType, gameType:gameType,slogan:slogan)
    log("Theme setted!")
  }
}

