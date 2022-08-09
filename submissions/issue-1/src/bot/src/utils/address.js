const { getAddress } = require('@ethersproject/address');

function isAddress(value) {
	try {
		return getAddress(value)
	} catch {
		return false
	}
}

function shortenAddress(address, chars = 4) {
	const parsed = isAddress(address)
	if (!parsed) {
		return `Invalid 'address'`
	}
	return `${parsed.substring(0, chars + 2)}...${parsed.substring(42 - chars)}`
}

module.exports = {
	isAddress,
	shortenAddress,
}
