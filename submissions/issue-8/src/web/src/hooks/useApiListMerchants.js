import PropTypes from "prop-types"
import {useMemo} from "react"
import {paths} from "src/global/constants"
import fetcher from "src/util/fetcher"
import laggy from "src/util/laggy"
import {normalizeMerchants} from "src/util/normalize-item"
import useSWR from "swr"

export default function useApiListMerchants(params) {
  const {data, error} = useSWR(paths.apiListMerchants(params), fetcher, {
    use: [laggy],
  })

  const merchantsListings = useMemo(() => {
    // Paginated queries return an object
    // console.log("data:" + JSON.stringify(data))
    const listingsArray = Array.isArray(data) ? data : data?.results
    const result =  listingsArray?.map(item => normalizeMerchants(item))
    console.log("result:" + JSON.stringify(result))
    return result
  }, [data])

  // return {listings, data, error, isLoading: !data && !error}
  return {merchantsListings, isBadgesLoading:false}
}

useApiListMerchants.propTypes = {
  params: PropTypes.object,
}
