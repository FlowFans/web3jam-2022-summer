
import NonFungibleToken from 0xNonFungibleToken
import OverluPackage from 0xOverluPackage
import OverluDNA from 0xOverluDNA
import OverluModel from 0xOverluModel

pub fun main(addr: Address): Bool{
    var inited = true
    let account = getAccount(addr)
    inited = account.getCapability<&{OverluPackage.CollectionPublic}>(OverluPackage.CollectionPublicPath).check()
    inited = account.getCapability<&{OverluDNA.CollectionPublic}>(OverluDNA.CollectionPublicPath).check()
    inited = account.getCapability<&{OverluModel.CollectionPublic}>(OverluModel.CollectionPublicPath).check()
   
    return inited
}
