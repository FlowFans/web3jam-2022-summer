# Token

## Setup CaaArts Collection

```sh
flow transactions send ./nft/transactions/setupCaaArtsCollection.cdc \
  --network testnet \
  --signer user-testnet \
  --gas-limit 1000
```

## Setup CaaPass Collection

```sh
flow transactions send ./nft/transactions/setupCaaPassCollection.cdc \
  --network testnet \
  --signer user-testnet \
  --gas-limit 1000
```

## Register Metadata

```sh
flow transactions send ./nft/transactions/registerMetadata.cdc \
  --network mainnet \
  --args-json "$(cat "./metadata.json")" \
  --signer admin-mainnet-new \
  --gas-limit 1000
```

## Mint CaaArts NFT

```sh
flow transactions send ./nft/transactions/mintCaaArts.cdc \
  --network testnet \
  --arg Address:0x56ac261eb0f67cf4 \
  --signer admin-testnet \
  --gas-limit 1000
```

## Mint CaaArts NFT in Batch

```sh
flow transactions send ./nft/transactions/mintCaaArtsBatch.cdc \
  --network mainnet \
  --args-json "$(cat "./arguments/batch.json")" \
  --signer admin-mainnet-new \
  --gas-limit 1000
```

## Mint CaaPass NFT in Batch

```sh
flow transactions send ./nft/transactions/mintCaaPassBatch.cdc \
  --network mainnet \
  --arg Address:0x98c9c2e548b84d31 \
  --arg UInt64:0 \
  --arg Int:500 \
  --signer admin-mainnet-new \
  --gas-limit 9999
```

## Transfer CaaPass in Batch

```sh
flow transactions send ./nft/transactions/transferCaaPassBatch.cdc 0x5f14b7e68e0bc3c3 4110 90 \
  --network mainnet \
  --signer admin-mainnet-1 \
  --gas-limit 1000
```

## Add New Keys

```sh
flow transactions send ./nft/transactions/addPublicKey.cdc \
  --network mainnet \
  --arg String:9e61eaca1d6e91064845d73bc6fb92aa0475aa346984f98e37c8fd36ee01c7c173ec3f871138cdc3ed2e8768ad7b12342597da1c47657e61d37980591c0ce979 \
  --signer admin-mainnet-new \
  --gas-limit 9999
```
