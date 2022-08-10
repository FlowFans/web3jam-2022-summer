import * as fcl from "@onflow/fcl"
import * as t from "@onflow/types"
import * as fs from "fs"
import { NFTMinter } from "../models/nftminter"
import * as path from "path"
import { json } from "stream/consumers"
import {FlowService} from "./flow"
import { OnlyBadgesMinted } from "../models/onlybadge-minted"

const nonFungibleTokenPath = '"../../contracts/NonFungibleToken.cdc"'
const metadataViewsPath = '"../../contracts/MetadataViews.cdc"'
const openBadgesPath = '"../../contracts/OnlyBadges.cdc"'
const fungibleTokenPath = '"../../contracts/FungibleToken.cdc"'
const flowTokenPath = '"../../contracts/FlowToken.cdc"'
const storefrontPath = '"../../contracts/NFTStorefront.cdc"'


const PER_PAGE = 8
class KittyItemsService {
  constructor(
    private readonly flowService: FlowService,
    private readonly nonFungibleTokenAddress: string,
    private readonly metadataViewsAddress: string,
    public readonly kittyItemsAddress: string,
    private readonly fungibleTokenAddress: string,
    private readonly flowTokenAddress: string,
    private readonly storefrontAddress: string
  ) {}

  setupAccount = async () => {
    const authorization = this.flowService.authorizeMinter()

    const transaction = fs
      .readFileSync(
        path.join(
          __dirname,
          `../../../cadence/transactions/kittyItems/setup_account.cdc`
        ),
        "utf8"
      )
      .replace(
        nonFungibleTokenPath,
        fcl.withPrefix(this.nonFungibleTokenAddress)
      )
      .replace(openBadgesPath, fcl.withPrefix(this.kittyItemsAddress))

    return this.flowService.sendTx({
      transaction,
      args: [],
      authorizations: [authorization],
      payer: authorization,
      proposer: authorization,
    })
  }

  mint = async (recipient: string) => {
    const authorization = this.flowService.authorizeMinter()

    const transaction = fs
      .readFileSync(
        path.join(
          __dirname,
          `../../../cadence/transactions/kittyItems/min_nftminter.cdc`
        ),
        "utf8"
      )
      .replace(
        nonFungibleTokenPath,
        fcl.withPrefix(this.nonFungibleTokenAddress)
      )
      .replace(openBadgesPath, fcl.withPrefix(this.kittyItemsAddress))

    return this.flowService.sendTx({
      transaction,
      args: [
        fcl.arg(recipient, t.Address),
        fcl.arg("test", t.String),
        fcl.arg("test1", t.String),
      ],
      authorizations: [authorization],
      payer: authorization,
      proposer: authorization,
      skipSeal: true,
    })
  }

  signWithAdminMinter = (tx: string) => {
    console.log("tx:" + tx)
    return this.flowService.generateMinterSignature(tx)
  } 

  mintAndList = async (recipient: string) => {
    const authorization = this.flowService.authorizeMinter()

    const transaction = fs
      .readFileSync(
        path.join(
          __dirname,
          `../../../cadence/transactions/kittyItems/mint_and_list_kitty_item.cdc`
        ),
        "utf8"
      )
      .replace(
        nonFungibleTokenPath,
        fcl.withPrefix(this.nonFungibleTokenAddress)
      )
      .replace(openBadgesPath, fcl.withPrefix(this.kittyItemsAddress))
      .replace(fungibleTokenPath, fcl.withPrefix(this.fungibleTokenAddress))
      .replace(flowTokenPath, fcl.withPrefix(this.flowTokenAddress))
      .replace(storefrontPath, fcl.withPrefix(this.storefrontAddress))

    return this.flowService.sendTx({
      transaction,
      args: [
        fcl.arg(recipient, t.Address)
      ],
      authorizations: [authorization],
      payer: authorization,
      proposer: authorization,
      skipSeal: true,
    })
  }

