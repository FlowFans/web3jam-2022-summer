import { json } from '@remix-run/node' // or "@remix-run/cloudflare"
import type { LoaderFunction } from '@remix-run/node' // or "@remix-run/cloudflare"
import fcl from '@onflow/fcl'
import t from '@onflow/types'

import { fclinit, isDiscordId, isFlowAddr } from '~/lib/flow/utils'
import { query } from '~/lib/flow/scripts'

export const loader: LoaderFunction = async ({ request, params }) => {
  fclinit()

  const url = new URL(request.url)
  const discordId = url.searchParams.get('discordId') ?? ''
  const address = url.searchParams.get('address') ?? ''

  let res = null
  console.log(discordId)
  console.log(address)

  if (isDiscordId(discordId)) {
    res = await query('get_address_by_dicord_id', [fcl.arg(discordId, t.String)])
    console.log(res)
  } else if (isFlowAddr(address)) {
    res = await query('get_discord_id_by_address', [fcl.arg(address, t.Address)])
    console.log(res)
  }
  return json({ success: true, res }, 200)
}
