import arbitrumLogoUrl from "../assets/svg/arbitrum_logo.svg"
import polygonLogoUrl from "../assets/svg/polygon-logo.svg"
import ethLogoUrl from "../assets/svg/ETH.svg"

export enum SupportedChainId {
  MAINNET = 1,
  ROPSTEN = 3,
  RINKEBY = 4,
  GOERLI = 5,
  KOVAN = 42,

  ARBITRUM_ONE = 42161,
  ARBITRUM_RINKEBY = 421611,

  MUMBAI = 80001,
  POLYGON = 137,
}

export const ALL_SUPPORTED_CHAIN_IDS: SupportedChainId[] = [
  SupportedChainId.MAINNET,
  SupportedChainId.ROPSTEN,
  SupportedChainId.RINKEBY,
  SupportedChainId.GOERLI,
  SupportedChainId.KOVAN,

  SupportedChainId.ARBITRUM_ONE,
  SupportedChainId.ARBITRUM_RINKEBY,

  SupportedChainId.MUMBAI,
  SupportedChainId.POLYGON,
]

export const L1_CHAIN_IDS = [
  SupportedChainId.MAINNET,
  SupportedChainId.ROPSTEN,
  SupportedChainId.RINKEBY,
  SupportedChainId.GOERLI,
  SupportedChainId.KOVAN,
] as const

export type SupportedL1ChainId = typeof L1_CHAIN_IDS[number]

export const L2_CHAIN_IDS = [
  SupportedChainId.ARBITRUM_ONE,
  SupportedChainId.ARBITRUM_RINKEBY,
  SupportedChainId.MUMBAI,
  SupportedChainId.POLYGON,
] as const

export type SupportedL2ChainId = typeof L2_CHAIN_IDS[number]

interface L1ChainInfo {
  readonly chainId: number
  readonly docs: string
  readonly explorer: string
  readonly infoLink: string
  readonly label: string
  readonly logoUrl: string
}
export interface L2ChainInfo extends L1ChainInfo {
  readonly bridge: string
}

type ChainInfo = { readonly [chainId: number]: L1ChainInfo | L2ChainInfo } & {
  readonly [chainId in SupportedL2ChainId]: L2ChainInfo
} & { readonly [chainId in SupportedL1ChainId]: L1ChainInfo }

export const CHAIN_INFO: ChainInfo = {
  [SupportedChainId.ARBITRUM_ONE]: {
    chainId: SupportedChainId.ARBITRUM_ONE,
    bridge: "https://bridge.arbitrum.io/",
    docs: "https://offchainlabs.com/",
    explorer: "https://arbiscan.io/",
    infoLink: "",
    label: "Arbitrum",
    logoUrl: arbitrumLogoUrl,
  },
  [SupportedChainId.ARBITRUM_RINKEBY]: {
    chainId: SupportedChainId.ARBITRUM_RINKEBY,
    bridge: "https://bridge.arbitrum.io/",
    docs: "https://offchainlabs.com/",
    explorer: "https://rinkeby-explorer.arbitrum.io/",
    infoLink: "",
    label: "Arbitrum Rinkeby",
    logoUrl: arbitrumLogoUrl,
  },
  [SupportedChainId.MUMBAI]: {
    chainId: SupportedChainId.MUMBAI,
    bridge: "https://wallet-dev.polygon.technology/",
    docs: "https://polygon.technology/",
    explorer: "https://mumbai.polygonscan.com/",
    infoLink: "",
    label: "Mumbai",
    logoUrl: polygonLogoUrl,
  },
  [SupportedChainId.POLYGON]: {
    chainId: SupportedChainId.POLYGON,
    bridge: "https://wallet.polygon.technology/bridge",
    docs: "https://polygon.technology/",
    explorer: "https://polygonscan.com/",
    infoLink: "",
    label: "Polygon",
    logoUrl: polygonLogoUrl,
  },
  [SupportedChainId.MAINNET]: {
    chainId: SupportedChainId.MAINNET,
    docs: "https://docs.uniswap.org/",
    explorer: "https://etherscan.io/",
    infoLink: "",
    label: "Mainnet",
    logoUrl: ethLogoUrl,
  },
  [SupportedChainId.RINKEBY]: {
    chainId: SupportedChainId.RINKEBY,
    docs: "https://docs.uniswap.org/",
    explorer: "https://rinkeby.etherscan.io/",
    infoLink: "",
    label: "Rinkeby",
    logoUrl: ethLogoUrl,
  },
  [SupportedChainId.ROPSTEN]: {
    chainId: SupportedChainId.ROPSTEN,
    docs: "https://docs.uniswap.org/",
    explorer: "https://ropsten.etherscan.io/",
    infoLink: "",
    label: "Ropsten",
    logoUrl: ethLogoUrl,
  },
  [SupportedChainId.KOVAN]: {
    chainId: SupportedChainId.KOVAN,
    docs: "https://docs.uniswap.org/",
    explorer: "https://kovan.etherscan.io/",
    infoLink: "",
    label: "Kovan",
    logoUrl: ethLogoUrl,
  },
  [SupportedChainId.GOERLI]: {
    chainId: SupportedChainId.GOERLI,
    docs: "https://docs.uniswap.org/",
    explorer: "https://goerli.etherscan.io/",
    infoLink: "",
    label: "Görli",
    logoUrl: ethLogoUrl,
  },
}
