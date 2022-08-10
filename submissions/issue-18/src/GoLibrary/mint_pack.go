package main

import (
	"context"
	"fmt"
	"math/rand"
	"time"

	"github.com/onflow/cadence"
	"github.com/onflow/flow-go-sdk"
	"github.com/onflow/flow-go-sdk/client"
	"google.golang.org/grpc"
)

type MainDetailOnChain struct {
	id          uint64
	series      string
	name        string
	description string
	ipfs        string
	components  []ComponentDetail
}

func Mint_Pack_Tx(extraIndex int, scarcity string, seires string, ipfs string, mainid []uint64, componentsid []uint64) {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	if err != nil {
		panic(err)
	}
	serviceAcctAddr, serviceAcctKey, serviceSigner := ServiceAccountExtraIndex(flowClient, extraIndex)
	referenceBlockID, err := flowClient.GetLatestBlock(ctx, false)
	tx := flow.NewTransaction().
		SetScript([]byte(mint_pack(SOULMADE_CONTRACT_ADDRESS))).
		SetGasLimit(9999).
		SetProposalKey(serviceAcctAddr, serviceAcctKey.Index, serviceAcctKey.SequenceNumber).
		SetReferenceBlockID(referenceBlockID.ID).
		SetPayer(serviceAcctAddr).
		AddAuthorizer(serviceAcctAddr)

	scarcityAdd, err := cadence.NewString(scarcity)
	if err != nil {
		panic(err)
	}
	seriesAdd, err := cadence.NewString(seires)
	if err != nil {
		panic(err)
	}
	ipfsAdd, err := cadence.NewString(ipfs)
	if err != nil {
		panic(err)
	}
	argmainNFTID := []cadence.Value{}
	for _, item := range mainid {
		argmainNFTID = append(argmainNFTID, cadence.NewUInt64(item))
	}
	mainidAdd := cadence.NewArray(argmainNFTID)
	var componentidAdd cadence.Array
	if len(componentsid) > 0 {
		argcomponentsNFTID := []cadence.Value{}
		for _, item := range componentsid {
			argcomponentsNFTID = append(argcomponentsNFTID, cadence.NewUInt64(item))
		}
		componentidAdd = cadence.NewArray(argcomponentsNFTID)
	} else {
		componentidAdd = cadence.NewArray([]cadence.Value{})
	}

	if err := tx.AddArgument(scarcityAdd); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(seriesAdd); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(ipfsAdd); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(mainidAdd); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(componentidAdd); err != nil {
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

func Mint_Free_Pack_Tx(extraIndex int, scarcity string, seires string, ipfs string, mainid []uint64, componentsid []uint64) {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	if err != nil {
		panic(err)
	}
	serviceAcctAddr, serviceAcctKey, serviceSigner := ServiceAccountExtraIndex(flowClient, extraIndex)
	referenceBlockID, err := flowClient.GetLatestBlock(ctx, false)
	tx := flow.NewTransaction().
		SetScript([]byte(mint_free_pack(SOULMADE_CONTRACT_ADDRESS))).
		SetGasLimit(9999).
		SetProposalKey(serviceAcctAddr, serviceAcctKey.Index, serviceAcctKey.SequenceNumber).
		SetReferenceBlockID(referenceBlockID.ID).
		SetPayer(serviceAcctAddr).
		AddAuthorizer(serviceAcctAddr)

	scarcityAdd, err := cadence.NewString(scarcity)
	if err != nil {
		panic(err)
	}
	seriesAdd, err := cadence.NewString(seires)
	if err != nil {
		panic(err)
	}
	ipfsAdd, err := cadence.NewString(ipfs)
	if err != nil {
		panic(err)
	}
	argmainNFTID := []cadence.Value{}
	for _, item := range mainid {
		argmainNFTID = append(argmainNFTID, cadence.NewUInt64(item))
	}
	mainidAdd := cadence.NewArray(argmainNFTID)
	var componentidAdd cadence.Array
	if len(componentsid) > 0 {
		argcomponentsNFTID := []cadence.Value{}
		for _, item := range componentsid {
			argcomponentsNFTID = append(argcomponentsNFTID, cadence.NewUInt64(item))
		}
		componentidAdd = cadence.NewArray(argcomponentsNFTID)
	} else {
		componentidAdd = cadence.NewArray([]cadence.Value{})
	}

	if err := tx.AddArgument(scarcityAdd); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(seriesAdd); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(ipfsAdd); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(mainidAdd); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(componentidAdd); err != nil {
		panic(err)
	}
	if err := tx.SignEnvelope(serviceAcctAddr, serviceAcctKey.Index, serviceSigner); err != nil {
		panic(err)
	}
	if err := flowClient.SendTransaction(ctx, *tx); err != nil {
		panic(err)
	}
	// fmt.Printf("keyindex %d Add Account PK transaction %s \n", extraIndex, tx.ID())
	txStatus := WaitForSeal(ctx, flowClient, tx.ID())
	if txStatus.Error != nil {
		fmt.Println("Err2🚩", tx.ID(), txStatus.Error)
	} else {
		fmt.Printf("keyindex %d Add Account PK transaction %s status: %s\n", extraIndex, tx.ID(), txStatus.Status)
	}
	ch <- extraIndex
}

// KiKo
// 1300个完整的Main. 两个散装的Component 放到pack中
//传入数组, 以及指定长度, 返回指定长度的数组
func Get_Random(arr []uint64, count int) []uint64 {
	tmpOrigin := make([]uint64, len(arr))
	copy(tmpOrigin, arr)
	rand.Seed(time.Now().Unix())
	rand.Shuffle(len(tmpOrigin), func(i int, j int) {
		tmpOrigin[i], tmpOrigin[j] = tmpOrigin[j], tmpOrigin[i]
	})
	result := make([]uint64, 0, count)
	for index, value := range tmpOrigin {
		if index == count {
			break
		}
		result = append(result, value)
		tmpOrigin = append(tmpOrigin[:index], tmpOrigin[index+1:]...)
		fmt.Println(len(tmpOrigin))
	}
	return result
}

func Get_Random_From_Array(arr []uint64, count int) ([]uint64, []uint64) {
	tmpOrigin := make([]uint64, len(arr))
	copy(tmpOrigin, arr)
	rand.Seed(time.Now().Unix())
	rand.Shuffle(len(tmpOrigin), func(i int, j int) {
		tmpOrigin[i], tmpOrigin[j] = tmpOrigin[j], tmpOrigin[i]
	})

	result := make([]uint64, 0, count)
	for index, value := range tmpOrigin {
		if index == count {
			break
		}
		result = append(result, value)
	}
	tmpOrigin = DiffArray(tmpOrigin, result)
	return result, tmpOrigin
}

// 最后拿到number个随机数的数组
//以及每个pack里面放几个component
func Get_Random_Component_id(series string, number int, component_per_pack int) []uint64 {
	component_id := []uint64{}
	componentAllIDSArray := GetComponentIDS(SOULMADE_CONTRACT_ADDRESS, SOULMADE_CONTRACT_ADDRESS)
	componentDetailArray := []ComponentDetail{}
	//fmt.Println(componentAllIDSArray)
	gap := 2000
	for i := 0; i < len(componentAllIDSArray)/gap; i++ {
		componentIDSArray := componentAllIDSArray[i*gap : (i+1)*gap]
		componentDetailArray = append(componentDetailArray, GetComponentDetailBatch(SOULMADE_CONTRACT_ADDRESS, componentIDSArray, SOULMADE_CONTRACT_ADDRESS)...)
		//fmt.Println(i*gap, (i+1)*gap)
	}
	componentDetailArray = append(componentDetailArray, GetComponentDetailBatch(SOULMADE_CONTRACT_ADDRESS, componentAllIDSArray[len(componentAllIDSArray)/gap*gap:], SOULMADE_CONTRACT_ADDRESS)...)
	for _, item := range componentDetailArray {
		if item.series == series {
			component_id = append(component_id, item.id)
		}
	}
	// fmt.Println(componentAllIDSArray)
	// fmt.Println("**********************************************************")
	// fmt.Println("**********************************************************")
	component_id = Get_Random(component_id, number*component_per_pack)
	return component_id
}

func GetRandomMainid(series string, number int) []uint64 {
	mainAllIDSArray := GetMainIDS(SOULMADE_CONTRACT_ADDRESS, SOULMADE_CONTRACT_ADDRESS)
	mainDetailArray := []MainDetailOnChain{}
	gap := 500
	for i := 0; i < len(mainAllIDSArray)/gap; i++ {
		mainIDSArray := mainAllIDSArray[i*gap : (i+1)*gap]
		mainDetailArray = append(mainDetailArray, GetMainDetailBatchScript(SOULMADE_CONTRACT_ADDRESS, mainIDSArray, SOULMADE_CONTRACT_ADDRESS)...)
		fmt.Println("Get Main Data From Chain", i*gap, (i+1)*gap)
	}
	mainDetailArray = append(mainDetailArray, GetMainDetailBatchScript(SOULMADE_CONTRACT_ADDRESS, mainAllIDSArray[len(mainAllIDSArray)/gap*gap:], SOULMADE_CONTRACT_ADDRESS)...)
	main_id := []uint64{}
	for _, item := range mainDetailArray {
		if item.series == series && len(item.components) >= 2 {
			main_id = append(main_id, item.id)
		}
	}
	main_id = Get_Random(main_id, number)
	return main_id
}

func Mint_Pack_Kiko(publicKeyNumber int) {
	index := 0
	number := 1300
	per_pack := 2
	batch := 50
	series := "kiko-witch"
	mainids := GetRandomMainid(series, number)
	componentids := Get_Random_Component_id(series, number, per_pack)
	for index < number {
		for i := 0; i < batch; i++ {
			go Mint_Pack_Tx((index+i)%publicKeyNumber, "", series, "", mainids[index+i:index+i+1], componentids[(index+i)*per_pack:(index+i+1)*per_pack])
		}
		for i := 0; i < batch; i++ {
			<-ch
		}
		index = index + batch
	}
}

func Mint_Pack_Arilf(publicKeyNumber int) {
	index := 0
	number := 300
	batch := 50
	series := "Disordered-FengFeng"
	mainids := GetRandomMainid(series, number)
	for index < number {
		for i := 0; i < batch; i++ {
			go Mint_Pack_Tx((index+i)%publicKeyNumber, "", series, "", mainids[index+i:index+i+1], []uint64{})
		}
		for i := 0; i < batch; i++ {
			<-ch
		}
		index = index + batch
	}
}

func Mint_Pack(publicKeyNumber int, series string, number int, batch int) {
	index := 0

	mainids := GetRandomMainid(series, number)
	for index < number {
		for i := 0; i < batch; i++ {
			go Mint_Pack_Tx((index+i)%publicKeyNumber, "", series, "", mainids[index+i:index+i+1], []uint64{})
		}
		for i := 0; i < batch; i++ {
			<-ch
		}
		index = index + batch
	}
	// fmt.Println("Just Demo", index, number, batch, mainids)
}

func Mint_Pack_FistShot(publicKeyNumber int, series string, batch int, componenetIDS [][]uint64, scarity string) {
	for i := 0; i < len(componenetIDS)/batch; i++ {
		for j := 0; j < batch; j++ {
			go Mint_Pack_Tx((j+i*batch)%publicKeyNumber, scarity, series, "", []uint64{}, componenetIDS[i*batch+j])
		}
		for j := 0; j < batch; j++ {
			<-ch
		}
	}
	left := len(componenetIDS) - len(componenetIDS)/batch*batch
	if left != 0 {
		for i := 0; i < left; i++ {
			go Mint_Pack_Tx((i+len(componenetIDS)/batch*batch)%publicKeyNumber, scarity, series, "", []uint64{}, componenetIDS[len(componenetIDS)/batch*batch+i])
		}
		for i := 0; i < left; i++ {
			<-ch
		}
	}
}

func Mint_Free_Pack_FistShot(publicKeyNumber int, series string, mainids []uint64, componenetIDS []uint64) {
	gap := 50
	index := 0
	for index < len(mainids) {
		for i := 0; i < gap; i++ {
			// go Mint_Free_Pack_Tx((index+i)%publicKeyNumber, "", series, "", []uint64{mainids[index+i]}, []uint64{componenetIDS[index+i]})
			go Mint_Free_Pack_Tx((index+i)%publicKeyNumber, "", series, "", []uint64{mainids[index+i]}, []uint64{})

		}
		for i := 0; i < gap; i++ {
			<-ch
		}
		index += gap
		fmt.Println(index)
	}
}

// func MintPackAntihuman(publicKeyNumber int, series string, batch int, mainid uint64, scarity string) {
// 	for i := 0; i < len(componenetIDS)/batch; i++ {
// 		for j := 0; j < batch; j++ {
// 			go Mint_Pack_Tx((j+i*batch)%publicKeyNumber, scarity, series, "", []uint64{mainid}, []uint64{})
// 		}
// 		for j := 0; j < batch; j++ {
// 			<-ch
// 		}
// 	}
// 	left := len(componenetIDS) - len(componenetIDS)/batch*batch
// 	if left != 0 {
// 		for i := 0; i < left; i++ {
// 			go Mint_Pack_Tx((i+len(componenetIDS)/batch*batch)%publicKeyNumber, scarity, series, "", []uint64{}, componenetIDS[len(componenetIDS)/batch*batch+i])
// 		}
// 		for i := 0; i < left; i++ {
// 			<-ch
// 		}
// 	}
// }
