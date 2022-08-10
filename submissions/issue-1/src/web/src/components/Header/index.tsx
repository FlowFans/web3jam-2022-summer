import {HStack, Link} from "@chakra-ui/react"
import { WalletModal } from "../Web3Status"

const Header = () => {
  return (
    <HStack justifyContent={"space-between"}>
      <Link fontWeight={"bold"} href={'/#/'}>Wakanda+</Link>
      <WalletModal />
    </HStack>
  )
}

export default Header
