import PropTypes from "prop-types"
import {useMemo} from "react"
import {paths} from "src/global/constants"
import fetcher from "src/util/fetcher"
import laggy from "src/util/laggy"
import {normalizeBadges} from "src/util/normalize-item"
import useSWR from "swr"

export default function useApiListBadges(params) {
  const {data, error} = useSWR(paths.apiMarketItemsList(params), fetcher, {
    use: [laggy],
  })

  const badges = useMemo(() => {
    // Paginated queries return an object
    const listingsArray = Array.isArray(data) ? data : data?.results
    const result =  listingsArray?.map(item => normalizeBadges(item))
    console.log("result1:" + JSON.stringify(result))
    return result
  }, [data])

  // return {listings, data, error, isLoading: !data && !error}
  return {badges, isLoading:false}
}

useApiListBadges.propTypes = {
  params: PropTypes.object,
}
