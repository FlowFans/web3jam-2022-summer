package main

import (
	"context"
	"fmt"

	"github.com/onflow/cadence"
	"github.com/onflow/flow-go-sdk"
	"github.com/onflow/flow-go-sdk/client"
	"google.golang.org/grpc"
)

func Open_Pack_Tx(extraIndex int, packid uint64) {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	if err != nil {
		panic(err)
	}
	serviceAcctAddr, serviceAcctKey, serviceSigner := ServiceAccountExtraIndex(flowClient, extraIndex)
	referenceBlockID, err := flowClient.GetLatestBlock(ctx, false)
	tx := flow.NewTransaction().
		SetScript([]byte(open_pack(SOULMADE_CONTRACT_ADDRESS))).
		SetGasLimit(9999).
		SetProposalKey(serviceAcctAddr, serviceAcctKey.Index, serviceAcctKey.SequenceNumber).
		SetReferenceBlockID(referenceBlockID.ID).
		SetPayer(serviceAcctAddr).
		AddAuthorizer(serviceAcctAddr)

	id := cadence.NewUInt64(packid)
	if err := tx.AddArgument(id); err != nil {
		panic(err)
	}
	if err := tx.SignEnvelope(serviceAcctAddr, serviceAcctKey.Index, serviceSigner); err != nil {
		panic(err)
	}
	if err := flowClient.SendTransaction(ctx, *tx); err != nil {
		panic(err)
	}
	// txStatus := WaitForSeal(ctx, flowClient, tx.ID())
	// if txStatus.Error != nil {
	// 	fmt.Println("Err2🚩", tx.ID(), txStatus.Error)
	// } else {
	// 	fmt.Printf("keyindex %d Add Account PK transaction %s status: %s\n", extraIndex, tx.ID(), txStatus.Status)
	// }
	// ch <- extraIndex
	fmt.Printf("keyindex %d Add Account PK transaction %s\n", extraIndex, tx.ID())
}

func OpenPackForSeries(pkNumber int, series string) {
	packAllIDSArray := GetPackIDBySeries(series)
	fmt.Println(packAllIDSArray, len(packAllIDSArray))
	index := 0
	for index < len(packAllIDSArray) {
		Open_Pack_Tx(index%pkNumber, packAllIDSArray[index])
		index += 1
	}

}
