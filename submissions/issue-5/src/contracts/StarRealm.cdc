import NonFungibleToken from "./NonFungibleToken.cdc"

pub contract StarRealm {

    pub let PortStoragePath: StoragePath;
    pub let DockerPublicPath: PublicPath;

    // -----------------------------------------------------------------------
    // StarDocker API, used for locker
    // Return nil if successed, and return `nft` itself if failed
    // -----------------------------------------------------------------------
    pub resource interface StarDocker {
        pub fun docking(nft: @AnyResource{NonFungibleToken.INFT}): @AnyResource{NonFungibleToken.INFT}?;
    }

    pub resource StarPort: StarDocker {
        priv var ownedNFT: @AnyResource{NonFungibleToken.INFT}?;

        init() {
            self.ownedNFT <- nil;
        }

        pub fun sailing(): @AnyResource{NonFungibleToken.INFT}? {
            if let nftRes <- self.ownedNFT <- nil {
                return <- nftRes;
            } else {
                return nil
            }
        }

        // -----------------------------------------------------------------------
        // StarDocker API, used for locker
        // Return nil if successed, and return `nft` itself if failed
        // -----------------------------------------------------------------------
        pub fun docking(nft: @AnyResource{NonFungibleToken.INFT}): @AnyResource{NonFungibleToken.INFT}? {
            if self.ownedNFT == nil {
                self.ownedNFT <-! nft;
                return nil;
            } else {
                return <- nft;
            }
            // let oldpunster <- self.punster <- nft;
            // destroy oldpunster;
        }

        destroy() {
            destroy self.ownedNFT;
        }
    }

    init() {
        self.PortStoragePath = StoragePath(identifier: "PortStoragePath".concat(self.account.address.toString()))!;
        self.DockerPublicPath = PublicPath(identifier: "DockerPublicPath".concat(self.account.address.toString()))!;
    }

    pub fun getStarDockerFromAddress(addr: Address): &{StarRealm.StarDocker}? {
        let pubAcct = getAccount(addr);
        let docker = pubAcct.getCapability<&{StarRealm.StarDocker}>(self.DockerPublicPath);
        return docker.borrow();
    }

    pub fun createStarPort(): @StarPort {
        return <- create StarPort();
    }
}