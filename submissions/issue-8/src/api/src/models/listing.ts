import { BaseModel } from "./base"

class Listing extends BaseModel {
  listing_resource_id!: number;
  id!: number;
  creator!: string;
  name!: string;
  badge_image!: string;
  owner!: string;
  price!: number;
  transaction_id!: string;

  static get tableName() {
    return "listings";
  }
}

export { Listing }
