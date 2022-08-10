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

func Get_NFTStoreFront_ListingIDs(checkAddr string, contractAddr string) string {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())

	address := cadence.NewAddress(flow.HexToAddress(checkAddr))
	args := []cadence.Value{address}
	value, err := flowClient.ExecuteScriptAtLatestBlock(ctx, getNFTStoreFrontListringResourceID(contractAddr), args)
	if err != nil {
		panic(err)
	}
	return value.String()
}

func GetNFTStoreFrontPackOnSellListingIDs(checkAddr string, contractAddr string) []uint64 {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())

	address := cadence.NewAddress(flow.HexToAddress(checkAddr))
	args := []cadence.Value{address}
	value, err := flowClient.ExecuteScriptAtLatestBlock(ctx, getPackSellListringIDS(contractAddr), args)
	if err != nil {
		panic(err)
	}
	nftstoreFrontListingResourceID := value.String()

	nftstoreFrontListingResourceID = strings.Replace(nftstoreFrontListingResourceID, "[", "", -1)
	nftstoreFrontListingResourceID = strings.Replace(nftstoreFrontListingResourceID, "]", "", -1)
	nftstoreFrontListingResourceID = strings.Replace(nftstoreFrontListingResourceID, " ", "", -1)
	nftstoreFrontListingResourceIDArray := String2Uint64Array(strings.Split(nftstoreFrontListingResourceID, ","))

	return nftstoreFrontListingResourceIDArray

}

func GetListringPackSeriesDetailByListringID(checkAddr string, contractAddr string, listingID uint64) cadence.Value {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	checklistingID := cadence.NewUInt64(listingID)
	args := []cadence.Value{checklistingID}
	value, err := flowClient.ExecuteScriptAtLatestBlock(ctx, getListPackDetailOnSale(contractAddr), args)
	if err != nil {
		panic(err)
	}
	return value
}

func GetListringDetailsByListringID(checkAddr string, contractAddr string, listingID uint64) cadence.Value {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	address := cadence.NewAddress(flow.HexToAddress(SOULMADE_CONTRACT_ADDRESS))
	args := []cadence.Value{address, cadence.NewUInt64(listingID)}
	fmt.Println(args)
	value, err := flowClient.ExecuteScriptAtLatestBlock(ctx, getListingDetailByListringID(), args)
	if err != nil {
		panic(err)
	}
	return value
}

func UnSellNFTStoreFrontListingTx(extraIndex int, listingID uint64) {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	if err != nil {
		panic(err)
	}
	serviceAcctAddr, serviceAcctKey, serviceSigner := ServiceAccountExtraIndex(flowClient, extraIndex)
	referenceBlockID, err := flowClient.GetLatestBlock(ctx, false)
	tx := flow.NewTransaction().
		SetScript([]byte(unsell_nftStoreFront())).
		SetGasLimit(9999).
		SetProposalKey(serviceAcctAddr, serviceAcctKey.Index, serviceAcctKey.SequenceNumber).
		SetReferenceBlockID(referenceBlockID.ID).
		SetPayer(serviceAcctAddr).
		AddAuthorizer(serviceAcctAddr)

	listringresourceid := cadence.NewUInt64(listingID)
	if err := tx.AddArgument(listringresourceid); err != nil {
		panic(err)
	}
	if err := tx.SignEnvelope(serviceAcctAddr, serviceAcctKey.Index, serviceSigner); err != nil {
		panic(err)
	}
	if err := flowClient.SendTransaction(ctx, *tx); err != nil {
		panic(err)
	}
	// deposit_body_to_main_tx := WaitForSeal(ctx, flowClient, tx.ID())
	// if deposit_body_to_main_tx.Error != nil {
	// 	fmt.Println("Error 🚩", extraIndex, tx.ID())
	// } else {
	// 	fmt.Println("KeyIndex", extraIndex, "Key Sequence", serviceAcctKey.SequenceNumber, "Tx ID", tx.ID().String())
	// }
	// ch <- extraIndex
	fmt.Println("KeyIndex", extraIndex, "Key Sequence", serviceAcctKey.SequenceNumber, "Tx ID", tx.ID().String())
}

func UnSell_NFTStoreFront(publicKeyNumber int) {
	nftstoreFrontListingResourceID := Get_NFTStoreFront_ListingIDs(SOULMADE_CONTRACT_ADDRESS, SOULMADE_CONTRACT_ADDRESS)
	nftstoreFrontListingResourceID = strings.Replace(nftstoreFrontListingResourceID, "[", "", -1)
	nftstoreFrontListingResourceID = strings.Replace(nftstoreFrontListingResourceID, "]", "", -1)
	nftstoreFrontListingResourceID = strings.Replace(nftstoreFrontListingResourceID, " ", "", -1)
	nftstoreFrontListingResourceIDArray := String2Uint64Array(strings.Split(nftstoreFrontListingResourceID, ","))
	index := 0
	gap := 50
	for index < len(nftstoreFrontListingResourceIDArray) {
		for i := 0; i < gap; i++ {
			go UnSellNFTStoreFrontListingTx((index+i)%publicKeyNumber, nftstoreFrontListingResourceIDArray[index+i])
		}
		for i := 0; i < gap; i++ {
			<-ch
		}
		index += gap
	}
}

func UnSell_NFTStoreFront_Series(publicKeyNumber int, seriesneed string, series2 string, series3 string) {
	nftstoreFrontListingResourceIDArray := GetNFTStoreFrontPackOnSellListingIDs(SOULMADE_CONTRACT_ADDRESS, SOULMADE_CONTRACT_ADDRESS)
	count := 0
	for _, listingID := range nftstoreFrontListingResourceIDArray {
		detail := GetListringPackSeriesDetailByListringID(SOULMADE_CONTRACT_ADDRESS, SOULMADE_CONTRACT_ADDRESS, listingID)
		series := detail.(cadence.Struct).Fields[2].ToGoValue().(string)
		if series == seriesneed || series == series2 || series == series3 {
			UnSellNFTStoreFrontListingTx(count%publicKeyNumber, listingID)
			count += 1

		}
	}
}

func CancelSellNFTStoreFrontBySeries(pkNum int, series string) {
	nftstoreFrontListingResourceIDArray := GetNFTStoreFrontPackOnSellListingIDs(SOULMADE_CONTRACT_ADDRESS, SOULMADE_CONTRACT_ADDRESS)
	fmt.Println(len(nftstoreFrontListingResourceIDArray))
	count := 0

	for _, listingID := range nftstoreFrontListingResourceIDArray {
		detail := GetListringPackSeriesDetailByListringID(SOULMADE_CONTRACT_ADDRESS, SOULMADE_CONTRACT_ADDRESS, listingID)
		series_item := detail.(cadence.Struct).Fields[2].ToGoValue().(string)
		if series_item == series {
			fmt.Println("Cancel Sell NFT Store Front Listing ID", listingID, count)
			// UnSellNFTStoreFrontListingTx(count%pkNum, listingID)
			count += 1
		}
	}
}
