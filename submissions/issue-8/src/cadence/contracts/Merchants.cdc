import MetadataViews from "./MetadataViews.cdc"

pub contract Merchants {
    pub event Created()

    pub resource interface IDs {
        pub fun getIds(): [UInt64]
        pub fun containdId(id: UInt64): Bool
        pub fun appendId(id: UInt64)
        //just for test
        pub fun removeId(id: UInt64)
        pub fun clear()
    }

    pub resource MerchantsInfo: IDs {
        
        pub var ownedIDs: {UInt64: Bool}

        pub var logo: MetadataViews.IPFSFile

        pub var name: String

        pub fun getIds(): [UInt64] {
            return self.ownedIDs.keys
        }

        pub fun containdId(id: UInt64): Bool {
            return self.ownedIDs.containsKey(id)
        }

        pub fun appendId(id: UInt64) {
            self.ownedIDs.insert(key: id, true)
        }
        //just for test
        pub fun removeId(id: UInt64) {
            self.ownedIDs.remove(key: id)
        }

        pub fun clear() {

        }

        init(name: String, logoCid: String, logoPath: String?) {
            self.ownedIDs = {}
            self.logo =  MetadataViews.IPFSFile(cid: logoCid, path: logoPath)
            self.name = name
        }

    }
}