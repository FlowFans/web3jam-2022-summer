const ethers = require("ethers");
const { WAKANDAPASS_ADDRESS } = require("../constant/address");
const SupportedChainId = require("../constant/chains");
const wakandapass_abi = require("../abis/wakandapass.json");
const { PolygonProvider } = require("../libs/web3");
const { isAddress } = require("../utils/address");

const wallets = ['0xB964b01281DF695Db1679bE4365Bc6afB7361CbF']

const main = async () => {
	const polygonPassContract = new ethers.Contract(WAKANDAPASS_ADDRESS[SupportedChainId.POLYGON], wakandapass_abi, PolygonProvider)
	let balanceOfPolygon = 0;
	let tokenURIOfPolygon = [];
	for (const addr of wallets.filter(address => isAddress(address))) {
		// query balance of address
		const [polygonBalance] = await Promise.all([
			polygonPassContract.balanceOf(addr),
		]);
		if (polygonBalance.toNumber() > 0) {
			balanceOfPolygon += polygonBalance.toNumber();
			for (let i = 0; i < polygonBalance.toNumber(); i++) {
				const tokenId = await polygonPassContract.tokenOfOwnerByIndex(addr, i)
				console.log(tokenId.toString())
			}
		}
	}
	console.log(balanceOfPolygon)
	console.log(tokenURIOfPolygon)
}

main()