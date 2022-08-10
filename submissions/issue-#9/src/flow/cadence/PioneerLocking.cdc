// import NonFungibleToken from "./NonFungibleToken.cdc"

import NonFungibleToken from 0x631e88ae7f1d7c20

pub contract PioneerLocking {
    pub event PioneerNFTLocking(id: UInt64, duration: UFix64, expiryTimestamp: UFix64)
    pub event PioneerNFTUnlocked(id: UInt64)

    access(self) var lockedNFTs: {UInt64: UFix64}   
    access(self) var unlockableNFTs: {UInt64: Bool} 

    pub fun isLocked(nftRef: &NonFungibleToken.NFT): Bool {
        return self.lockedNFTs.containsKey(nftRef.uuid)
    }

    pub fun getLockExpiry(nftRef: &NonFungibleToken.NFT): UFix64 {
        if !self.lockedNFTs.containsKey(nftRef.uuid) {
            panic("NFT is not locked")
        }
        return self.lockedNFTs[nftRef.uuid]!
    }

    pub fun lockNFT(nft: @NonFungibleToken.NFT, duration: UFix64): @NonFungibleToken.NFT {
        let TopShotNFTType: Type = CompositeType("A.0b2a3299cc857e29.TopShot.NFT")!
        if !nft.isInstance(TopShotNFTType) {
            panic("NFT is not a TopShot NFT")
        }

        let uuid = nft.uuid
        if self.lockedNFTs.containsKey(uuid) {
            return <- nft
        }

        let expiryTimestamp = getCurrentBlock().timestamp + duration

        self.lockedNFTs[uuid] = expiryTimestamp

        emit PioneerNFTLocking(id: nft.id, duration: duration, expiryTimestamp: expiryTimestamp)

        return <- nft
    }

    pub fun unlockNFT(nft: @NonFungibleToken.NFT): @NonFungibleToken.NFT {
        let uuid = nft.uuid
        if !self.lockedNFTs.containsKey(uuid) {
            return <- nft
        }

        let lockExpiryTimestamp: UFix64 = self.lockedNFTs[uuid]!
        let isPastExpiry: Bool = getCurrentBlock().timestamp >= lockExpiryTimestamp

        let isUnlockableOverridden: Bool = self.unlockableNFTs.containsKey(uuid)

        if !(isPastExpiry || isUnlockableOverridden) {
            panic("NFT is not eligible to be unlocked, expires at ".concat(lockExpiryTimestamp.toString()))
        }

        self.unlockableNFTs.remove(key: uuid)
        self.lockedNFTs.remove(key: uuid)

        emit PioneerNFTUnlocked(id: nft.id)

        return <- nft
    }

    pub resource Admin {
        pub fun createNewAdmin(): @Admin {
            return <-create Admin()
        }
        pub fun markNFTUnlockable(nftRef: &NonFungibleToken.NFT) {
            PioneerLocking.unlockableNFTs[nftRef.uuid] = true
        }
    }

    init() {
        self.lockedNFTs = {}
        self.unlockableNFTs = {}

        let admin <- create Admin()

        self.account.save(<-admin, to: /storage/PioneerLockingAdmin)
    }
}
