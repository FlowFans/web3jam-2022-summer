// import SoulMadePack from "../../contracts/SoulMadePack.cdc"

// testnet
import SoulMadePack from 0xb4187e54e0ed55a8


pub fun main(address: Address, ids : [UInt64]) : [SoulMadePack.PackDetail] {

    let receiverRef = getAccount(address)
                      .getCapability<&{SoulMadePack.CollectionPublic}>(SoulMadePack.CollectionPublicPath).borrow() ?? panic("Could not borrow the receiver reference")
        
    var res : [SoulMadePack.PackDetail] = []
		for id in ids{
			res.append(receiverRef.borrowPack(id: id).packDetail)
		}
    return res
}