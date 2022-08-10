import MetadataViews from "./MetadataViews.cdc"
import NonFungibleToken from "./NonFungibleToken.cdc"
import StarRealm from "./StarRealm.cdc"

pub contract PunstersNFT: NonFungibleToken {
    // -----------------------------------------------------------------------
    // NonFungibleToken Standard Events
    // -----------------------------------------------------------------------
    pub event ContractInitialized()
    pub event Withdraw(id: UInt64, from: Address?)
    pub event Deposit(id: UInt64, to: Address?)
    
    pub var totalSupply: UInt64
    
    // -----------------------------------------------------------------------
    // Punsters
    // -----------------------------------------------------------------------
    pub event Destroy(info: String)

    pub let PunsterStoragePath: StoragePath;
    pub let IPunsterPublicPath: PublicPath;
    // pub let IFunnyIndexPublicPath: PublicPath;
    // pub let DuanjiStoragePath: StoragePath;
    // pub let IDuanjiPublicPath: PublicPath;

    access(contract) var PunsterTotal: UInt64;
    access(contract) var DuanjiTotal: UInt64;
    
    pub let cidKey: String;
    pub let pathKey: String;
    pub let descriptionKey: String;

    // half life attenuation of funny index
    pub let halfLife: UFix64;
    // minimum timespan to publish a new ad.
    pub let baseTimespan: UFix64;
    pub let baseCommends: UFix64;

    access(contract) let registeredPunsters: {UInt64: Address};

    init() {
        self.PunsterStoragePath = StoragePath(identifier: "PunsterStoragePath".concat(self.account.address.toString()))!;
        self.IPunsterPublicPath = PublicPath(identifier: "IPunsterPublicPath".concat(self.account.address.toString()))!;
        // self.IFunnyIndexPublicPath = /public/IFunnyIndexPublicPath;
        // self.DuanjiStoragePath = /storage/DuanjiStoragePath;
        // self.IDuanjiPublicPath = /public/IDuanjiPublicPath;

        self.PunsterTotal = 1000000;
        self.DuanjiTotal = 1;

        self.totalSupply = 0;
        self.registeredPunsters = {};
        
        self.cidKey = "thumbnailCID";
        self.pathKey = "thumbnailPath";
        self.descriptionKey = "description";

        self.halfLife = 3600.0 * 24.0 * 7.0;
        self.baseTimespan = 3600.0 * 24.0 * 3.0;
        self.baseCommends = 108.0;
    }

    // -----------------------------------------------------------------------
    // NonFungibleToken Standard Functions
    // -----------------------------------------------------------------------
    // This interface is useless 
    pub fun createEmptyCollection(): @NonFungibleToken.Collection {
        let punsterRes <- create Collection(id: self.PunsterTotal, acct: 0x00, description: "", ipfsURL: "");
        self.PunsterTotal = self.PunsterTotal + 1; 
        return <-punsterRes
    }

    // This `I` is not mean 'Interface' but 'Interaction'
    pub resource interface IPunsterPublic {
        // tell-fetch model. 
        // Notify followers to 
        pub fun notify(addr: Address);
        // return last update timestamp, that is `fun getCurrentBlock(): Block`
        pub fun getLatestUpdate(): UFix64;

        // Get `Duanji` information
        // Return informations of `Duanji` the time after `timestamp`
        pub fun getDuanjiViewFrom(timestamp: UFix64): [DuanjiView];
        // Return DuanjiView
        pub fun getAllDuanjiView(): [DuanjiView];
        pub fun getLatestDuanjiView(): DuanjiView?;

        // tell-fetch model.
        // Follow/Unfollow some funnyguy
        pub fun followedBy(addr: Address);
        pub fun cancelFollowedBy(addr: Address)
        pub fun isFollowing(addr: Address): Bool;

        // Public query information of follow machanism
        pub fun getFollowings(): [Address];
        pub fun getFollowers(): [Address];

        pub fun getAllFollowingDuanji(): [DuanjiView];
        pub fun getFollowingUpdates(): [DuanjiView];


        // tell-fetch model
        // Receive `Duanji` commending from others
        pub fun ReceiveCommend(addr: Address, duanjiID: UInt64);
        pub fun ReceiveCancelCommend(addr: Address, duanjiID: UInt64);
        pub fun isCommended(duanjiID: UInt64): Bool;

        // FunnyIndex
        pub fun getFunnyIndex(): UInt32;

        // Punster View
        pub fun getPunsterView(): PunstersNFT.PunsterView;

        // Get AD remaining time
        pub fun getADRemainingTime(): Fix64;

        // tell-fetch model
        pub fun destroyNotify(addr: Address);
        // is destroying
        pub fun isDestroying(): Bool;

        // -----------------------------------------------------------------------
        // NFT operations
        // -----------------------------------------------------------------------
        pub fun deposit(token: @NonFungibleToken.NFT)
        pub fun getIDs(): [UInt64]
        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT
        pub fun borrowDuanji(id: UInt64): &PunstersNFT.NFT? {
            post {
                (result == nil) || (result?.id == id): 
                    "Cannot borrow TestNFTWithViews reference: The ID of the returned reference is incorrect"
            }
        }
    }

    // `Duanji` NFT
    pub resource NFT: NonFungibleToken.INFT, MetadataViews.Resolver {
        pub let id: UInt64;
        pub let timestamp: UFix64;

        pub let metadata: { String: AnyStruct};

        priv let commends: [Address];
        // priv let funnyIndex: UInt32;

        init(
            id: UInt64,
            description: String,
            ipfsURL: String
        ) {
            self.id = id;
            self.timestamp = getCurrentBlock().timestamp;
            self.metadata = {};
            self.metadata[PunstersNFT.cidKey] = ipfsURL;
            self.metadata[PunstersNFT.descriptionKey] = description;
            self.commends = [];
            // self.funnyIndex = 0;
        }
        
        access(contract) fun commend(addr: Address): Bool{
            if (!self.commends.contains(addr)) {
                self.commends.append(addr);
                // TODO: increase funny index
                
                return true;
            } else {
                return false;
            }
        }

        access(contract) fun cancelCommend(addr: Address): Bool {            
            let idxOr = self.commends.firstIndex(of: addr);
            if let idx = idxOr {
                self.commends.remove(at: idx);
                // TODO: decrease funny index

                return true;
            } else {
                return false;
            }
        }

        access(contract) fun getFunnyIndex(): UInt32 {
            let leftShiftBit: UInt64 = UInt64((getCurrentBlock().timestamp - self.timestamp) / PunstersNFT.halfLife); 
            return UInt32(UInt64(self.commends.length) >> leftShiftBit);
        }

        access(contract) fun getCommendAddress(): [Address] {
            return self.commends;
        }

        pub fun getViews(): [Type] {
            return [
                Type<MetadataViews.Display>(),
                Type<MetadataViews.Royalties>(),
                Type<MetadataViews.Editions>(),
                Type<MetadataViews.ExternalURL>(),
                Type<MetadataViews.NFTCollectionData>(),
                Type<MetadataViews.NFTCollectionDisplay>(),
                Type<MetadataViews.Serial>(),
                Type<MetadataViews.Traits>()
            ]
        }

        pub fun resolveView(_ view: Type): AnyStruct? {
            switch view {
                case Type<MetadataViews.Display>():
                    return MetadataViews.Display(
                        name: "DuanjiNFT".concat(self.id.toString()),
                        description: self.metadata[PunstersNFT.descriptionKey]! as! String,
                        thumbnail: MetadataViews.IPFSFile(
                            url: self.metadata[PunstersNFT.cidKey]! as! String, path: nil
                        )
                    )
                case Type<MetadataViews.Editions>():
                    // There is no max number of NFTs that can be minted from this contract
                    // so the max edition field value is set to nil
                    let editionInfo = MetadataViews.Edition(name: "PunStar Hackathon Edition", number: self.id, max: nil)
                    let editionList: [MetadataViews.Edition] = [editionInfo]
                    return MetadataViews.Editions(
                        editionList
                    )
                case Type<MetadataViews.Serial>():
                    return MetadataViews.Serial(
                        self.id
                    )
                case Type<MetadataViews.Royalties>():
                    return MetadataViews.Royalties(
                        []
                    )
                case Type<MetadataViews.ExternalURL>():
                    return MetadataViews.ExternalURL(self.metadata[PunstersNFT.cidKey]! as! String)
                case Type<MetadataViews.NFTCollectionData>():
                    return MetadataViews.NFTCollectionData(
                        storagePath: PunstersNFT.PunsterStoragePath,
                        publicPath: PunstersNFT.IPunsterPublicPath,
                        providerPath: /private/PunsterNFTCollection,
                        publicCollection: Type<&PunstersNFT.Collection{IPunsterPublic}>(),
                        publicLinkedType: Type<&PunstersNFT.Collection{IPunsterPublic, NonFungibleToken.CollectionPublic,NonFungibleToken.Receiver,MetadataViews.ResolverCollection}>(),
                        providerLinkedType: Type<&PunstersNFT.Collection{IPunsterPublic, NonFungibleToken.CollectionPublic,NonFungibleToken.Provider,MetadataViews.ResolverCollection}>(),
                        createEmptyCollectionFunction: (fun (): @NonFungibleToken.Collection {
                            return <-PunstersNFT.createEmptyCollection()
                        })
                    )
                case Type<MetadataViews.NFTCollectionDisplay>():
                    let media = MetadataViews.Media(
                        file: MetadataViews.IPFSFile(
                            url: self.metadata[PunstersNFT.cidKey]! as! String, path: nil
                        ),
                        mediaType: "image/svg+xml"
                    )
                    return MetadataViews.NFTCollectionDisplay(
                        name: "The Punster Collection",
                        description: "This collection is a punster to publish your Duanji Flow NFT.",
                        externalURL: MetadataViews.ExternalURL(self.metadata[PunstersNFT.cidKey]! as! String),
                        squareImage: media,
                        bannerImage: media,
                        socials: {
                            "twitter": MetadataViews.ExternalURL("https://twitter.com/flow_blockchain")
                        }
                    )
                case Type<MetadataViews.Traits>():
                    // exclude mintedTime and foo to show other uses of Traits
                    let excludedTraits = ["mintedTime", "foo"]
                    let traitsView = MetadataViews.dictToTraits(dict: self.metadata, excludedNames: excludedTraits)

                    // mintedTime is a unix timestamp, we should mark it with a displayType so platforms know how to show it.
                    let mintedTimeTrait = MetadataViews.Trait(name: "mintedTime", value: self.timestamp, displayType: "Date", rarity: nil)
                    traitsView.addTrait(mintedTimeTrait)

                    // foo is a trait with its own rarity
                    let fooTraitRarity = MetadataViews.Rarity(score: 10.0, max: 100.0, description: "Common")
                    let fooTrait = MetadataViews.Trait(name: "foo", value: self.metadata["foo"], displayType: nil, rarity: fooTraitRarity)
                    traitsView.addTrait(fooTrait)
                    
                    return traitsView

            }
            return nil
        }

        pub fun getMetadata(): {String: AnyStruct} {
            return self.metadata;
        }

        pub fun getURL(): String? {
            return self.metadata[PunstersNFT.cidKey] as! String?;
        }
    }

    // Simplified information of `duanji`
    pub struct DuanjiView {
        pub let id: UInt64;
        pub let owner: Address;
        pub let description: String;
        pub let ipfsUrl: String;
        pub let funnyIndex: UInt32;
        pub let isAD: Bool;

        pub let commends: [Address];

        init(id: UInt64, owner: Address, description: String, ipfsUrl: String, fidx: UInt32, commends: [Address], _ ad: Bool) {
            self.id = id;
            self.owner = owner;
            self.description = description;
            self.ipfsUrl = ipfsUrl;
            self.funnyIndex = fidx;
            self.commends = commends;

            self.isAD = ad;
        }
    }

    pub struct PunsterView {
        pub let id: UInt64;
        pub let owner: Address;
        pub let description: String;
        pub let ipfsUrl: String;
        pub let funnyIndex: UInt32;

        pub let followings: [Address];
        pub let followers: [Address];

        init(id: UInt64, owner: Address, description: String, ipfsUrl: String, funnyIndex: UInt32, followings: [Address], followers: [Address]) {
            self.id = id;
            self.owner = owner;
            self.description = description;
            self.ipfsUrl = ipfsUrl;
            self.funnyIndex = funnyIndex;

            self.followings = followings;
            self.followers = followers;
        }
    }

    pub struct AdBox {
        // One Punster is only permission to publish one ad. at a moment.
        priv var adView: DuanjiView?;
        priv var startTime: UFix64;
        priv var timespan: UFix64;

        init() {
            self.adView = nil;
            self.startTime = 0.0;
            self.timespan = 0.0;
        }

        pub fun publishAD(dv: DuanjiView, fi: UInt32): Bool {
            let currentTime = getCurrentBlock().timestamp;
            
            if ((currentTime - self.startTime) > self.timespan) {
                
                if (UFix64(fi) == UFix64(0.0)) {
                    self.timespan = PunstersNFT.halfLife;
                }else if (UFix64(fi) < PunstersNFT.baseCommends) {
                    let alpha: UFix64 = UFix64(fi) / PunstersNFT.baseCommends;
                    self.timespan = PunstersNFT.halfLife - ((1.0 / (2.0 / alpha - 1.0)) * PunstersNFT.baseTimespan);
                } else {
                    let alpha: UFix64 = UFix64(fi) / PunstersNFT.baseCommends;
                    self.timespan = PunstersNFT.halfLife - ((2.0 - 1.0 / alpha) * PunstersNFT.baseTimespan);
                }
                
                self.startTime = currentTime;
                self.adView = dv;
                return true;
            } else{
                return false;
            }
        }

        pub fun getRemainingTime(): Fix64 {
            let currentTime = getCurrentBlock().timestamp;
            let remainingTime: Fix64 = Fix64(self.timespan) - Fix64(currentTime - self.startTime)
            
            if (remainingTime > 0.0) {
                return remainingTime;
            } else {
                return 0.0;
            }
        }

        pub fun getCurrentAD(): PunstersNFT.DuanjiView? {
            return self.adView;
        }
    }

    // `Punster` is a NFT and a NFT collection for `Duanji` NFT
    // This NFT will be locked for a time before being traded again
    pub resource Collection: StarRealm.StarDocker, IPunsterPublic, NonFungibleToken.INFT, MetadataViews.Resolver, NonFungibleToken.Provider, NonFungibleToken.Receiver, NonFungibleToken.CollectionPublic, MetadataViews.ResolverCollection {
        pub let id: UInt64;
        pub let timestamp: UFix64;
        // pub let acct: Address;

        pub let metadata: { String: AnyStruct };
        pub var ownedNFTs: @{UInt64: NonFungibleToken.NFT};

        pub let followings: [Address];
        pub let followers: [Address];

        priv var duanjiUpdates: [DuanjiView];
        priv var ad: AdBox;
        priv var latestUpdate: UFix64;
        priv var latestIdx: Int?;

        priv let commended: [UInt64];

        priv var isDestroy: Bool;

        init(
            id: UInt64,
            acct: Address,
            description: String,
            ipfsURL: String
        ) {
            self.id = id;
            self.timestamp = getCurrentBlock().timestamp;
            // self.acct = acct;
            self.metadata = {};
            self.metadata[PunstersNFT.cidKey] = ipfsURL;
            self.metadata[PunstersNFT.descriptionKey] = description;
            self.ownedNFTs <- {};

            self.followings = [];
            self.followers = [];

            self.duanjiUpdates = [];
            self.ad = AdBox();
            self.latestUpdate = self.timestamp;
            self.latestIdx = nil;

            self.commended = [];

            self.isDestroy = false;
        }

        pub fun preDestroy() {
            self.isDestroy = true;

            for ele in self.followings {
                if let punsterRef = PunstersNFT.getIPunsterFromAddress(addr: ele) {
                    punsterRef.destroyNotify(addr: self.owner!.address);
                }
            }

            for ele in self.followers {
                if let punsterRef = PunstersNFT.getIPunsterFromAddress(addr: ele) {
                    punsterRef.destroyNotify(addr: self.owner!.address);
                }
            }

            PunstersNFT.destroyPunsters(punsterID: self.id);
        }

        access(contract) destroy () {
            destroy self.ownedNFTs;
        }

        // -----------------------------------------------------------------------
        // NonFungibleToken Standard Functions---Collection
        // -----------------------------------------------------------------------
        pub fun withdraw(withdrawID: UInt64): @NonFungibleToken.NFT {
            let token <- self.ownedNFTs.remove(key: withdrawID) ?? panic("missing NFT")
            // emit Withdraw(id: token.id, from: self.owner?.address)
            return <-(token as! @NonFungibleToken.NFT)
        }

        pub fun deposit(token: @NonFungibleToken.NFT) {
            let token <- token as! @NFT
            let id: UInt64 = token.id
            let oldToken <- self.ownedNFTs[id] <- token
            // emit Deposit(id: id, to: self.owner?.address)
            destroy oldToken
        }

        pub fun getIDs(): [UInt64] {
            return self.ownedNFTs.keys
        }

        pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT {
            return (&self.ownedNFTs[id] as &NonFungibleToken.NFT?)!
        }

        pub fun borrowViewResolver(id: UInt64): &AnyResource{MetadataViews.Resolver} {
            let nft = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
            let test = nft as! &PunstersNFT.NFT
            return test as &AnyResource{MetadataViews.Resolver}
        }

        // -----------------------------------------------------------------------
        // NonFungibleToken Standard Functions---NFT
        // -----------------------------------------------------------------------
        pub fun getViews(): [Type] { 
            return [
                Type<MetadataViews.Display>()
            ]
        }

        pub fun resolveView(_ view: Type): AnyStruct? {
            switch view {
                case Type<MetadataViews.Display>():
                    var ipfsImage = MetadataViews.IPFSFile(cid: "No thumbnail cid set", path: "No thumbnail path set")
                    if (self.getMetadata().containsKey(PunstersNFT.cidKey)) {
                        ipfsImage = MetadataViews.IPFSFile(cid: self.getMetadata()[PunstersNFT.cidKey]! as! String, path: self.getMetadata()[PunstersNFT.pathKey] as! String?)
                    }
                    return MetadataViews.Display(
                        name: self.getMetadata()["name"] as! String? ?? "PunStar".concat(self.id.toString()),
                        description: self.getMetadata()["description"] as! String? ?? "No description set",
                        thumbnail: ipfsImage
                    )
            }

            return nil
        }

        pub fun getMetadata(): {String: AnyStruct} {
            return self.metadata;
        }

        // -----------------------------------------------------------------------
        // For collections
        // -----------------------------------------------------------------------
        pub fun getOwnedNFTsRef(): &{UInt64: NonFungibleToken.NFT} {
            return &self.ownedNFTs as &{UInt64: NonFungibleToken.NFT};
        }

        pub fun borrowDuanji(id: UInt64): &PunstersNFT.NFT? {
            if self.ownedNFTs[id] != nil {
                let ref = (&self.ownedNFTs[id] as auth &NonFungibleToken.NFT?)!
                return ref as! &PunstersNFT.NFT
            } else {
                return nil
            }
        }
        
        // -----------------------------------------------------------------------
        // Interface IPunsterPublic API
        // -----------------------------------------------------------------------
        // tell-fetch model. 
        // Notify followers to 
        pub fun notify(addr: Address) {
            // if (self.followings.contains(addr)) {
            //     if let punsterRef = PunstersNFT.getIPunsterFromAddress(addr: addr) {
            //         if let dv = punsterRef.getLatestDuanjiView() {
            //             self.duanjiUpdates.append(dv);
            //         }
            //     }
            // }
        }

        // return last update timestamp, that is `fun getCurrentBlock(): Block`
        pub fun getLatestUpdate(): UFix64{
            return self.latestUpdate;
        }



        pub fun getDuanjiViewFrom(timestamp: UFix64): [DuanjiView]{
            var outputViews: [DuanjiView] = [];
            // Add advertisement into view first
            if let ad = self.ad.getCurrentAD() {
                outputViews.append(ad);
            }

            for ele in self.ownedNFTs.keys {
                let nft = (&self.ownedNFTs[ele] as auth &NonFungibleToken.NFT?)!
                let temp = nft as! &PunstersNFT.NFT;
                if (temp.timestamp > timestamp) {
                    if let url = temp.getURL() {
                        outputViews.append(DuanjiView(id: ele, owner: self.owner!.address, description: temp.metadata[PunstersNFT.descriptionKey]! as! String, 
                                                        ipfsUrl: url, fidx: temp.getFunnyIndex(), commends: temp.getCommendAddress(), false));
                    }
                }
            }

            return outputViews;
        }

        // Return all of the punster's DuanjiViews
        pub fun getAllDuanjiView(): [DuanjiView] {
            var outputViews: [DuanjiView] = [];
            // Add advertisement into view first
            if let ad = self.ad.getCurrentAD() {
                outputViews.append(ad);
            }

            for ele in self.ownedNFTs.keys {
                let nft = (&self.ownedNFTs[ele] as auth &NonFungibleToken.NFT?)!
                let temp = nft as! &PunstersNFT.NFT;
                if let url = temp.getURL() {
                    outputViews.append(DuanjiView(id: ele, owner: self.owner!.address, description: temp.metadata[PunstersNFT.descriptionKey]! as! String, 
                                                    ipfsUrl: url, fidx: temp.getFunnyIndex(), commends: temp.getCommendAddress(), false));
                }
            }

            return outputViews;
        }

        pub fun getLatestDuanjiView(): DuanjiView? {
            if let lastKeyIndex = self.latestIdx {
                let id = UInt64(lastKeyIndex);
                if let nft = self.borrowDuanji(id: id) {
                    if let url = nft.getURL() {
                        return DuanjiView(id: nft.id, owner: self.owner!.address, description: nft.metadata[PunstersNFT.descriptionKey]! as! String,
                                            ipfsUrl: url, fidx: nft.getFunnyIndex(), commends: nft.getCommendAddress(), false);
                    }
                }
            }

            return nil;
        }

        // tell-fetch model.
        // Followed by other punster
        pub fun followedBy(addr: Address) {
            if (!self.followers.contains(addr)) {
                if let punsterRef = PunstersNFT.getIPunsterFromAddress(addr: addr){
                    if (punsterRef.isFollowing(addr: self.owner!.address)) {
                        self.followers.append(addr);
                    }
                }
            }
        }

        // Unfollowed by other punster
        pub fun cancelFollowedBy(addr: Address) {
            if let idx = self.followers.firstIndex(of: addr) {
                if let punsterRef = PunstersNFT.getIPunsterFromAddress(addr: addr) {
                    if (!punsterRef.isFollowing(addr: self.owner!.address)) {
                        self.followers.remove(at: idx);
                    }
                }
            }
        }

        // Tell other punster I'm following him
        pub fun isFollowing(addr: Address): Bool {
            if (self.followings.contains(addr)) {
                return true;
            } else {
                return false;
            }
        }

        // Public query information of follow machanism
        pub fun getFollowings(): [Address] {
            return self.followings;
        }

        pub fun getFollowers(): [Address] {
            return self.followers;
        }

        // returns duanji views of all the following punsters
        pub fun getAllFollowingDuanji(): [DuanjiView] {
            var outputViews: [DuanjiView] = [];
            for ele in self.followings {
                if let punsterRef = PunstersNFT.getIPunsterFromAddress(addr: ele) {
                    outputViews = outputViews.concat(punsterRef.getAllDuanjiView());
                }
            }
            return outputViews;
        }

        // returns all latest update duanji view
        pub fun getFollowingUpdates(): [DuanjiView] {
            return self.duanjiUpdates;
        }

        // tell-fetch model
        // Receive `Duanji` commending from others
        pub fun ReceiveCommend(addr: Address, duanjiID: UInt64) {
            if let punsterRef = PunstersNFT.getIPunsterFromAddress(addr: addr) {
                if (punsterRef.isCommended(duanjiID: duanjiID)) {
                    if let duanji = self.borrowDuanji(id: duanjiID) {
                        duanji.commend(addr: addr);
                    }
                }
            }
        }

        pub fun ReceiveCancelCommend(addr: Address, duanjiID: UInt64){
            if let punsterRef = PunstersNFT.getIPunsterFromAddress(addr: addr) {
                if (!punsterRef.isCommended(duanjiID: duanjiID)) {
                    if let duanji = self.borrowDuanji(id: duanjiID) {
                        duanji.cancelCommend(addr: addr);
                    }
                }
            }
        }

        pub fun isCommended(duanjiID: UInt64): Bool {
            if (self.commended.contains(duanjiID)) {
                return true;
            } else {
                return false;
            }
        }

        pub fun getFunnyIndex(): UInt32{
            var fi: UInt32 = 0;

            for ele in self.ownedNFTs.keys {
                let nft = (&self.ownedNFTs[ele] as auth &NonFungibleToken.NFT?)!
                let temp = nft as! &PunstersNFT.NFT;
                fi = fi + temp.getFunnyIndex();
            }

            return fi;
        }

        pub fun getPunsterView(): PunstersNFT.PunsterView {
            return PunstersNFT.PunsterView(id: self.id, 
                                            owner: self.owner!.address, 
                                            description: self.metadata[PunstersNFT.cidKey]! as! String,
                                            ipfsUrl: self.metadata[PunstersNFT.cidKey]! as! String,
                                            funnyIndex: self.getFunnyIndex(),
                                            followings: self.followings,
                                            followers: self.followers);

        }

        pub fun getADRemainingTime(): Fix64 {
            return self.ad.getRemainingTime();
        }

        pub fun destroyNotify(addr: Address) {
            // emit Destroy(info: addr.toString().concat("before clear"));

            if let punsterRef = PunstersNFT.getIPunsterFromAddress(addr: addr) {
                // emit Destroy(info: addr.toString().concat("in clear"));
                if (punsterRef.isDestroying()) {
                    if let idx = self.followings.firstIndex(of: addr) {
                        self.followings.remove(at: idx);
                    }

                    if let idx = self.followers.firstIndex(of: addr) {
                        self.followers.remove(at: idx);
                    }
                }
            }
        }

        pub fun isDestroying(): Bool {
            return self.isDestroy;
        }

        // -----------------------------------------------------------------------
        // StarDocker API, used for locker
        // -----------------------------------------------------------------------
        pub fun docking(nft: @AnyResource{NonFungibleToken.INFT}): @AnyResource{NonFungibleToken.INFT}? {
            if let duanjiNFT <- (nft as? @NonFungibleToken.NFT){
                self.deposit(token: <- duanjiNFT);
                return nil;
            } else {
                return <- nft;
            }
        }

        // -----------------------------------------------------------------------
        // Resouce API
        // -----------------------------------------------------------------------
        pub fun publishDuanji(description: String, ipfsURL: String) {

            let oldToken <-self.ownedNFTs[PunstersNFT.DuanjiTotal] <- create NFT(id: PunstersNFT.DuanjiTotal, 
                                                                                        description: description,
                                                                                        ipfsURL: ipfsURL);

            self.latestIdx = Int(PunstersNFT.DuanjiTotal);
            
            PunstersNFT.DuanjiTotal = PunstersNFT.DuanjiTotal + 1;
            PunstersNFT.totalSupply = PunstersNFT.DuanjiTotal;

            self.latestUpdate = getCurrentBlock().timestamp;

            for ele in self.followers {
                if let punsterRef = PunstersNFT.getIPunsterFromAddress(addr: ele) {
                    punsterRef.notify(addr: self.owner!.address);
                }
            }

            destroy oldToken;
        }

        pub fun postADs(description: String, ipfsURL: String) {
            if (self.ad.getRemainingTime() == 0.0) {
                let dv = PunstersNFT.DuanjiView(id: PunstersNFT.DuanjiTotal, 
                                            owner: self.owner!.address, 
                                            description: description, 
                                            ipfsUrl: ipfsURL, 
                                            fidx: 0, 
                                            commends: [],
                                            true);

                PunstersNFT.DuanjiTotal = PunstersNFT.DuanjiTotal + 1;
                PunstersNFT.totalSupply = PunstersNFT.DuanjiTotal;
                
                self.ad.publishAD(dv: dv, fi: self.getFunnyIndex());
            } else {
                panic("ADs posting is in cooling! Try it later! ".concat(self.getADRemainingTime().toString()).concat(" seconds left!"));
            }
        }

        pub fun clearUpdate() {
            self.duanjiUpdates = [];
        }

        pub fun followSomeone(addr: Address) {
            if (!self.followings.contains(addr)) {
                if let punsterRef = PunstersNFT.getIPunsterFromAddress(addr: addr){
                    self.followings.append(addr);
                    punsterRef.followedBy(addr: self.owner!.address);
                }
            }
        }

        pub fun cancelFollow(addr: Address) {
            if (self.followings.contains(addr)) {
                if let punsterRef = PunstersNFT.getIPunsterFromAddress(addr: addr) {
                    if let idx = self.followings.firstIndex(of: addr) {
                        self.followings.remove(at: idx);
                        punsterRef.cancelFollowedBy(addr: self.owner!.address);
                    }
                }
            }
        }

        pub fun commendToDuanji(addr: Address, duanjiID: UInt64) {
            if let punsterRef = PunstersNFT.getIPunsterFromAddress(addr: addr) {
                if (!self.commended.contains(duanjiID)) {
                    self.commended.append(duanjiID);
                }
                punsterRef.ReceiveCommend(addr: self.owner!.address, duanjiID: duanjiID);
            }
        }

        pub fun cancelCommendToDuanji(addr: Address, duanjiID: UInt64) {
            if let punsterRef = PunstersNFT.getIPunsterFromAddress(addr: addr) {
                if let idx = self.commended.firstIndex(of: duanjiID) {
                    self.commended.remove(at: idx);
                }
                punsterRef.ReceiveCancelCommend(addr: self.owner!.address, duanjiID: duanjiID);
            }
        }
    }

    pub fun getIPunsterFromAddress(addr: Address): &{IPunsterPublic}? {
        let pubAcct = getAccount(addr);
        let oIPunster = pubAcct.getCapability<&{IPunsterPublic}>(PunstersNFT.IPunsterPublicPath);
        return oIPunster.borrow();
    }

    // one account, one `Punster` NFT
    // This function is used for everyone to create 
    pub fun registerPunster(addr: Address, description: String, ipfsURL: String): @PunstersNFT.Collection{
        let punsterRes <- create Collection(id: self.PunsterTotal, acct: addr, description: description, ipfsURL: ipfsURL);
        self.PunsterTotal = self.PunsterTotal + 1; 
        self.registeredPunsters[punsterRes.id] = addr;
        return <-punsterRes
    }

    // view registed `Punsters`
    pub fun getRegisteredPunsters(): {UInt64: Address} {
        return self.registeredPunsters;
    }

    access(contract) fun destroyPunsters(punsterID: UInt64) {
        self.registeredPunsters.remove(key: punsterID);
    }

    // pub fun clearAllPunsters(punsterID: UInt64) {
    //     self.registeredPunsters.remove(key: punsterID);
    // }

    // pub fun setPunsterID(id: UInt64) {
    //     self.PunsterTotal = id;
    // }

    pub fun updateRegisteredPunster(punster: &PunstersNFT.Collection) {
        self.registeredPunsters[punster.id] = punster.owner!.address;
    }

    // Get Funny Index
    pub fun getDuanjiFunnyIndex(ownerAddr: Address, duanjiID: UInt64): UInt32?{
        if let punsterRef = self.getIPunsterFromAddress(addr: ownerAddr) {
            if let duanji = punsterRef.borrowDuanji(id: duanjiID) {
                return duanji.getFunnyIndex();
            }
        }

        return nil;
    }

    pub fun getPunsterFunnyIndex(ownerAddr: Address): UInt32? {
        if let punsterRef = self.getIPunsterFromAddress(addr: ownerAddr) {
            return punsterRef.getFunnyIndex();
        }

        return nil;
    }
}
