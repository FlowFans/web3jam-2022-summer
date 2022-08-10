import { json } from '@remix-run/node' // or "@remix-run/cloudflare"
import type { LoaderFunction } from '@remix-run/node' // or "@remix-run/cloudflare"

export const loader: LoaderFunction = async ({ request, params }) => {
  // handle "GET" request
  const id = params.id
  return json({ success: true, id }, 200)
}
