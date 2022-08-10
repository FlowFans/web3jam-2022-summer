import { 
  getAccountAddress,
  deployContractByName,
  sendTransaction,
  executeScript,
  shallPass,
  shallResolve,
  mintFlow
} from "flow-js-testing"

export const getCloudAdmin = async () => getAccountAddress("CloudAdmin")
export const getMistAdmin = async () => getAccountAddress("MistAdmin")

export const deployCoreContracts = async (deployer) => {
  const Deployer = deployer
  await mintFlow(Deployer, 1000.0)
  await deployByName(Deployer, "core/NonFungibleToken")
  await deployByName(Deployer, "core/MetadataViews")
  await deployByName(Deployer, "core/FUSD")
}

export const deployFLOATContracts = async (deployer) => {
  const Deployer = deployer
  await mintFlow(Deployer, 1000.0)
  await deployByName(Deployer, "float/GrantedAccountAccess")
  await deployByName(Deployer, "float/FLOAT")
  await deployByName(Deployer, "float/FLOATVerifiers")
}

export const deployExampleNFTContracts = async (deployer) => {
  const Deployer = deployer
  await mintFlow(Deployer, 1000.0)
  await deployByName(Deployer, "examplenft/ExampleNFT")
}

export const deployDrizzleContracts = async (deployer) => {
  const Deployer = deployer
  await mintFlow(Deployer, 1000.0)
  await deployByName(Deployer, "Distributors")
  await deployByName(Deployer, "EligibilityVerifiers")
  await deployByName(Deployer, "Cloud")
  await deployByName(Deployer, "Mist")
}

export const deployContracts = async (deployer) => {
  await deployCoreContracts(deployer)
  await deployFLOATContracts(deployer)
  await deployDrizzleContracts(deployer)
}

export const deployByName = async (deployer, contractName, args) => {
  const [, error] = await deployContractByName({ to: deployer, name: contractName, args: args })
  expect(error).toBeNull()
}

export const setupFUSDVault = async (account) => {
  const signers = [account]
  const name = "fusd/setup_fusd_vault"
  await shallPass(sendTransaction({ name: name, signers: signers }))
}

export const mintFUSD = async (minter, amount, recipient) => {
  const signers = [minter]
  const args = [amount, recipient]
  const name = "fusd/mint_fusd"
  await shallPass(sendTransaction({ name: name, args: args, signers: signers }))
}

export const getFUSDBalance = async (account) => {
  const [result, err] = await shallResolve(executeScript({ name: "fusd/get_fusd_balance", args: [account] }))
  return parseFloat(result)
}

export const checkFUSDBalance = async (account, expectedBalance) => {
  const balance = await getFUSDBalance(account)
  expect(balance).toBe(expectedBalance)
}