package main

import (
	"context"
	"fmt"

	"github.com/onflow/cadence"
	"github.com/onflow/flow-go-sdk"
	"github.com/onflow/flow-go-sdk/client"
	"google.golang.org/grpc"
)

func Sell_Single_Market_Place_Tx(extraIndex int, nftid uint64, nftprice string, nftType string) {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	if err != nil {
		panic(err)
	}
	serviceAcctAddr, serviceAcctKey, serviceSigner := ServiceAccountExtraIndex(flowClient, extraIndex)
	referenceBlockID, err := flowClient.GetLatestBlock(ctx, false)
	tx := flow.NewTransaction().
		SetScript([]byte(sell_single(SOULMADE_CONTRACT_ADDRESS))).
		SetGasLimit(9999).
		SetProposalKey(serviceAcctAddr, serviceAcctKey.Index, serviceAcctKey.SequenceNumber).
		SetReferenceBlockID(referenceBlockID.ID).
		SetPayer(serviceAcctAddr).
		AddAuthorizer(serviceAcctAddr)

	id := cadence.NewUInt64(nftid)
	price, err := cadence.NewUFix64(nftprice)
	if err != nil {
		panic(err)
	}
	addType, err := cadence.NewString(nftType)
	if err != nil {
		panic(err)
	}
	if err := tx.AddArgument(id); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(price); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(addType); err != nil {
		panic(err)
	}
	if err := tx.SignEnvelope(serviceAcctAddr, serviceAcctKey.Index, serviceSigner); err != nil {
		panic(err)
	}
	if err := flowClient.SendTransaction(ctx, *tx); err != nil {
		panic(err)
	}
	fmt.Printf("keyindex %d Add Account PK transaction %s \n", extraIndex, tx.ID())
	// txStatus := WaitForSeal(ctx, flowClient, tx.ID())
	// if txStatus.Error != nil {
	// 	fmt.Println("Err2🚩", tx.ID(), txStatus.Error)
	// } else {
	// 	fmt.Printf("keyindex %d Add Account PK transaction %s status: %s\n", extraIndex, tx.ID(), txStatus.Status)
	// }
	// ch <- extraIndex
}

func Parse_MainDetail_Get_Key(info map[string]int) []string {
	keys := []string{}
	for item := range info {
		keys = append(keys, item)
	}
	return keys
}

func Judge_Profile_Contains(info []string, str string) bool {
	for _, item := range info {
		if item == str {
			return true
		}
	}
	return false
}

//拿到150个完整的mainid, 其中有三类, 一类50个
func Get_Kiko_Sell_Main_Id() []uint64 {
	maindetails := Get_Kiko_Complete_Main()
	// fmt.Println(len(maindetails))
	profile_count := make(map[string]int)
	for _, item := range maindetails {
		profile_count[item.name] += 1
	}
	keys := []string{}
	for name, count := range profile_count {
		if count > 50 {
			keys = append(keys, name)
		}
	}
	mainids := []uint64{}
	for i := 0; i < 3; i++ {
		count := 0
		for _, item := range maindetails {
			if item.name == keys[i] {
				mainids = append(mainids, item.id)
				count += 1
			}
			if count > 49 {
				break
			}
		}
	}
	return mainids
}

//拿到150个mainid
func Get_Arilf_Sell_Main_Id() []uint64 {
	maindetails := Get_Arilf_Complete_Main()
	profile_count := make(map[string]int)
	for _, item := range maindetails {
		profile_count[item.name] += 1
	}
	keys := []string{}
	for name, count := range profile_count {
		if count > 10 {
			keys = append(keys, name)
		}
	}
	mainids := []uint64{}
	for i := 0; i < 4; i++ {
		count := 0
		for _, item := range maindetails {
			if item.name == keys[i] {
				mainids = append(mainids, item.id)
				count += 1
			}
			if count > 4 {
				break
			}
		}
	}
	return mainids
}

