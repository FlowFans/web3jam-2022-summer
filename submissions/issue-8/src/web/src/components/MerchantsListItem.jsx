import Link from "next/link"
import PropTypes from "prop-types"
import {paths} from "src/global/constants"
import {normalizedMerchantItemType} from "src/global/types"
import useAppContext from "src/hooks/useAppContext"
import {rarityTextColors} from "src/util/classes"
import ListItemImage from "./ListItemImage"
import ListItemPrice from "./ListItemPrice"
import OwnerInfo from "./OwnerInfo"

export default function MerchantsListItem({
  item,
  showOwnerInfo,
  size = "sm",
  isStoreItem,
  queryState,
  updateQuery,
}) {
  const {currentUser} = useAppContext()
  const currentUserIsOwner = currentUser && item.owner === currentUser?.addr
  const hasListing = Number.isInteger(item.listingResourceID)
  const profileUrl = paths.profileItem(item.owner, item.itemID)
  const updateFilter = () => updateQuery({page: 1, creator: item.address})
  // const rarityTextColor = rarityTextColors(item.rarity)
  console.log("owner" + item.address)
  return (
    <div className="w-full">
      {/* <Link href={profileUrl} passHref> */}
        <a className="w-full" onClick={updateFilter}>
          <ListItemImage
            name={item.name}
            // rarity={item.rarity}
            cid={item.image}
            address={item.address}
            // id={item.txID}
            size={size}
            isStoreItem={isStoreItem}
            classes="item-image-container-hover"
          >
            {/* {isStoreItem && (
              <div className="absolute top-3 left-3">
                <div
                  className={`bg-white py-1 px-4 font-bold text-sm rounded-full uppercase ${rarityTextColor}`}
                >
                  New
                </div>
              </div>
            )} */}
          </ListItemImage>
        </a>
      {/* </Link> */}
      <div>
        {showOwnerInfo && <OwnerInfo address={item.address} />}
        <div className="flex justify-between items-center mt-5 gap-4 link">
          {/* <div className="flex flex-col link"> */}
            {/* <Link> */}
              <a className="text-lg items-center font-semibold center w-full" onClick={updateFilter}>{item.name}</a>
            {/* </Link> */}
          {/* </div> */}
        </div>
      </div>
    </div>
  )
}

MerchantsListItem.propTypes = {
  item: normalizedMerchantItemType,
  showOwnerInfo: PropTypes.bool,
  size: PropTypes.string,
  isStoreItem: PropTypes.bool,
  updateQuery: PropTypes.func.isRequired,
}
