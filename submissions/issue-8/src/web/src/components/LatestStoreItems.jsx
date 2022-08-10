import PropTypes from "prop-types"
import {useLayoutEffect, useMemo, useRef, useState} from "react"
import ListItem from "src/components/ListItem"
import {storeItemsSelector} from "src/global/selectors"
import {normalizedBadges} from "src/global/types"
import {useDebouncedCallback} from "use-debounce"

const ITEMS_LENGTH = 10
const ITEM_WIDTH = 432

const PageButton = ({onClick, disabled, children}) => (
  <button
    onClick={onClick}
    disabled={disabled}
    className="rounded-full border border-gray-200 h-14 w-14 flex items-center justify-center bg-white hover:opacity-80 disabled:opacity-50 disabled:cursor-default"
  >
    {children}``
  </button>
)

export default function LatestStoreItems({items}) {
  const listRef = useRef()
  const [reachedScrollEnd, setReachedScrollEnd] = useState(false)
  const [offsetWidth, setOffsetWidth] = useState(0)
  const [scrollLeft, setScrollLeft] = useState(0)
  // const storeItems = storeItemsSelector(items)
  //   .slice(0, ITEMS_LENGTH)
  //   .sort((a, b) => b.itemID - a.itemID)

  const storeItems = items

  console.log("storeItems:" + JSON.stringify(items))

  const firstVisibleItem = useMemo(
    () => Math.ceil(scrollLeft / ITEM_WIDTH),
    [scrollLeft]
  )

  const onDebouncedScroll = useDebouncedCallback(
    e => setScrollLeft(e.target.scrollLeft),
    200
  )

  const onDebouncedWindowResize = useDebouncedCallback(
    () => setOffsetWidth(listRef.current?.offsetWidth || 0),
    200
  )

  useLayoutEffect(() => {
    onDebouncedWindowResize()
  }, [onDebouncedWindowResize])

  useLayoutEffect(() => {
    setReachedScrollEnd(
      listRef.current
        ? scrollLeft + offsetWidth >= listRef.current.scrollWidth
        : false
    )
  }, [offsetWidth, scrollLeft])

  useLayoutEffect(() => {
    window.addEventListener("resize", onDebouncedWindowResize)
    return () => {
      window.removeEventListener("resize", onDebouncedWindowResize)
    }
  }, [onDebouncedWindowResize])

  if (storeItems && storeItems.length === 0) return null

  const scrollToItem = index =>
    listRef.current?.scrollTo({
      top: 0,
      left: index * ITEM_WIDTH,
      behavior: "smooth",
    })

  const prevPage = () => scrollToItem(firstVisibleItem - 1)
  const nextPage = () => scrollToItem(firstVisibleItem + 1)

  return (
    <>
      <div className="main-container flex pt-10 flex-col sm:flex-row">
        <div>
          <h1 className="text-4xl text-gray-darkest mb-1">
            Latest Badges
          </h1>
          <div className="text-xl text-gray-light">
            Check out the latest freshly-minted Badges.
          </div>
        </div>
        {storeItems && storeItems.length > 2 && (
          <div className="flex mt-6 sm:mt-0 sm:ml-auto">
            <div className="mr-5">
              <PageButton onClick={prevPage} disabled={scrollLeft === 0}>
                <img
                  src="/images/arrow-left.svg"
                  alt="Previous Page"
                  width="16"
                  height="16"
                />
              </PageButton>
            </div>
            <PageButton onClick={nextPage} disabled={reachedScrollEnd}>
              <img
                src="/images/arrow-right.svg"
                alt="Next Page"
                width="16"
                height="16"
              />
            </PageButton>
          </div>
        )}
      </div>
      <div className="mt-8 mb-10 2xl:latest-store-list-items">
        <div
          className="overflow-x-auto pb-5"
          onScroll={onDebouncedScroll}
          ref={listRef}
        >
          <div className="whitespace-nowrap flex -ml-2 lg:pr-3">
            {storeItems && storeItems.map(item => (
              <div
                key={item.id}
                className="flex justify-center px-4"
                style={{minWidth: ITEM_WIDTH}}
              >
                <ListItem item={item} size="sm" isStoreItem={true} />
              </div>
            ))}
          </div>
        </div>
      </div>
      <div className="border-t border-gray-200" />
    </>
  )
}

LatestStoreItems.propTypes = {
  // items: PropTypes.arrayOf(normalizedBadges),
}

PageButton.propTypes = {
  onClick: PropTypes.func.isRequired,
  disabled: PropTypes.bool,
  children: PropTypes.node.isRequired,
}