func Sell_Kiko_Main(publicKeyNumber int) {
	mainids := Get_Kiko_Sell_Main_Id()

	// fmt.Println(len(mainids))
	index := 0
	batch := 50
	for index < len(mainids) {
		for i := 0; i < batch; i++ {
			go Sell_Single_Market_Place_Tx((index+i)%publicKeyNumber, mainids[index+i], "26.0", "SoulMadeMain")
		}
		for i := 0; i < batch; i++ {
			<-ch
		}
		index = index + batch
	}
}

func Sell_Arilf_Main(publicKeyNumber int) {
	mainids := Get_Arilf_Sell_Main_Id()
	index := 0
	batch := 20
	for index < len(mainids) {
		for i := 0; i < batch; i++ {
			go Sell_Single_Market_Place_Tx((index+i)%publicKeyNumber, mainids[index+i], "13.0", "SoulMadeMain")
		}
		for i := 0; i < batch; i++ {
			<-ch
		}
		index = index + batch
	}
}

func Query_Component_Price_By_Ipfs(ipfs string, product_info []ProductInfo) string {
	for _, item := range product_info {
		if item.ipfs_hash == ipfs && item.price.Float64 > 0 {
			return fmt.Sprintf("%v", item.price.Float64)
		}
	}
	return "0"
}

func Get_Component_Details_Array_Plust_ID_Price(series string) ([]ComponentDetail, []uint64, []string) {
	componentAllIDSArray := GetComponentIDS(SOULMADE_CONTRACT_ADDRESS, SOULMADE_CONTRACT_ADDRESS)
	componentDetailArray := []ComponentDetail{}
	//fmt.Println(componentAllIDSArray)
	gap := 2000
	for i := 0; i < len(componentAllIDSArray)/gap; i++ {
		componentIDSArray := componentAllIDSArray[i*gap : (i+1)*gap]
		componentDetailArray = append(componentDetailArray, GetComponentDetailBatch(SOULMADE_CONTRACT_ADDRESS, componentIDSArray, SOULMADE_CONTRACT_ADDRESS)...)
	}
	componentDetailArray = append(componentDetailArray, GetComponentDetailBatch(SOULMADE_CONTRACT_ADDRESS, componentAllIDSArray[len(componentAllIDSArray)/gap*gap:], SOULMADE_CONTRACT_ADDRESS)...)
	//拿到符合series的component和id
	need_info := []ComponentDetail{}
	need_ids := []uint64{}
	need_price := []string{}
	count := 0
	product_info := GetProductInfo()
	for _, item := range componentDetailArray {
		price := Query_Component_Price_By_Ipfs(item.ipfsHash, product_info)
		if item.series == series && price != "0" {
			need_info = append(need_info, item)
			need_ids = append(need_ids, item.id)
			if len(price) == 1 {
				price = price + ".0"
			}
			need_price = append(need_price, price)
			count += 1
			if count >= 2500 {
				break
			}
		}
	}
	return need_info, need_ids, need_price
}

func Sell_Component(publicKeyNumber int) {
	_, componentids, componentPrice := Get_Component_Details_Array_Plust_ID_Price("kiko-witch")
	// fmt.Println(len(componentids), len(componentPrice))

	//残次不全, 所以分两批
	batch := 50
	for i := 0; i < len(componentids)/batch; i++ {
		for j := 0; j < batch; j++ {
			go Sell_Single_Market_Place_Tx((i*batch+j)%publicKeyNumber, componentids[i*batch+j], componentPrice[i*batch+j], "SoulMadeComponent")
		}
		for j := 0; j < batch; j++ {
			<-ch
		}
	}
	// left := len(componentids) - len(componentids)/batch*batch
	// for i := 0; i < left; i++ {
	// 	go Sell_Single_Market_Place_Tx((i+len(componentids)/batch*batch)%publicKeyNumber, componentids[i+len(componentids)/batch*batch], componentPrice[i+len(componentids)/batch*batch], "SoulMadeComponent")
	// }
	// for k := 0; k < left; k++ {
	// 	<-ch
	// }
}
