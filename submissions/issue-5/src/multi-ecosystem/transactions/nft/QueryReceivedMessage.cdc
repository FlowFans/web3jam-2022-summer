import ReceivedMessageContract from 0x1a478a7149935b63;
import CrossChain from 0x1a478a7149935b63;

pub fun main(
    recvAddress: Address,
    link: String
): {String: UInt128}{
  return ReceivedMessageContract.queryCompletedID(recvAddress: recvAddress, link: link);
}