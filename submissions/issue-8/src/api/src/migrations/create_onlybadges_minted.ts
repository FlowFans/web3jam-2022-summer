import {Knex} from "knex"

export async function up(knex: Knex): Promise<void> {
  return knex.schema.createTable("onlybadges_minted", async table => {
    table.integer("id").primary()
    table.text("owner")
    table.text("name")
    table.text("badge_image")
    table.integer("number")
    table.integer("max")
    table.text("transaction_id")
    table.timestamps(true, true)
  })
}

export async function down(knex: Knex): Promise<void> {
  return knex.schema.dropTable("onlybadges_minted")
}
