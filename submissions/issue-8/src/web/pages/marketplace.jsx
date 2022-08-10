import Link from "next/link"
import {useRouter} from "next/router"
import PropTypes from "prop-types"
import {useEffect, useState} from "react"
import ListItems from "src/components/ListItems"
import MarketplaceFilters from "src/components/MarketplaceFilters"
import PageTitle from "src/components/PageTitle"
import Pagination from "src/components/Pagination"
import {paths} from "src/global/constants"
import useApiListings from "src/hooks/useApiListings"
import useAppContext from "src/hooks/useAppContext"
import {cleanObject} from "src/util/object"
import PopularMerchants from "src/components/PopularMerchants"
import useApiListMerchants from "src/hooks/useApiListMerchants"
import Footer from "src/components/Footer"


const PER_PAGE = 12

const MainContent = ({queryState}) => {
  const router = useRouter()

  const {currentUser} = useAppContext()
  const {merchantsListings, isLoading} = useApiListMerchants()
  const {listings, data} = useApiListings({
    ...queryState,
    // owner: undefined,
  })
  const showPagination = data?.total !== undefined

  const updateQuery = (payload, scroll = true) => {
    const newQueryObject = {...queryState, ...payload}
    console.log("execute query:" + JSON.stringify(newQueryObject))
    console.log("execute query payload:" + JSON.stringify(payload))
    console.log("execute query queryState:" + JSON.stringify(queryState))
    router.push(
      {
        pathname: router.pathname,
        query: cleanObject({
          ...newQueryObject,
          page: newQueryObject.page === 1 ? undefined : newQueryObject.page,
        }),
      },
      undefined,
      {
        scroll: scroll,
      }
    )
  }

  const onPageClick = (newPage, scroll) => updateQuery({page: newPage}, scroll)

  return (
    <div className="main-container mx-auto py-14">
      <div className="text-center mb-8 ">
        <div className="-mt-8 text-center font-extrabold text-7xl bg-clip-text text-transparent bg-gradient-to-tl from-teal-500  via-orange-500  via-indigo-600 via-red-300 to-blue-400 ">Marketplace</div>
        {/* {!!currentUser && (
          <Link href={paths.profile(currentUser.addr)}>
            <a className=" uppercase font-bold text-m text-white rounded-full bg-pink-600 hover:opacity-70 marketplace-list-my-badges-button">
              List My Badges
            </a>
          </Link>
        )} */}
      </div>

      <hr className="pt-1 mb-8" />

      {/* {typeof queryState !== "undefined" && (
        <MarketplaceFilters queryState={queryState} updateQuery={updateQuery}>
          {showPagination && (
            <Pagination
              currentPage={queryState.page}
              total={data.total}
              perPage={PER_PAGE}
              onPageClick={newPage => onPageClick(newPage, false)}
            />
          )}
        </MarketplaceFilters>
      )} */}

      {typeof queryState !== "undefined" && (
        <PopularMerchants items={merchantsListings} updateQuery={updateQuery}/>
       )}

      <div className="main-container flex pt-10 flex-col sm:flex-row ">
        <div className="flex ml-48">
          <div className="mr-96 -ml-48">
              <h1 className="text-4xl text-gray-darkest mb-1">
                Badges Listing
              </h1>
              <div className="text-xl text-gray-light">
                Check out the latest listing badges.
              </div>
            </div>
          <div className="ml-96">
            {!!currentUser && (
              <Link href={paths.profile(currentUser.addr)}>
                <div className=" uppercase font-bold text-m text-white rounded-full bg-pink-600 hover:opacity-70 marketplace-list-my-badges-button">
                  List My Badges
                </div>
              </Link>
            )}
          </div>

        </div>
      </div>
      {!!listings && <ListItems items={listings} />}

      {showPagination && (
        <div className="flex items-center justify-center mt-16 py-6">
          <Pagination
            currentPage={queryState.page}
            total={data.total}
            perPage={PER_PAGE}
            onPageClick={onPageClick}
          />
        </div>
      )}
    </div>
  )
}

export default function Marketplace() {
  const router = useRouter()

  const [queryState, setQueryState] = useState()

  useEffect(() => {
    if (router.isReady) {
      setQueryState({
        ...router.query,
        page: Number(router.query.page || 1),
      })
    }
  }, [router])

  return (
    <div>
      <PageTitle>Marketplace</PageTitle>
      <main>
        <div>
          <MainContent queryState={queryState} />
        </div>
      </main>
      <Footer/>
    </div>
  )
}

MainContent.propTypes = {
  queryState: PropTypes.object,
}
