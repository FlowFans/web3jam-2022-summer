/* 

        // todo: so to give something for free. we need another collection for this right
        let freeCollectionStoragePath =/storage/SoulMadeMainCollectionFree
        let freeCollectionPublicPath =/public/SoulMadeMainCollectionFree
        
        account.save(<- SoulMadeMain.createEmptyCollection(), to: freeCollectionStoragePath)
        // todo: we should specify interface here.
        // todo: the interface will anyway be double checked!
        account.link<&{NonFungibleToken.Provider, NonFungibleToken.CollectionPublic}>(freeCollectionPublicPath, target: freeCollectionStoragePath)


*/