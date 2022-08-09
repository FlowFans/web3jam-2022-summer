const { ethers } = require("ethers");
const INFURA_NETWORK_URLS = require("../constant/infura");
const SupportedChainId = require("../constant/chains");
const dotenv = require("dotenv");
dotenv.config();

const mnemonic = process.env.MNEMONIC

const walletMnemonic = ethers.Wallet.fromMnemonic(mnemonic)

const PolygonProvider = new ethers.providers.JsonRpcProvider(INFURA_NETWORK_URLS[SupportedChainId.POLYGON]);

const PolygonProviderWithSinger = walletMnemonic.connect(PolygonProvider)

module.exports = {
	PolygonProvider,
	PolygonProviderWithSinger,
}

