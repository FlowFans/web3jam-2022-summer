export const nodeUrl = process.env.FLOW_ACCESS_NODE

export const discoveryUrl = process.env.DISCOVERY_URL

export const discoveryEndpointUrl = process.env.DISCOVERY_ENDPOINT_URL

export const flowTokenAddr = process.env.FLOW_TOKEN_ADDRESS

export const fusdTokenAddr = process.env.FUSD_TOKEN_ADDRESS

export const flowFungibleAddr = process.env.FLOW_FUNGIBLE_ADDRESS

export const flowNonFungibleAddr = process.env.FLOW_NONFUNGIBLE_ADDRESS

export const emeraldIdAddress = process.env.EMERALD_ID_ADDRESS

export const gaCode = process.env.GA

export const network = process.env.NETWORK

export const isTestnet = network == 'testnet'

export const graffleId = process.env.GRAFFLE_ID

export const privateKey = process.env.PRIVATE_KEY
export const publicKey = process.env.PUBLIC_KEY

export const getGraffleUrl = () => {
  let url = `https://prod-${
    isTestnet ? 'test-net' : 'main-net'
  }-dashboard-api.azurewebsites.net/api/company/${graffleId}/search`
  return url
}

export const getExplorerTxUrl = (type = 'flowScan') => {
  const flowScanUrl = `https://${isTestnet ? 'testnet.' : ''}flowscan.org/transaction/`
  const viewSourceUrl = `https://flow-view-source.com/${network}/tx/`
  return type == 'flowScan' ? flowScanUrl : viewSourceUrl
}

export const getSupportTokenConfig = (): any => {
  let tokenConfigs = {}
  if (isTestnet) {
    tokenConfigs = {
      FLOW: {
        type: 'A.7e60df042a9c0868.FlowToken.Vault',
        publicBalPath: getSupportTokenVaultPath()['FLOW'],
        publicReceiverPath: getSupportTokenVaultPath('receiver')['FLOW'],
        storagePath: getSupportTokenVaultPath('private')['FLOW'],
      },
      FUSD: {
        type: 'A.e223d8a629e49c68.FUSD.Vault',
        publicBalPath: getSupportTokenVaultPath()['FUSD'],
        publicReceiverPath: getSupportTokenVaultPath('receiver')['FUSD'],
        storagePath: getSupportTokenVaultPath('private')['FUSD'],
      },
      BLT: {
        type: 'A.6e0797ac987005f5.BloctoToken.Vault',
        publicBalPath: getSupportTokenVaultPath()['BLT'],
        publicReceiverPath: getSupportTokenVaultPath('receiver')['BLT'],
        storagePath: getSupportTokenVaultPath('private')['BLT'],
      },
      USDC: {
        type: 'A.a983fecbed621163.FiatToken.Vault',
        publicBalPath: getSupportTokenVaultPath()['USDC'],
        publicReceiverPath: getSupportTokenVaultPath('receiver')['USDC'],
        storagePath: getSupportTokenVaultPath('private')['USDC'],
      },
      MY: {
        type: 'A.40212f3e288efd03.MyToken.Vault',
        publicBalPath: getSupportTokenVaultPath()['MY'],
        publicReceiverPath: getSupportTokenVaultPath('receiver')['MY'],
        storagePath: getSupportTokenVaultPath('private')['MY'],
      },
    }
  } else {
    tokenConfigs = {
      FLOW: {
        type: 'A.1654653399040a61.FlowToken.Vault',
        publicBalPath: getSupportTokenVaultPath()['FLOW'],
        publicReceiverPath: getSupportTokenVaultPath('receiver')['FLOW'],
        storagePath: getSupportTokenVaultPath('private')['FLOW'],
      },
      FUSD: {
        type: 'A.3c5959b568896393.FUSD.Vault',
        publicBalPath: getSupportTokenVaultPath()['FUSD'],
        publicReceiverPath: getSupportTokenVaultPath('receiver')['FUSD'],
        storagePath: getSupportTokenVaultPath('private')['FUSD'],
      },
      BLT: {
        type: 'A.0f9df91c9121c460.BloctoToken.Vault',
        publicBalPath: getSupportTokenVaultPath()['BLT'],
        publicReceiverPath: getSupportTokenVaultPath('receiver')['BLT'],
        storagePath: getSupportTokenVaultPath('private')['BLT'],
      },
      USDC: {
        type: 'A.b19436aae4d94622.FiatToken.Vault',
        publicBalPath: getSupportTokenVaultPath()['USDC'],
        publicReceiverPath: getSupportTokenVaultPath('receiver')['USDC'],
        storagePath: getSupportTokenVaultPath('private')['USDC'],
      },
      MY: {
        type: 'A.348fe2042c8a70d8.MyToken.Vault',
        publicBalPath: getSupportTokenVaultPath()['MY'],
        publicReceiverPath: getSupportTokenVaultPath('receiver')['MY'],
        storagePath: getSupportTokenVaultPath('private')['MY'],
      },
    }
  }
  return tokenConfigs
}

export const getSupportTokenVaultPath = (type = 'balance'): any => {
  let paths = {}
  switch (type) {
    case 'balance':
      paths = {
        FLOW: '/public/flowTokenBalance',
        FUSD: '/public/fusdBalance',
        BLT: '/public/bloctoTokenBalance',
        USDC: 'FiatToken.VaultBalancePubPath',
        MY: '/public/mytokenBalance',
      }
      break
    case 'receiver':
      paths = {
        FLOW: '/public/flowTokenReceiver',
        FUSD: '/public/fusdReceiver',
        BLT: '/public/bloctoTokenReceiver',
        USDC: 'FiatToken.VaultReceiverPubPath',
        MY: '/public/mytokenReceiver',
      }
      break
    case 'private':
      paths = {
        FLOW: '/storage/flowTokenVault',
        FUSD: '/storage/fusdVault',
        BLT: '/storage/bloctoTokenVault',
        USDC: 'FiatToken.VaultStoragePath',
        MY: '/storage/mytokenVault',
      }
  }

  return paths
}
