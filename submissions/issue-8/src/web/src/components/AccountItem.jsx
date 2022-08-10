import PropTypes from "prop-types"
import useAccountItem from "src/hooks/useAccountItem"
import ListItem from "./ListItem"

export default function AccountItem({address, id, showOwnerInfo}) {
  const {item, isLoading} = useAccountItem(address, id)
  if (isLoading || !item) return null
  console.log("item222:" + JSON.stringify(item))
  return <ListItem item={item} showOwnerInfo={showOwnerInfo} />
}

AccountItem.propTypes = {
  address: PropTypes.string.isRequired,
  id: PropTypes.number.isRequired,
  showOwnerInfo: PropTypes.bool,
}
