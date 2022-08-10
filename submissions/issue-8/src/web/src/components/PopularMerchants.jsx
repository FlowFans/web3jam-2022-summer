import PropTypes from "prop-types"
import {useLayoutEffect, useMemo, useRef, useState} from "react"
import MerchantsListItem from "src/components/MerchantsListItem"
import {storeItemsSelector} from "src/global/selectors"
import {normalizedMerchantItemType} from "src/global/types"
import {useDebouncedCallback} from "use-debounce"

const ITEMS_LENGTH = 10
const ITEM_WIDTH = 232

export default function PopularMerchants({items, queryState, updateQuery}) {

  const listRef = useRef()
  const [reachedScrollEnd, setReachedScrollEnd] = useState(false)
  const [offsetWidth, setOffsetWidth] = useState(0)
  const [scrollLeft, setScrollLeft] = useState(0)
  // const storeItems = storeItemsSelector(items)
  //   .slice(0, ITEMS_LENGTH)
  //   .sort((a, b) => b.itemID - a.itemID)

  const storeItems = items


  // const firstVisibleItem = useMemo(
  //   () => Math.ceil(scrollLeft / ITEM_WIDTH),
  //   [scrollLeft]
  // )

  // const onDebouncedScroll = useDebouncedCallback(
  //   e => setScrollLeft(e.target.scrollLeft),
  //   200
  // )

  // const onDebouncedWindowResize = useDebouncedCallback(
  //   () => setOffsetWidth(listRef.current?.offsetWidth || 0),
  //   200
  // )

  // useLayoutEffect(() => {
  //   onDebouncedWindowResize()
  // }, [onDebouncedWindowResize])

  // useLayoutEffect(() => {
  //   setReachedScrollEnd(
  //     listRef.current
  //       ? scrollLeft + offsetWidth >= listRef.current.scrollWidth
  //       : false
  //   )
  // }, [offsetWidth, scrollLeft])

  // useLayoutEffect(() => {
  //   window.addEventListener("resize", onDebouncedWindowResize)
  //   return () => {
  //     window.removeEventListener("resize", onDebouncedWindowResize)
  //   }
  // }, [onDebouncedWindowResize])

  console.log("storeItems:" + JSON.stringify(storeItems))
  if (storeItems && storeItems.length === 0) return null

  return (
    <>
      {/* <div className="main-container flex pt-10 flex-col sm:flex-row">
        <div>
          <h1 className="text-4xl text-gray-darkest mb-1">
            Popular Merchants
          </h1>
          <div className="text-xl text-gray-light">
            Check out the latest merchants.
          </div>
        </div>
      </div> */}
      <div className="mt-8 mb-10 1xl:latest-store-list-items">
        <div
          className="overflow-x-auto pb-5"
          // onScroll={onDebouncedScroll}
          ref={listRef}
        >
          <div className="whitespace-nowrap flex -ml-2 lg:pr-3">
            {storeItems && storeItems.map(item => (
              <div
                key={item.txID}
                className="flex justify-center px-4"
                style={{minWidth: ITEM_WIDTH}}
              >
                <MerchantsListItem item={item} size="sm" isStoreItem={true} queryState={queryState} updateQuery={updateQuery}/>
              </div>
            ))}
          </div>
        </div>
      </div>
      <div className="border-t border-gray-200" />
    </>
  )
}

PopularMerchants.propTypes = {
  items: PropTypes.arrayOf(normalizedMerchantItemType),
}


