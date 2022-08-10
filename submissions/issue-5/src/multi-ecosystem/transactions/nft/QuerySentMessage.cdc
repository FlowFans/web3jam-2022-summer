import SentMessageContract from 0x1a478a7149935b63;
import CrossChain from 0x1a478a7149935b63;

pub fun main(): [SentMessageContract.SentMessageCore]{
  return SentMessageContract.QueryMessage(msgSender: 0x1a478a7149935b63, link: "sentMessageVault");
}