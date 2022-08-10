package main

import (
	"context"
	"fmt"

	"github.com/onflow/cadence"
	"github.com/onflow/flow-go-sdk"
	"github.com/onflow/flow-go-sdk/client"
	"google.golang.org/grpc"
)

func TransferNFTTx(extraIndex int, nftids []uint64, to string, nftType string) {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	if err != nil {
		panic(err)
	}
	scriptbyte := []byte{}
	serviceAcctAddr, serviceAcctKey, serviceSigner := ServiceAccountExtraIndex(flowClient, extraIndex)
	referenceBlockID, err := flowClient.GetLatestBlock(ctx, false)
	if nftType == "SoulMadeMain" {
		scriptbyte = []byte(transfer_main(SOULMADE_CONTRACT_ADDRESS))
	} else {
		scriptbyte = []byte(transfer_component(SOULMADE_CONTRACT_ADDRESS))
	}

	tx := flow.NewTransaction().
		SetScript(scriptbyte).
		SetGasLimit(9999).
		SetProposalKey(serviceAcctAddr, serviceAcctKey.Index, serviceAcctKey.SequenceNumber).
		SetReferenceBlockID(referenceBlockID.ID).
		SetPayer(serviceAcctAddr).
		AddAuthorizer(serviceAcctAddr)

	arg := []cadence.Value{}
	for _, item := range nftids {
		arg = append(arg, cadence.NewUInt64(item))
	}

	toAddr := cadence.Address(flow.HexToAddress(to))

	if err := tx.AddArgument(cadence.NewArray(arg)); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(toAddr); err != nil {
		panic(err)
	}

	if err := tx.SignEnvelope(serviceAcctAddr, serviceAcctKey.Index, serviceSigner); err != nil {
		panic(err)
	}
	if err := flowClient.SendTransaction(ctx, *tx); err != nil {
		panic(err)
	}
	fmt.Printf("keyindex %d Add Account PK transaction %s \n", extraIndex, tx.ID())
}

// ipfs: the ipfs of want to transfer the components
// to : destination address
// amount: the amount of components want to transfer
func TranferNftByIpfs(count int, ipfs string, to string, amount int, nftType string) {
	switch nftType {
	case "SoulMadeMain":
		ids, _ := GetIPFSCMainIDArrayAndDetail(ipfs, SOULMADE_CONTRACT_ADDRESS)
		if len(ids) >= amount {
			for i := 0; i < amount; i++ {
				TransferNFTTx(count, ids[:amount], to, nftType)
			}
		}
	default:
		ids, _ := GetIPFSComponentIDArrayAndDetail(ipfs, SOULMADE_CONTRACT_ADDRESS)
		if len(ids) >= amount {
			for i := 0; i < amount; i++ {
				TransferNFTTx(count, ids[:amount], to, nftType)
			}
		}
	}
}

func MultiPeopleTranferIPFSNFT(publicKeyNumber int, ipfs string, tos []string, amounts []int) {
	ids, _ := GetIPFSComponentIDArrayAndDetail(ipfs, SOULMADE_CONTRACT_ADDRESS)
	start := 0
	end := 0
	for j := 0; j < len(tos); j++ {
		end = end + amounts[j]
		TransferNFTTx(j%publicKeyNumber, ids[start:end], tos[j], "SoulMadeComponent")
		start = end
	}
}
