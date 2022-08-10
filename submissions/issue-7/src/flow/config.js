import { config } from "@onflow/fcl";

config({
    "accessNode.api": "https://rest-testnet.onflow.org", // Mainnet: "https://rest-mainnet.onflow.org"
    "discovery.wallet": "https://fcl-discovery.onflow.org/testnet/authn", // Mainnet: "https://fcl-discovery.onflow.org/authn"
    "app.detail.icon": "https://images-platform.99static.com/IW-uUVk8SlQopEL8F713Q3TAlXc=/0x0:1000x1000/500x500/top/smart/99designs-contests-attachments/77/77945/attachment_77945946",
    "app.detail.title": "TIK8", // app name
    "0xProfile": "0xba1132bc08f82fe2",
    "contracts": {
        "NonFungibleToken": "./cadence/contract/NonFungibleToken.cdc",
        "FungibleToken": "./cadence/contract/FungibleToken.cdc",
        "NebulaToken": "./cadence/contract/NebulaActivity.cdc"
    },
    "deployments": {
        "testnet": {
            "acct": ["NonFungibleToken", "FungibleToken", "NebulaActivity"]
        }
    }
})