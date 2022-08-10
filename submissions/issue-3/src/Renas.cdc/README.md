# CAA-NFT

NFT for Chinese Academy of Art

## How to use SwapTrader（Testnet）

### Register SwapPair

```sh
flow transactions send ./nft/transactions/swapTrader_registerSwapPair.cdc 0 "[[0,10,1],[10,20,1]]" "[[20,30,1]]" \
  --network testnet \
  --signer admin-testnet
```

```sh
flow transactions send ./nft/transactions/swapTrader_registerSwapPair.cdc 0 "[[0,5000,1],[5000,10000,1],[10000,15000,1],[15000,20000,1],[20000,25000,1],[25000,28000,1],[28000,31000,1],[31000,34000,1],[34000,37000,1]]" "[[37000,40000,1]]" \
  --network mainnet \
  --gas-limit 9999 \
  --signer admin-mainnet-local \
  -f flow.json -f flow.mainnet.json
```

Parameters:

1. PairID
2. Source array of CaaPass:
   1. MinID
   2. MaxID
   3. Amount
3. Target array of CaaPass:
   1. MinID
   2. MaxID
   3. Amount

### Execute Swap

```sh
flow transactions send ./nft/transactions/swapTrader_swapNFT.cdc 0xf8d6....20c7 0 "[0, 10]" \
  --network testnet \
  --signer user-testnet
```

Parameters:

1. Contract Address
2. PairID
3. Array of CaaPass IDs

### Query SwapPair info

```sh
flow scripts execute ./nft/scripts/swapTrader_getSwapPair.cdc 0xf8d6....20c7 0 \
  --network testnet
```

Parameters:

1. Contract Address
2. PairID

### Query SwapPair swapped times

```sh
flow scripts execute ./nft/scripts/swapTrader_getSwappedAmount.cdc 0xf8d6....20c7 0 \
  --network testnet
```

Parameters:

1. Contract Address
2. PairID

### Query SwapPair how many pairs remained

```sh
flow scripts execute ./nft/scripts/swapTrader_getTradableAmount.cdc 0xf8d6....20c7 0 \
  --network testnet
```

Parameters:

1. Contract Address
2. PairID

### Other

- Change SwapPair state: `./nft/transactions/swapTrader_setSwapPairState.cdc`
- Check if tradable `./nft/scripts/swapTrader_isTradable.cdc`
