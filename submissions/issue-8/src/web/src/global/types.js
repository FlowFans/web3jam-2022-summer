import PropTypes from "prop-types"

export const normalizedItemType = PropTypes.exact({
  id: PropTypes.number.isRequired,
  owner: PropTypes.string.isRequired,
  name: PropTypes.string,
  badge_image: PropTypes.string,
  listingResourceID: PropTypes.number,
  price: PropTypes.string,
  txID: PropTypes.string,
})

export const apiListingType = PropTypes.exact({
  id: PropTypes.number.isRequired,
  creator: PropTypes.string.isRequired,
  listingResourceID: PropTypes.number.isRequired,
  owner: PropTypes.string.isRequired,
  price: PropTypes.string,
  name: PropTypes.string,
  badge_image: PropTypes.string,
  txID: PropTypes.string,
})


export const normalizedMerchantItemType = PropTypes.exact({
  name: PropTypes.string.isRequired,
  image: PropTypes.string,
  address: PropTypes.string.isRequired,
  txID: PropTypes.string.isRequired,
})

export const normalizedBadges = PropTypes.exact({
  id: PropTypes.number.isRequired,
  owner: PropTypes.string.isRequired,
  name: PropTypes.string,
  badge_image: PropTypes.string,
  number: PropTypes.number,
  max: PropTypes.number,
  txID: PropTypes.string,
})