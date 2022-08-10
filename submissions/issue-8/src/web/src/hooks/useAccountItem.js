import PropTypes from "prop-types"
import {fetchAccountItem} from "src/flow/script.get-account-item"
import {apiListingType} from "src/global/types"
import {normalizeBadges} from "src/util/normalize-item"
import useSWR from "swr"

export function compAccountItemKey(address, id) {
  if (typeof address === "undefined" || typeof id === "undefined") return null
  return `${address}/account-item/${id}`
}

export function expandAccountItemKey(key) {
  const paths = key.split("/")
  return {address: paths[0], id: Number(paths[paths.length - 1])}
}

export default function useAccountItem(address, id, listing) {
  const {data, error} = useSWR(
    compAccountItemKey(address, id),
    fetchAccountItem
  )
  console.log("useAccountItem:" + JSON.stringify(data))
  const item = data ? normalizeBadges(data, listing) : undefined
  console.log("normalizeBadges:" + JSON.stringify(item))
  return {item, error, isLoading: !data && !error}
}

useAccountItem.propTypes = {
  address: PropTypes.string,
  id: PropTypes.number,
  listing: apiListingType,
}
