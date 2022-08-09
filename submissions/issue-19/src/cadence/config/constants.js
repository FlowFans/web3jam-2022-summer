import { build } from '@onflow/sdk'
import dotenv from 'dotenv'
dotenv.config()

export const nodeUrl = process.env.FLOW_ACCESS_NODE

export const privateKey = process.env.FLOW_ACCOUNT_PRIVATE_KEY

export const publicKey = process.env.FLOW_ACCOUNT_PUBLIC_KEY

export const accountKeyId = process.env.FLOW_ACCOUNT_KEY_ID

export const accountAddr = process.env.FLOW_ACCOUNT_ADDRESS

export const FLOWTokenAddr = process.env.FLOW_TOKEN_ADDRESS

export const FUSDTokenAddr = process.env.FUSD_TOKEN_ADDRESS

export const flowFungibleAddr = process.env.FLOW_FUNGIBLE_ADDRESS

export const flowNonFungibleAddr = process.env.FLOW_NONFUNGIBLE_ADDRESS

export const metadataViewsAddr = process.env.FLOW_METADATA_VIEW_ADDRESS

export const network = process.env.FLOW_NETOWRK

const buildPath = (fileName, type) => {
  let filePath = ''
  switch (type) {
    case 'script':
      filePath = `../cadence/scripts/${fileName}`
      break
    default:
      filePath = `../cadence/transactions/${fileName}`
  }
  return filePath
}

export const paths = {
  scripts: {
    getFLOW: buildPath('get_flow_balance.cdc', 'script'),
    getKIBBLE: buildPath('get_kibble_balance.cdc', 'script'),
    getFUSD: buildPath('get_fusd_balance.cdc', 'script'),
    getTimestamp: buildPath('get_block_timestamp.cdc', 'script'),

    checkInit: buildPath('check_init_state.cdc', 'script'),
    getGraceDuration: buildPath('get_grace_duration.cdc', 'script'),
    getPause: buildPath('get_pause.cdc', 'script'),
    getCommision: buildPath('get_commision.cdc', 'script'),
    getMinimumPaymentAmount: buildPath('get_minimum_payment_amount.cdc', 'script'),
    getNFTMetadata: buildPath('get_nft_metadata.cdc', 'script'),
    getTicketMetadata: buildPath('get_ticket_metadata.cdc', 'script'),
    getNFTTotalSupply: buildPath('get_nft_total_supply.cdc', 'script'),
    getPaymentInfo: buildPath('get_payment_info.cdc', 'script'),
    getStreamCount: buildPath('get_streaming_count.cdc', 'script'),
    getVestingCount: buildPath('get_vesting_count.cdc', 'script'),
    getTotalPaymentCount: buildPath('get_total_payment_count.cdc', 'script'),
    getTotalPaymentCount: buildPath('get_total_payment_count.cdc', 'script'),
    getUserIncomePayment: buildPath('get_user_income_tickets.cdc', 'script'),
    getOutgoingPayment: buildPath('get_user_outgoing_payments.cdc', 'script'),
    getUserUnclaimTickets: buildPath('get_user_unclaim_tickets.cdc', 'script'),
    getPaymentClaimable: buildPath('get_payment_claimable.cdc', 'script'),
    getUserStats: buildPath('get_user_stats.cdc', 'script'),
  },

  transactions: {
    initTokens: buildPath('init_tokens.cdc'),
    mintFLOW: buildPath('mint_flow_token.cdc'),
    mintFUSD: buildPath('mint_fusd.cdc'),
    mintKIBBLE: buildPath('mint_kibble.cdc'),
    transferFLOW: buildPath('transfer_flow.cdc'),
    transferFUSD: buildPath('transfer_fusd.cdc'),
    transferKibble: buildPath('transfer_kibble.cdc'),
    setupAccount: buildPath('setup_account.cdc'),
    //---== init
    setCommision: buildPath('set_commision.cdc'),
    setGraceDuration: buildPath('set_grace_duration.cdc'),
    setMinimumPayment: buildPath('set_minimum_payment.cdc'),
    setPause: buildPath('set_pause.cdc'),

    // ==
    revokePayment: buildPath('rovoke_payment.cdc'),
    changeRevocable: buildPath('change_revocable.cdc'),
    changeTransferable: buildPath('change_transferable.cdc'),
    claimTicket: buildPath('claim_ticket.cdc'),
    claimAllTicket: buildPath('claim_all_ticket.cdc'),

    createStream: buildPath('create_stream.cdc'),
    createVesting: buildPath('create_vesting.cdc'),
    createSimpleVesting: buildPath('create_simple_vesting.cdc'),
    transferTicket: buildPath('transfer_ticket.cdc'),
    withdraw: buildPath('withdraw.cdc'),
    destoryTicket: buildPath('destory_ticket.cdc'),
  },
}
