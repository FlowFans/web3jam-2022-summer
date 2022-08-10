import NonFungibleToken from 0x03
import FungibleToken from 0x04
import ExampleToken from 0x05

/*
  This is a Standard Contract for a NebulaActivity
*/
access(all) contract NebulaActivity{

    // Set the total number of tickets
    pub var totalSupply: UInt64

    // TicketList lists the following information of the activity:
    // whole range types of tickets on selling;
    // its maximum number to sell; how

    // TicketInfo show the follwing information of each type of Ticket in this particular Activity:
    // type is the name of this kind of ticket
    // maximumSupply is the maximum number a particular type of ticket can sell
    // sold means how many
    access(all) struct TicketInfo {
        pub let type: String
        pub let maximumSupply: UInt64
        pub var sold: UInt64
        pub let price: UFix64

        init(_type: String, _maximum: UInt64, _price: UFix64) {
            self.type = _type
            self.sold = 0
            self.maximumSupply = _maximum
            self.price = _price
        }

        access(contract) fun sell(_num: UInt64) {
            self.sold = _num + self.sold
        }
    }

    // Define a TimeVerifiable Date
    access(all) struct Date {
        access(all) let year: Int
        access(all) let month: Int
        access(all) let day: Int
        access(all) let hour: Int
        access(all) let minute: Int

        pub fun verifyBefore(_givenDate: Date): Bool {
            if _givenDate.year <= self.year  {
                if _givenDate.month <= self.month {
                    if _givenDate.day <= self.day {
                        if _givenDate.hour <= self.hour {
                            if _givenDate.minute < self.minute {
                                return true
                            }
                        }
                    }
                }
            }
            return false
        }

        // Initialize the Date
        init(_year: Int, _month: Int, _day: Int, _hour: Int, _minute: Int) {
            pre {
                _month >= 1 && _month <= 12: "Month should be from January to December"
                _hour >= 0 && _hour <= 23: "Hour should be from 0 to 24"
                _minute >= 0 && _minute <= 60: "Minute should be from 0 to 60"
            }

            self.year = _year
            self.month = _month
            self.day = _day
            self.hour = _hour
            self.minute = _minute
        }
    }

    // Basic Info about one particular activity
    access(all) struct ActivityInfo {
        // Time Info
        pub let startTime: Date
        pub let endTime: Date

        // Name of this Activity
        pub let activityName: String
        // Description of this Activity
        pub let description: String
        // Name of the Host
        pub let hostName: String

        // Tags about this activity, such as: "web3"/"Defi"
        pub let tags: [String]

        init(
        _startTime: Date,
        _endTime: Date,
        _activityName: String,
        _descriptiion: String,
        _hostName: String,
        _tags: [String]
        ) {
        self.startTime = _startTime
        self.endTime = _endTime
        self.activityName = _activityName
        self.description = _descriptiion
        self.hostName = _hostName
        self.tags = _tags
        }
    }

    // Info about the activity host
    access(all) struct HostInfo {
        pub let salePaymentVaultType: Type // The type of Vault this activity accept

        pub let commissionAmount: UFix64 //

        init(
            salePaymentVaultType: Type,
            commissionAmount: UFix64,
        ) {
            self.salePaymentVaultType = salePaymentVaultType
            self.commissionAmount = commissionAmount
        }
    }

    // The public exposure to the public
    pub resource interface NebulaTicketPublic {
        pub fun getIDs(): UInt64
        pub fun checkVerifyState(): Bool
        pub fun getActivityInfo(): ActivityInfo
        pub fun resolveView(_ view: Type): String
        pub fun verifyTicket(_signer: UInt64, _currentDate: Date)
    }

    // The resource which represent the NFT ticket a user can possess
    pub resource NebulaTicket: NonFungibleToken.INFT, NebulaTicketPublic {
        pub let id: UInt64 // The unique ID that each NFT have

        pub let type: {String: UInt64} // Ticket type and the corresponding supply number

        pub let activityInfo: ActivityInfo

        // URL for this ticket
        access(self) let metaDataVerifiedURL: String
        access(self) let metaDataNotVerifiedURL: String

        // To check whether a ticket is verified
        access(contract) var verified: Bool
        // verifyCode is used to identify whether a verifier is qualified in case that someone else check this ticket previsely
        access(self) let verifyCode: UInt64

        pub fun verifyTicket(_signer: UInt64, _currentDate: Date) {
            pre {
               // One ticket can only be verified if the verify state is false
               self.verified == false: "This ticket has been expired before"
                // One ticket can onnly be verified if the signer is the one who publish it
                self.verifyCode ==  _signer: "You are not qulified to verify this"
                // Make sure it will be checked in the proper time
                self.activityInfo.startTime.verifyBefore(_givenDate: _currentDate) == true: "This Ticket havn't reach the check time"
                _currentDate.verifyBefore(_givenDate: self.activityInfo.endTime) == true: "This Ticket has been overdue"
            }
            self.verified = true
        }

        // Check the verify state of this ticket
        pub fun checkVerifyState(): Bool {
            let state = self.verified
            return state
        }

        init(
        _type: String,
        _supply: UInt64,
        _activityInfo: ActivityInfo,
        _metaDataVerified: String,
        _metaDataNotVerified: String,
        _verifyCode: UInt64
        ) {
            self.verified = false
            self.id = self.uuid

            self.type = {}
            self.type.insert(key: _type, _supply)

            self.activityInfo = _activityInfo
            self.metaDataNotVerifiedURL = _metaDataNotVerified
            self.metaDataVerifiedURL = _metaDataVerified

            self.verifyCode = _verifyCode
        }

        // Get the Unique ticket of this ticket
        pub fun getIDs(): UInt64 {
            return self.id
        }

        // Get the basic info of the activity where the ticket used in
        pub fun getActivityInfo(): ActivityInfo {
            let acInfo = self.activityInfo
            return acInfo
        }

        // Return the metaData of one particular ticket, depends on whether it has been verified
        pub fun resolveView(_ view: Type): String {
            if self.verified {
                return self.metaDataVerifiedURL
            }
            return  self.metaDataNotVerifiedURL
        }

        destroy() {
        }
    }

    // The own tickets collection in one account
    pub resource interface TicketsCollectionPublic {
        pub fun deposit(ticket: @NebulaTicket)
        pub fun borrowNebulaTicket(id: UInt64): &NebulaTicket? {
            post {
                (result == nil) || (result?.id == id): "Cannot borrow"
            }
        }
    }

    // TicketsCollection, similar to Collection, but store Tickets only
    pub resource TicketsCollection: TicketsCollectionPublic {
        pub var ownedTickets: @{UInt64: NebulaTicket}

        pub fun deposit(ticket: @NebulaTicket) {
            self.ownedTickets[ticket.getIDs()] <-! ticket
        }

        pub fun borrowNebulaTicket(id: UInt64): & NebulaTicket? {
            post {
                (result == nil) || (result?.id == id): "Cannot borrow"
            }
            return (&self.ownedTickets[id] as &NebulaTicket?)!
        }

        init() {
            self.ownedTickets <- {}
        }

        destroy() {
            destroy self.ownedTickets
        }
    }

    // Create a TicketsCollection for an Accoun
    pub fun createEmptyTicketsCollection(): @TicketsCollection {
        return <- create TicketsCollection()
    }



    pub resource TicketMachine {
        access(self) let id: UInt64

        pub let activityInfo: ActivityInfo

        // TicketList lists the following information of the activity:
        // whole range types of tickets on selling;
        // its maximum number to sell;
        // keys here is each type's name
        pub let ticketList: {String: TicketInfo}

        access(all) let metaDataNotVerified: String
        access(all) let metaDataVerified: String

        access(all) let details: HostInfo

        access(self) var checked: {UInt64: Bool}

        pub fun purchase(
        type: String,
        number: UInt64,
        payment: @FungibleToken.Vault,
        commissionRecipient: Capability<&{FungibleToken.Receiver}>?
        ): @NebulaTicket {
            pre {
                // Check if there's any remaining tickets
                self.ticketList[type]!.sold + number <= self.ticketList[type]!.maximumSupply: "There's no remaing tickets!"
                // Check if there's enough balance in this account
                payment.balance == self.equeryPrice(type: type): "You don't have enough banlance to pay this"
            }
            // The following three stpes will transfer the demanding amount of Vault to sellors' account
            let commissionReceiver = commissionRecipient ?? panic("Commission recipient can't be nil")
            let commissionPayment <- payment.withdraw(amount: self.equeryPrice(type: type))
            let recipient = commissionReceiver.borrow() ?? panic("Unable to borrow the recipient capability")
            recipient.deposit(from: <- commissionPayment)

            var residualReceiver: &{FungibleToken.Receiver}? = nil
            residualReceiver!.deposit(from: <-payment)


            self.ticketList[type]!.sell(_num: number)

            // Mint a new Ticket
            let ticket <- create NebulaTicket(
            _type: type,
            _supply: self.ticketList[type]!.maximumSupply,
            _activityInfo: self.activityInfo,
            _metaDataVerified: self.metaDataVerified,
            _metaDataNotVerified: self.metaDataNotVerified,
            _verifyCode: self.id
            )
            // Get the ticket's unique id
            let id  = ticket.getIDs()
            // Make a bond with this Unique id and let the verified state become false
            self.checked[id] = false

            return <- ticket
        }

        pub fun mintNebulaTicket(type: String): @NebulaTicket {
            let ticket <- create NebulaTicket(
            _type: type,
            _supply: self.ticketList[type]!.maximumSupply,
            _activityInfo: self.activityInfo,
            _metaDataVerified: self.metaDataVerified,
            _metaDataNotVerified: self.metaDataNotVerified,
            _verifyCode: self.id
            )

            // Make a bond with this Unique id and let the verified state become false
            let id = ticket.getIDs()
            self.checked[id] = false
            return <- ticket
        }

        // Get the corresponding price of this type ticket
        pub fun equeryPrice(type: String): UFix64 {
            let price = self.ticketList[type]!.price
            return price
        }

        pub fun equeryPaymentType(): Type {
            return self.details.salePaymentVaultType
        }

        pub fun equeryActivityInfo(): ActivityInfo {
            return self.activityInfo
        }

        // The neccessary process from the view of Host
        // Return 'true' means it verify successfully
        // Otherwiese, it doesn't fit the requset
        access(all) fun verifyHost(id: UInt64, ticket: &NebulaTicket, date: Date): Bool {
            // if this id's corresponding ticket fit the critirion:
            //  (1)Not Verify yet (2) Inside the published tickets' list
            // Then we can verify it
            if self.checked[id]! {
                self.checked[id] = true

                //ticket.verifyTicket(_signer: self.id, _currentDate: date)
                return true
            }

            return false
        }

        pub fun adjustTicketInfo(_supply: {String: UInt64}, _price: {String: UFix64}) {
            let types = _supply.keys
            for type in types {
                let info = TicketInfo(_type: type, _maximum: _supply[type]!, _price: _price[type]!)
                self.ticketList.insert(key: type, info)
            }
        }

        init(
        _activityInfo: ActivityInfo,
        _metaDataVerified: String,
        _metaDataNotVerified: String,
        ) {
            self.id = self.uuid
            self.activityInfo = _activityInfo
            self.ticketList = {}
            self.details = HostInfo(salePaymentVaultType: Type<&ExampleToken.Vault>(), commissionAmount: 1.0)
            // var i = 0
            // Initialize the Ticketlist
            //while i < _ticketList.length {
            //self.ticketList.insert(key: _ticketList[i].type, _ticketList[i])
            //}

            self.metaDataNotVerified = _metaDataNotVerified
            self.metaDataVerified = _metaDataVerified
            //self.details = HostInfo(salePaymentVaultType:_salePaymentVaultType, commissionAmount: _commisionAmount)

            self.checked = {}
        }
    }

    pub fun createTicketMachine(activityInfo: ActivityInfo, metaDataVerified: String, metaDataNotVerified: String,
    ): @TicketMachine {
        return <- create TicketMachine(
        _activityInfo: activityInfo,
        _metaDataVerified: metaDataVerified,
        _metaDataNotVerified: metaDataNotVerified,
        )
    }

    pub resource interface AutoActivitesShop {
        // purchase a ticket
        pub fun purchase(
        activityName: String,
        type: String,
        number: UInt64,
        payment: @FungibleToken.Vault,
        commissionRecipient: Capability<&{FungibleToken.Receiver}>?
        ): @NebulaTicket

        // The following functions aim at acquiring some basic info of one particulat activity and its corresponding tickets
        // Get the supply info of the tickets
        pub fun equeryTicketsSupply(activityName: String): {String: UInt64}
        // Get the price info of the tickets
        pub fun equeryTicketsPrice(activityName: String): {String: UFix64}
    }

    // Activity Manager is the resource to manage all the activities the host have held,
    // Also the customers can access to the "Activities Public" to purchase one particular activity
    pub resource ActivitiesManager: AutoActivitesShop {
        pub var operatedActivities: @{String: TicketMachine}

        // The address of the host, used in payment
        access(self) let address: Address

        // deposit a new activity to host's manager
        pub fun depositActivity(ticketMachine: @TicketMachine) {
            let name = ticketMachine.activityInfo.activityName
            self.operatedActivities[name] <-! ticketMachine
        }

        // Add corresponding tickets type to the ticket machine
        pub fun addTickets(_supply: {String: UInt64}, _activityName: String, _price: {String: UFix64}) {
            // Borrow one particular ticketMachine
            let ticketMachine = (&self.operatedActivities[_activityName] as &TicketMachine?)!
            ticketMachine.adjustTicketInfo(_supply: _supply, _price: _price)
        }

        pub fun equeryTicketsSupply(activityName: String): {String: UInt64} {
            let res: {String: UInt64} = {}
            // Borrow one particular ticketMachine
            let ticketMachine = (&self.operatedActivities[activityName] as &TicketMachine?)!
            let types = ticketMachine.ticketList.keys
            let list = ticketMachine.ticketList
            // Create the demanding list
            for type in types {
                res.insert(key: type, list[type]!.maximumSupply)
            }
            return res
        }

        pub fun equeryTicketsPrice(activityName: String): {String: UFix64} {
             let res: {String: UFix64} = {}
            // Borrow one particular ticketMachine
            let ticketMachine = (&self.operatedActivities[activityName] as &TicketMachine?)!
            let types = ticketMachine.ticketList.keys
            let list = ticketMachine.ticketList
            // Create the demanding list
            for type in types {
                res.insert(key: type, list[type]!.price)
            }
            return res
        }

        pub fun borrowTicketMachine(activityName: String): &TicketMachine? {
            return (&self.operatedActivities[activityName] as &TicketMachine?)
        }

        // purchase a ticket through the activity manager
        pub fun purchase(
        activityName: String,
        type: String,
        number: UInt64,
        payment: @FungibleToken.Vault,
        commissionRecipient: Capability<&{FungibleToken.Receiver}>?
        ): @NebulaTicket {
            // Get the reference of the ticketMachine in it
            let ticketMachine = (&self.operatedActivities[activityName] as &TicketMachine?)!
            // The following three stpes will transfer the demanding amount of Vault to sellors' account
            let commissionReceiver = commissionRecipient ?? panic("Commission recipient can't be nil")
            let commissionPayment <- payment.withdraw(amount: ticketMachine.equeryPrice(type: type))
            let recipient = commissionReceiver.borrow() ?? panic("Unable to borrow the recipient capability")
            recipient.deposit(from: <- commissionPayment)

            var residualReceiver: &{FungibleToken.Receiver}? = nil
            residualReceiver!.deposit(from: <-payment)


            ticketMachine.ticketList[type]!.sell(_num: number)

            // Mint a new Ticket
            let ticket <- ticketMachine.mintNebulaTicket(type: type)
            // Get the ticket's unique id
            let id  = ticket.getIDs()
            // Make a bond with this Unique id and let the verified state become false


            return <- ticket
        }

        init(_address: Address) {
            self.operatedActivities <- {}
            self.address = _address
        }

        destroy() {
            destroy self.operatedActivities
        }
    }

    pub fun createEmptyActivitiesManager(_address: Address): @ActivitiesManager {
        return <- create ActivitiesManager(_address: _address)
    }

    init() {
      self.totalSupply = 0

    }

}
