import NebulaActivity from 0x01

pub fun main(address: Address, activityName: String): {String: UFix64} {
  let autoShop = getAccount(address).getCapability(/public/AutoActivityShop)
        .borrow<&NebulaActivity.ActivitiesManager{NebulaActivity.AutoActivitesShop}>()
          ?? panic ("There is no such activity")
  let prices = autoShop.equeryTicketsPrice(activityName: activityName)

  return prices
}
