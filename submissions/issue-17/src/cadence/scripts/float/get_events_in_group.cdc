import FLOAT from "../../contracts/float/FLOAT.cdc"

pub fun main(account: Address, groupName: String): [FLOATEventMetadata] {
  let floatEventCollection = getAccount(account).getCapability(FLOAT.FLOATEventsPublicPath)
                              .borrow<&FLOAT.FLOATEvents{FLOAT.FLOATEventsPublic}>()
                              ?? panic("Could not borrow the FLOAT Events Collection from the account.")
  let group = floatEventCollection.getGroup(groupName: groupName) ?? panic("This group doesn't exist.")
  let eventIds = group.getEvents()

  let answer: [FLOATEventMetadata] = []
  for eventId in eventIds {
    let event = floatEventCollection.borrowPublicEventRef(eventId: eventId) ?? panic("This event does not exist in the account")
    let metadata = FLOATEventMetadata(
      _claimable: event.claimable, 
      _dateCreated: event.dateCreated, 
      _description: event.description, 
      _eventId: event.eventId, 
      _extraMetadata: event.getExtraMetadata(), 
      _groups: event.getGroups(), 
      _host: event.host, 
      _image: event.image, 
      _name: event.name, 
      _totalSupply: event.totalSupply, 
      _transferrable: event.transferrable, 
      _url: event.url, 
      _claimed: event.getClaimed()
    )
    answer.append(metadata)
  }

  return answer
}

pub struct FLOATEventMetadata {
  pub let claimable: Bool
  pub let dateCreated: UFix64
  pub let description: String 
  pub let eventId: UInt64
  pub let extraMetadata: {String: AnyStruct}
  pub let groups: [String]
  pub let host: Address
  pub let image: String 
  pub let name: String
  pub let totalSupply: UInt64
  pub let transferrable: Bool
  pub let url: String
  pub let claimed: {Address: FLOAT.TokenIdentifier}

  init(
      _claimable: Bool,
      _dateCreated: UFix64,
      _description: String, 
      _eventId: UInt64,
      _extraMetadata: {String: AnyStruct},
      _groups: [String],
      _host: Address, 
      _image: String, 
      _name: String,
      _totalSupply: UInt64,
      _transferrable: Bool,
      _url: String,
      _claimed: {Address: FLOAT.TokenIdentifier}
  ) {
      self.claimable = _claimable
      self.dateCreated = _dateCreated
      self.description = _description
      self.eventId = _eventId
      self.extraMetadata = _extraMetadata
      self.groups = _groups
      self.host = _host
      self.image = _image
      self.name = _name
      self.transferrable = _transferrable
      self.totalSupply = _totalSupply
      self.url = _url
      self.claimed = _claimed
  }
}