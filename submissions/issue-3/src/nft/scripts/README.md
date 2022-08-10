# Token

## Get Metadata from CaaArts

```sh
flow scripts execute ./nft/scripts/getCaaArtMetadata.cdc 0x56ac261eb0f67cf4 \
  --network testnet
```

## Get All Metadata from CaaArts

```sh
flow scripts execute ./nft/scripts/getAllCaaArtsMetadata.cdc 0xdd718b0856a69974 \
  --network mainnet
```

## Get All Metadata from CaaPass

```sh
flow scripts execute ./nft/scripts/getAllCaaPassMetadata.cdc 0xdd718b0856a69974 \
  --network mainnet
```

## Get Metadata for a typeID from CaaPass

```sh
flow scripts execute ./nft/scripts/getCaaPassTypeMetadata.cdc 0 \
  --network mainnet
```
