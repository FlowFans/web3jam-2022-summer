import OverluError from "./OverluError.cdc"

pub contract OverluConfig {

    /**    ___  ____ ___ _  _ ____
       *   |__] |__|  |  |__| [__
        *  |    |  |  |  |  | ___]
         *************************/

    
    pub let UserCertificateStoragePath: StoragePath
    pub let UserCertificatePrivatePath: PrivatePath

    /**    ____ _  _ ____ _  _ ___ ____
       *   |___ |  | |___ |\ |  |  [__
        *  |___  \/  |___ | \|  |  ___]
         ******************************/

    pub event ContractInitialized()
    pub event WhitelistAdded(address: Address, operator: Address)
    pub event WhitelistRemoved(address: Address, operator: Address)
    pub event PauseStateChanged(pauseFlag: Bool, operator: Address)



    /**    ____ ___ ____ ___ ____
       *   [__   |  |__|  |  |___
        *  ___]  |  |  |  |  |___
         ************************/


    /// Reserved parameter fields: {ParamName: Value}
    access(self) let _reservedFields: {String: AnyStruct}

    // white list for creator when isPermissionless is false
    access(self) let whitelist: [Address]

    // global pause: true will stop pool creation
    pub var pause: Bool

     // record dna in upgrade model
    access(account) var upgradeRecords: {UInt64: [{String: AnyStruct}]}

    // record dna on model
    access(account) var dnaNestRecords: {UInt64: UInt64}

    // record dna in expand model
    access(account) var expandRecords: {UInt64: [{String: AnyStruct}]}

    /**    ____ _  _ _  _ ____ ___ _ ____ _  _ ____ _    _ ___ _   _
       *   |___ |  | |\ | |     |  | |  | |\ | |__| |    |  |   \_/
        *  |    |__| | \| |___  |  | |__| | \| |  | |___ |  |    |
         ***********************************************************/

    
    pub resource interface IdentityCertificate {}

    pub resource UserCertificate: IdentityCertificate{ }



    // resources
     // overlu admin resource for manage staking contract
    pub resource Admin {

        pub fun addWhitelist(address: Address) {
            pre{
                !OverluConfig.whitelist.contains(address) : OverluError.errorEncode(msg: "Whitelist address already exist", err: OverluError.ErrorCode.WHITE_LIST_EXIST)
            }
            OverluConfig.whitelist.append(address)
            
            emit WhitelistAdded(address: address, operator: self.owner!.address)
            
        }

        pub fun removeWhitelist(_ idx: UInt8) {
            pre{
                OverluConfig.whitelist[idx] != nil : OverluError.errorEncode(msg: "Address not exist", err: OverluError.ErrorCode.INVALID_PARAMETERS)
            }
            let address = OverluConfig.whitelist[idx]
            OverluConfig.whitelist.remove(at: idx)

            emit WhitelistRemoved(address: address, operator: self.owner!.address)
        }


        pub fun setPause(_ flag: Bool) {
            pre {
                OverluConfig.pause != flag : OverluError.errorEncode(msg: "Set pause state faild, the state is same", err: OverluError.ErrorCode.SAME_BOOL_STATE)
            }
            OverluConfig.pause = flag

            emit PauseStateChanged(pauseFlag: flag, operator: self.owner!.address)
        }
        
    }

    pub fun getRandomId(_ range: Int): UInt64 {
        return unsafeRandom() % UInt64(range)
    }


    access(account) fun setUpgradeRecords(_ id: UInt64, metadata: {String: AnyStruct}) {
        let records = OverluConfig.upgradeRecords[id] ?? []
        records.append(metadata)
        OverluConfig.upgradeRecords[id] = records
    }


    access(account) fun setExpandRecords(_ id: UInt64, metadata: {String: AnyStruct}) {
        let records = OverluConfig.expandRecords[id] ?? []
        records.append(metadata)
        OverluConfig.expandRecords[id] = records
    }


    access(account) fun setDNANestRecords(_ id: UInt64, dnaId: UInt64) {
        OverluConfig.dnaNestRecords[dnaId] = id
    }



   
    // ---- contract methods ----


    pub fun setupUser(): @UserCertificate {
        let certificate <- create UserCertificate()
        return <- certificate
    }


    pub fun getUpgradeRecords(_ id: UInt64): [{String: AnyStruct}]? {
        return OverluConfig.upgradeRecords[id]
    }

    pub fun getExpandRecords(_ id: UInt64): [{String: AnyStruct}]? {
        return OverluConfig.expandRecords[id]
    }

    pub fun getDNANestRecords(_ id: UInt64): UInt64? {
        return OverluConfig.dnaNestRecords[id]
    }

    pub fun getAllUpgradeRecords(): {UInt64: [{String: AnyStruct}]} {
        return OverluConfig.upgradeRecords
    }

     pub fun getAllDNANestRecords(): {UInt64: UInt64} {
        return OverluConfig.dnaNestRecords
    }

     pub fun getAllExpandRecords(): {UInt64: [{String: AnyStruct}]} {
        return OverluConfig.expandRecords
    }


    // ---- init func ----
    init() {
        self.UserCertificateStoragePath = /storage/overluUserCertificate
        self.UserCertificatePrivatePath = /private/overluUserCertificate
        self._reservedFields = {}
        self.whitelist = []
        self.pause = false
        self.upgradeRecords = {}
        self.expandRecords = {}
        self.dnaNestRecords = {}
    }

}