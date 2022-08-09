import { account } from "@onflow/fcl"

const scriptFlowBalance = (address: string | null) => {
  if (address == null) return Promise.resolve(null)
  // @ts-ignore
  return account(address).then(d => d.balance)
}

export default scriptFlowBalance