const { config } = require("@onflow/fcl");

config({
	"accessNode.api": "https://rest-testnet.onflow.org", // Mainnet: https://rest-mainnet.onflow.org
	"app.detail.title": "Wakanda+",
	"app.detail.icon": "https://wakandaplus.wakanda.cn/logo512.png",
	"fcl.limit": "100",
	"discovery.wallet": "https://fcl-discovery.onflow.org/testnet/authn", // Mainnet: https://fcl-discovery.onflow.org/authn
	"flow.network": "testnet", // Mainnet: mainnet
	"discovery.authn.endpoint": "https://fcl-discovery.onflow.org/api/testnet/authn", // Mainnet: https://fcl-discovery.onflow.org/api/authn
})