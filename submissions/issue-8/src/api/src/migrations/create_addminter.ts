import {Knex} from "knex"

export async function up(knex: Knex): Promise<void> {
  return knex.schema.createTable("nft_minters", async table => {
    table.text("name")
    table.text("image_path")
    table.text("address")
    table.text("transaction_id").primary()
    table.timestamps(true, true)
  })
}

export async function down(knex: Knex): Promise<void> {
  return knex.schema.dropTable("nft_minters")
}
