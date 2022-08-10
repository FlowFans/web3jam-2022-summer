package main

import (
	"context"
	"fmt"
	"os"
	"strconv"
	"strings"

	"github.com/onflow/cadence"
	"github.com/onflow/flow-go-sdk"
	"github.com/onflow/flow-go-sdk/client"
	"google.golang.org/grpc"
)

func pluspriceToIPFS(file string) {
	ipfsHashData := readCsvFile("Resource/itemIDIPFS.txt")
	var price string
	kikoNew := readCsvFile("Resource/KikoNew.csv")

	f, err := os.Create(file)
	if err != nil {
		panic(err)
	}
	defer f.Close()
	for _, item := range ipfsHashData {
		for _, kikoitem := range kikoNew {
			if strings.Split(item[0], "--")[1] == kikoitem[6] {
				price = kikoitem[len(kikoitem)-2]
			}
		}
		_, err := f.WriteString(item[0] + "--" + price + "\n")
		if err != nil {
			panic(err)
		}
	}
}

func Sell(nftid uint64, price string, extraIndex int) {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	if err != nil {
		panic(err)
	}
	serviceAcctAddr, serviceAcctKey, serviceSigner := ServiceAccountExtraIndex(flowClient, extraIndex)
	referenceBlockID, err := flowClient.GetLatestBlock(ctx, false)
	tx := flow.NewTransaction().
		SetScript([]byte(batchSellTransaction(SOULMADE_CONTRACT_ADDRESS))).
		SetGasLimit(9999).
		SetProposalKey(serviceAcctAddr, serviceAcctKey.Index, serviceAcctKey.SequenceNumber).
		SetReferenceBlockID(referenceBlockID.ID).
		SetPayer(serviceAcctAddr).
		AddAuthorizer(serviceAcctAddr)
	//Start NFT ID
	toNftid := cadence.NewUInt64(nftid)
	if err := tx.AddArgument(toNftid); err != nil {
		panic(err)
	}
	//Price
	toPrice, err := cadence.NewUFix64(price)
	if err != nil {
		panic(err)
	}
	if err := tx.AddArgument(toPrice); err != nil {
		panic(err)
	}
	//Limit , default 100
	toLimit := cadence.NewUInt64(100)
	if err := tx.AddArgument(toLimit); err != nil {
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

func BatchSell() {
	ipfsAndPrice := ReadCsvFile("Resource/itemIDIPFSPlustPrice.txt")
	var count int
	for index, item := range ipfsAndPrice {
		items := strings.Split(item[0], "--")
		//价格不为空的前提下
		if items[2] != "" {
			nftid, err := strconv.Atoi(items[0])
			if err != nil {
				panic(err)
			}
			//防止为整数的情况
			price := items[2]
			if len(price) == 1 {
				price = price + ".0"
			}
			count += 1
			fmt.Println(uint64(nftid), price, index%180)
			//Sell(uint64(nftid), price, index%180)
		}
	}
	fmt.Println(count)
}

// func main() {
// 	//pluspriceToIPFS("Resource/itemIDIPFSPlustPrice.txt")
// 	BatchSell()
// }
