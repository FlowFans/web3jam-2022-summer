import {Knex} from "knex"

export async function up(knex: Knex): Promise<void> {
  return knex.schema.createTable("listings", async table => {
    table.integer("listing_resource_id").primary()
    table.integer("id")
    table.text("creator")
    table.text("owner")
    table.text("name")
    table.text("badge_image")
    table.decimal("price", null)
    table.text("transaction_id")
    table.timestamps(true, true)
  })
}

export async function down(knex: Knex): Promise<void> {
  return knex.schema.dropTable("listings")
}
