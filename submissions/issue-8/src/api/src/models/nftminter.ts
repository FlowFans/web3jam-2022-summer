import { BaseModel } from "./base"

class NFTMinter extends BaseModel {
  name!: string;
  image_path!: string;
  address!: string;
  transaction_id!: string;

  static get tableName() {
    return "nft_minters";
  }
}

export { NFTMinter }
