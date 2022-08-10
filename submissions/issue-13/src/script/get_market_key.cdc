import TicketNFT from 0xTicketNFT

//Result: [0x569915ef0ece1609, 0x1dd1316850f649ea, 0xd1183642a19fd336, 0xde0b02c3b3126a85, 0x8817b7aa9096c618]
// 0xcc2156ea0b55aa52

pub fun main():[Address]{
    let arr =TicketNFT.middleOwner.keys
    return arr
}