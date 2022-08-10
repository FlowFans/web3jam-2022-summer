
import ExampleNFTUser from 0xExampleNFTUser

pub fun main(id: UInt64): Bool{
    return ExampleNFTUser.getExpired(uuid: id)
}

