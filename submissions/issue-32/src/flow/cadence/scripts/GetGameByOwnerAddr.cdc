import Racenumber from 0xf8d6e0586b0a20c7

pub fun main(addr:Address):{UInt64:Racenumber.GameDetail} {
  let acct = getAccount(addr)
  let eventsRef = acct.getCapability<&Racenumber.Games{Racenumber.GamesPublic}>(Racenumber.GamesPublicPath).borrow() ?? panic("Events resource not found")
  let allEvents = eventsRef.getAllGames()
  log(allEvents.length)
  return allEvents
}
