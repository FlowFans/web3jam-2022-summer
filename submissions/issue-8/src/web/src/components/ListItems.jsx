import PropTypes from "prop-types"
import {normalizedBadges} from "src/global/types"
import AccountItem from "./AccountItem"
import EmptyKittyItems from "./EmptyKittyItems"
import ListItem from "./ListItem"

export const listItemsRootClasses =
  "grid gap-y-16 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 sm:gap-x-5 minh-50"
const listItemsStyle = {
  minHeight: 520,
}

export default function ListItems({items = [], accountItemIds = []}) {
  if (items.length === 0 && accountItemIds.length === 0)
    return <EmptyKittyItems />

  console.log("items:" + JSON.stringify(items))
  console.log("accountItemIds:" + JSON.stringify(accountItemIds))

  return (
      <div className={listItemsRootClasses} style={listItemsStyle}>
        {items.map(item => (
          <ListItem
            key={`${item.itemID}-${item.listingResourceID}`}
            item={item}
            showOwnerInfo={true}
          />
        ))}
        {accountItemIds.map(item => (
          <AccountItem
            key={item.id}
            id={item.id}
            address={item.owner}
            showOwnerInfo={true}
          />
        ))}
      </div>
  )
}

ListItems.propTypes = {
  items: PropTypes.arrayOf(normalizedBadges),
  accountItemIds: PropTypes.array,
}
