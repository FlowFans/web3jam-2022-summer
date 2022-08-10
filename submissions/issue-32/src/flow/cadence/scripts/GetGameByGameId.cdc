import Racenumber from 0xf8d6e0586b0a20c7
pub fun main(uid:UInt64):Racenumber.GameDetail {
    return Racenumber.getGameById(id:uid)
}