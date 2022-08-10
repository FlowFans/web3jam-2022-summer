package main

import (
	"context"
	"fmt"
	"strings"

	"github.com/onflow/cadence"
	"github.com/onflow/flow-go-sdk"
	"github.com/onflow/flow-go-sdk/client"
	"google.golang.org/grpc"
)

func batchCancelSell(extraIndex int, nftIDS []uint64, nfttype string) {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	if err != nil {
		panic(err)
	}
	serviceAcctAddr, serviceAcctKey, serviceSigner := ServiceAccountExtraIndex(flowClient, extraIndex)
	referenceBlockID, err := flowClient.GetLatestBlock(ctx, false)
	tx := flow.NewTransaction().
		SetScript([]byte(batchCancelSellTransaction(SOULMADE_CONTRACT_ADDRESS))).
		SetGasLimit(9999).
		SetProposalKey(serviceAcctAddr, serviceAcctKey.Index, serviceAcctKey.SequenceNumber).
		SetReferenceBlockID(referenceBlockID.ID).
		SetPayer(serviceAcctAddr).
		AddAuthorizer(serviceAcctAddr)

	argNFTIDS := []cadence.Value{}
	for _, item := range nftIDS {
		argNFTIDS = append(argNFTIDS, cadence.NewUInt64(item))
	}
	if err := tx.AddArgument(cadence.NewArray(argNFTIDS)); err != nil {
		panic(err)
	}

	nftType, err := cadence.NewString(nfttype)
	if err != nil {
		panic(err)
	}
	if err := tx.AddArgument(nftType); err != nil {
		panic(err)
	}
	if err := tx.SignEnvelope(serviceAcctAddr, serviceAcctKey.Index, serviceSigner); err != nil {
		panic(err)
	}
	if err := flowClient.SendTransaction(ctx, *tx); err != nil {
		panic(err)
	}
	fmt.Println("KeyIndex", extraIndex, "Key Sequence", serviceAcctKey.SequenceNumber, "Tx ID", tx.ID().String())
}

func BatchCancelSellingComponent() {
	componentAllIDS := GetSellingComponentID(SOULMADE_CONTRACT_ADDRESS, SOULMADE_CONTRACT_ADDRESS)
	componentAllIDS = strings.Replace(componentAllIDS, "[", "", -1)
	componentAllIDS = strings.Replace(componentAllIDS, "]", "", -1)
	componentAllIDS = strings.Replace(componentAllIDS, " ", "", -1)
	componentAllIDSArray := String2Uint64Array(strings.Split(componentAllIDS, ","))
	batchCancelCount := 100
	publicKeyNumber := 180
	for i := 0; i < len(componentAllIDSArray)/batchCancelCount; i++ {
		componentIDSArray := componentAllIDSArray[i*batchCancelCount : (i+1)*batchCancelCount]
		batchCancelSell(i%publicKeyNumber, componentIDSArray, "SoulMadeComponent")
		//WriteSellingComponentDetailArrayToSQL(componentIDSArray, db)
		fmt.Println(i*batchCancelCount, (i+1)*batchCancelCount)
	}
	batchCancelSell(181, componentAllIDSArray[len(componentAllIDSArray)/batchCancelCount*batchCancelCount:], "SoulMadeComponent")
}

func BatchCancelSellingMain() {
	mainAllIDS := GetSellingMainID(SOULMADE_CONTRACT_ADDRESS, SOULMADE_CONTRACT_ADDRESS)
	mainAllIDS = strings.Replace(mainAllIDS, "[", "", -1)
	mainAllIDS = strings.Replace(mainAllIDS, "]", "", -1)
	mainAllIDS = strings.Replace(mainAllIDS, " ", "", -1)
	mainAllIDSArray := String2Uint64Array(strings.Split(mainAllIDS, ","))
	fmt.Println(len(mainAllIDSArray))
	batchCancelCount := 10
	publicKeyNumber := 180
	for i := 0; i < len(mainAllIDSArray)/batchCancelCount; i++ {
		componentIDSArray := mainAllIDSArray[i*batchCancelCount : (i+1)*batchCancelCount]
		batchCancelSell(i%publicKeyNumber, componentIDSArray, "SoulMadeMain")
	}
	batchCancelSell(181, mainAllIDSArray[len(mainAllIDSArray)/batchCancelCount*batchCancelCount:], "SoulMadeMain")
}

// func main() {
// 	BatchCancelSellingComponent()
// 	// componentAllIDS := GetSellingComponentID()
// 	// componentAllIDS = strings.Replace(componentAllIDS, "[", "", -1)
// 	// componentAllIDS = strings.Replace(componentAllIDS, "]", "", -1)
// 	// componentAllIDS = strings.Replace(componentAllIDS, " ", "", -1)
// 	// componentAllIDSArray := String2Uint64Array(strings.Split(componentAllIDS, ","))
// 	// fmt.Println(len(componentAllIDSArray))
// }
