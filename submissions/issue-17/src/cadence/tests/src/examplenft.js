import { executeScript, sendTransaction, shallPass } from "flow-js-testing"

export const NFT_setupExampleNFTCollection = async (signer) => {
  const signers = [signer]
  const txName = "examplenft/setup_examplenft_collection"
  const args = []

  await shallPass(sendTransaction({ name: txName, signers: signers, args: args })) 
}

export const NFT_mintExampleNFT = async (admin, recipient) => {
  const name = "examplenft/mint_examplenft"
  const args = [recipient, "EXAMPLE", "FOR TEST", "", [], [], []]
  const signers = [admin]

  return shallPass(sendTransaction({ name, args, signers }))
}

export const NFT_getIDs = async (account) => {
  const name = "examplenft/get_examplenft_ids"
  const args = [account]
  const [result, error] = await executeScript({ name: name, args: args })
  expect(error).toBeNull()
  return result
}