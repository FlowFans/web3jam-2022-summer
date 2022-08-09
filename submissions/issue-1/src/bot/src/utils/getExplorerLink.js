const SupportedChainId = require("../constant/chains");

const ETHERSCAN_PREFIXES = {
  [SupportedChainId.MAINNET]: 'https://etherscan.io',
  [SupportedChainId.GOERLI]: 'https://goerli.etherscan.io',
  [SupportedChainId.POLYGON]: 'https://polygonscan.com',
}

const ExplorerDataType = {
  TRANSACTION: 'transaction',
  TOKEN: 'token',
  ADDRESS: 'address',
  BLOCK: 'block',
}

/**
 * Return the explorer link for the given data and data type
 * @param chainId the ID of the chain for which to return the data
 * @param data the data to return a link for
 * @param type the type of the data
 */
function getExplorerLink(chainId, data, type) {
  const prefix = ETHERSCAN_PREFIXES[chainId] ?? 'https://etherscan.io'

  switch (type) {
    case ExplorerDataType.TRANSACTION:
      return `${prefix}/tx/${data}`

    case ExplorerDataType.TOKEN:
      return `${prefix}/token/${data}`

    case ExplorerDataType.BLOCK:
      return `${prefix}/block/${data}`

    case ExplorerDataType.ADDRESS:
      return `${prefix}/address/${data}`
    default:
      return `${prefix}`
  }
}

module.exports = {
  getExplorerLink,
  ExplorerDataType,
}