  transfer = async (recipient: string, itemID: number) => {
    const authorization = this.flowService.authorizeMinter()

    const transaction = fs
      .readFileSync(
        path.join(
          __dirname,
          `../../../cadence/transactions/kittyItems/transfer_kitty_item.cdc`
        ),
        "utf8"
      )
      .replace(
        nonFungibleTokenPath,
        fcl.withPrefix(this.nonFungibleTokenAddress)
      )
      .replace(openBadgesPath, fcl.withPrefix(this.kittyItemsAddress))

    return this.flowService.sendTx({
      transaction,
      args: [fcl.arg(recipient, t.Address), fcl.arg(itemID, t.UInt64)],
      authorizations: [authorization],
      payer: authorization,
      proposer: authorization,
    })
  }

  getCollectionIds = async (account: string): Promise<number[]> => {
    const script = fs
      .readFileSync(
        path.join(
          __dirname,
          `../../../cadence/scripts/kittyItems/get_collection_ids.cdc`
        ),
        "utf8"
      )
      .replace(
        nonFungibleTokenPath,
        fcl.withPrefix(this.nonFungibleTokenAddress)
      )
      .replace(openBadgesPath, fcl.withPrefix(this.kittyItemsAddress))

    return this.flowService.executeScript<number[]>({
      script,
      args: [fcl.arg(account, t.Address)],
    })
  }

  getKittyItem = async (itemID: number, address: string): Promise<number> => {
    const script = fs
      .readFileSync(
        path.join(
          __dirname,
          `../../../cadence/scripts/kittyItems/get_kitty_item.cdc`
        ),
        "utf8"
      )
      .replace(
        nonFungibleTokenPath,
        fcl.withPrefix(this.nonFungibleTokenAddress)
      )
      .replace(metadataViewsPath, fcl.withPrefix(this.metadataViewsAddress))
      .replace(openBadgesPath, fcl.withPrefix(this.kittyItemsAddress))

    return this.flowService.executeScript<number>({
      script,
      args: [fcl.arg(address, t.Address), fcl.arg(itemID, t.UInt64)],
    })
  }

  getSupply = async (): Promise<number> => {
    const script = fs
      .readFileSync(
        path.join(
          __dirname,
          `../../../cadence/scripts/kittyItems/get_kitty_items_supply.cdc`
        ),
        "utf8"
      )
      .replace(openBadgesPath, fcl.withPrefix(this.kittyItemsAddress))

    return this.flowService.executeScript<number>({script, args: []})
  }

  addMinter = async listingEvent => {
    return NFTMinter.transaction(async tx => {
      const jsonstr = {
        name: listingEvent.data.minterName,
        image_path: listingEvent.data.minterImageFile,
        address: listingEvent.data.address,
        transaction_id: listingEvent.transactionId,
      };
      return await NFTMinter.query(tx)
        .insert(jsonstr)
        .returning("transaction_id")
        .onConflict("transaction_id")
        .ignore()
        .catch(e => {
          console.log(e)
        })
    });
  }

  onlybadgesMinted = async listingEvent => {
    return OnlyBadgesMinted.transaction(async tx => {
      const jsonstr = {
        owner: listingEvent.data.owner,
        id: listingEvent.data.id,
        name: listingEvent.data.name,
        badge_image: listingEvent.data.badge_image.cid,
        number: listingEvent.data.number,
        max: listingEvent.data.max,
        transaction_id: listingEvent.transactionId,
      };
      console.log("tx:" + tx)
      console.log("json:" + JSON.stringify(jsonstr))
      return await OnlyBadgesMinted.query(tx)
        .insert(jsonstr)
        .returning("transaction_id")
        .onConflict("id")
        .ignore()
        .catch(e => {
          console.log(e)
        })
    });
  }

  findMostRecentMinter = params => {
    return NFTMinter.transaction(async tx => {
      const query = NFTMinter.query(tx).select("*").orderBy("updated_at", "desc")

      if (params.name) {
        query.where("name", params.name)
      }

      if (params.address) {
        query.where("address", params.address)
      }

      if (params.page) {
        query.page(Number(params.page) - 1, PER_PAGE)
      }

      return await query
    })
  }

  getOnlyBadges = params => {
    return OnlyBadgesMinted.transaction(async tx => {
      const query = OnlyBadgesMinted.query(tx).select("*").orderBy("updated_at", "desc")

      if (params.name) {
        query.where("name", params.name)
      }

      if (params.owner) {
        query.where("owner", params.owner)
      }

      if (params.page) {
        query.page(Number(params.page) - 1, PER_PAGE)
      }

      return await query
    })
  }


}

export {KittyItemsService}
