export class GameDetailClass {
    constructor(gameName, timestamp, issues, mintedNum, uid, gameId, hostAddr, price, imageHash, templateType, gameType, slogan) {
        this.gameName = gameName
        this.timestamp = timestamp
        this.issues = issues
        this.mintedNum = mintedNum
        this.uid = uid
        this.gameId = gameId
        this.hostAddr = hostAddr
        this.price = price
        this.imageHash = imageHash
        this.templateType = templateType
        this.gameType = gameType
        this.slogan = slogan
    }
}

//gameDetail:GameDetailClass
export class ThemeMetaClass {
    constructor(gameDetail, num, background) {
        this.gameDetail = gameDetail
        this.num = num
        this.background = background
    }
}