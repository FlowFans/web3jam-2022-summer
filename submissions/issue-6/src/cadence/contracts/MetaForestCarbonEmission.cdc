import MetaForestCarbonEnergy from 0x25d45165c1b69b6e
import FungibleToken from 0x9a0766d93b6608b7
pub contract MetaForestCarbonEmission {

    // A dictionary that stores all token emissions against it's user.
    access(self) var totalEmission : {Address: UFix64}

    // A dictionary that stores last token emissions against it's user.
    access(self) var lastEmission : {Address: UFix64}

    // A dictionary that stores last token update emissions against it's user.
    access(self) var lastUpdate : {Address:UFix64}

    // variable for number of blocks produced in one day
    pub var blockNumOfOneDay: UFix64

    // Contract Event definitions
    pub event CarbonEmissionIncreased(user: Address, amount: UFix64)

    // function for increaseing the MetaForestCarbonEnergy tokens of a user
    access(account) fun increaseMetaForestCarbonEmissions(user: Address, amount: UFix64){
        
        let convertedBlockNumber = MetaForestCarbonEmission.blockNumOfOneDay
        assert(convertedBlockNumber - MetaForestCarbonEmission.lastUpdate[user]! > convertedBlockNumber, message: "can't increase in limit time")
        MetaForestCarbonEmission.lastUpdate[user] = MetaForestCarbonEmission.blockNumOfOneDay
        MetaForestCarbonEmission.lastEmission[user] = amount
        MetaForestCarbonEmission.totalEmission[user] = MetaForestCarbonEmission.totalEmission[user]! + amount

        let tokenAdmin = self.account.borrow<&MetaForestCarbonEnergy.Administrator>(from: /storage/MetaForestCarbonEnergyAdmin)
            ?? panic("Signer is not the token admin")

        let tokenReceiver = getAccount(user)
            .getCapability(/public/MetaForestCarbonEnergyReceiver)
            .borrow<&MetaForestCarbonEnergy.Vault{FungibleToken.Receiver}>()
            ?? panic("Unable to borrow receiver reference")
        let minter <- tokenAdmin.createNewMinter(allowedAmount: amount)
        let mintedVault <- minter.mintTokens(amount: amount)
        let castedVault  <-mintedVault as! @FungibleToken.Vault
        tokenReceiver.deposit(from: <-castedVault)
        destroy minter

        emit CarbonEmissionIncreased(user: user, amount: amount)
    }

    // get the MetaForestCarbonEnergy balance for last emission against it's user
    pub fun getlastBalanceOf(user: Address): UFix64 {
            let MetaForestCarbonEnergyBalance = getAccount(user).getCapability(/public/MetaForestCarbonEnergyBalance)
                                .borrow<&MetaForestCarbonEnergy.Vault{FungibleToken.Balance}>() 
                                ??panic("could not borrow reference")
            MetaForestCarbonEmission.lastEmission[user] = MetaForestCarbonEnergyBalance.balance
            return MetaForestCarbonEnergyBalance.balance
            
        }
        // get the MetaForestCarbonEnergy balance for last update against it's user
        pub fun getlastUpdateOf(user: Address):UFix64{
            let MetaForestCarbonEnergyBalance = getAccount(user).getCapability(/public/MetaForestCarbonEnergyBalance)
                                .borrow<&MetaForestCarbonEnergy.Vault{FungibleToken.Balance}>() 
                                ??panic("could not borrow reference")
            MetaForestCarbonEmission.lastUpdate[user] = MetaForestCarbonEnergyBalance.balance
            return MetaForestCarbonEnergyBalance.balance
            
        }
        // get the MetaForestCarbonEnergy balance for total emission against it's user
        pub fun gettotalBalanceOf(user: Address): UFix64  {
            let MetaForestCarbonEnergyBalance = getAccount(user).getCapability(/public/MetaForestCarbonEnergyBalance)
                            .borrow<&MetaForestCarbonEnergy.Vault{FungibleToken.Balance}>() 
                            ??panic("could not borrow reference")
            MetaForestCarbonEmission.totalEmission[user] = MetaForestCarbonEnergyBalance.balance
            return MetaForestCarbonEnergyBalance.balance
        }

    init(){
        self.totalEmission = {}
        self.lastEmission = {}
        self.lastUpdate = {}
        self.blockNumOfOneDay = getCurrentBlock().timestamp
    }
}