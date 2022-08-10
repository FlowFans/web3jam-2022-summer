package main

import (
	"context"
	"fmt"
	"sort"

	"github.com/onflow/cadence"
	"github.com/onflow/flow-go-sdk"
	"github.com/onflow/flow-go-sdk/client"
	"google.golang.org/grpc"
)

type Pack struct {
	id       uint64
	scarcity string
	series   string
	ipfs     string
}

func PackToNFTStoreFrontTx(extraIndex int, packid uint64, price string) {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	if err != nil {
		panic(err)
	}
	serviceAcctAddr, serviceAcctKey, serviceSigner := ServiceAccountExtraIndex(flowClient, extraIndex)
	referenceBlockID, err := flowClient.GetLatestBlock(ctx, false)
	tx := flow.NewTransaction().
		SetScript([]byte(pack_to_nftstorefront(SOULMADE_CONTRACT_ADDRESS))).
		SetGasLimit(9999).
		SetProposalKey(serviceAcctAddr, serviceAcctKey.Index, serviceAcctKey.SequenceNumber).
		SetReferenceBlockID(referenceBlockID.ID).
		SetPayer(serviceAcctAddr).
		AddAuthorizer(serviceAcctAddr)

	toNftid := cadence.NewUInt64(packid)
	toPrice, err := cadence.NewUFix64(price)
	if err != nil {
		panic(err)
	}

	if err := tx.AddArgument(toNftid); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(toPrice); err != nil {
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
	// 	fmt.Println(txStatus.Events)
	// 	fmt.Printf("keyindex %d Add Account PK transaction %s status: %s\n", extraIndex, tx.ID(), txStatus.Status)
	// }
	// ch <- extraIndex
	fmt.Printf("keyindex %d Add Account PK transaction %s \n", extraIndex, tx.ID())

}

//获取指定series的PackID
func GetPackIDBySeries(series string) []uint64 {
	pack_id := []uint64{}
	packAllIDSArray := GetPackIDSScript(SOULMADE_CONTRACT_ADDRESS)
	sort.Slice(packAllIDSArray, func(i, j int) bool { return packAllIDSArray[i] < packAllIDSArray[j] })
	packDetailArray := []Pack{}
	gap := 100
	for i := 0; i < len(packAllIDSArray)/gap; i++ {
		componentIDSArray := packAllIDSArray[i*gap : (i+1)*gap]
		packDetailArray = append(packDetailArray, GetPackDetailBatchScript(componentIDSArray, SOULMADE_CONTRACT_ADDRESS)...)
	}
	packDetailArray = append(packDetailArray, GetPackDetailBatchScript(packAllIDSArray[len(packAllIDSArray)/gap*gap:], SOULMADE_CONTRACT_ADDRESS)...)
	for _, item := range packDetailArray {
		if item.series == series {
			pack_id = append(pack_id, item.id)
		}
	}
	return pack_id
}

func Pack_To_NFTStoreFront(publicKeyNumber int, series string, number int, price string, batch int) {
	index := 0
	packid := GetPackIDBySeries(series)
	for index < number {
		for i := 0; i < batch; i++ {
			go PackToNFTStoreFrontTx((index+i)%publicKeyNumber, packid[index+i], price)
		}
		for i := 0; i < batch; i++ {
			<-ch
		}
		index = index + batch
	}
}

func MysteryBox_To_NFTStoreFront(publicKeyNumber int, series string, price string, scarity string) {
	packid := GetPackIDBySeries(series)
	sort.Slice(packid, func(i, j int) bool { return packid[i] < packid[j] })
	fmt.Println(packid[0], packid[len(packid)-1], len(packid))
	for index, item := range packid {
		PackToNFTStoreFrontTx(index%publicKeyNumber, item, price)
		fmt.Println(index)
	}
	// Mint_Pack_Tx(index%publicKeyNumber, "", series, "", []uint64{}, item)
}
