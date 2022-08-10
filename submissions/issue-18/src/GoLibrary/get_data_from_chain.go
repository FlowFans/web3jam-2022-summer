package main

import (
	"context"
	"fmt"
	"reflect"
	"strconv"
	"strings"

	"github.com/onflow/cadence"
	"github.com/onflow/flow-go-sdk"
	"github.com/onflow/flow-go-sdk/client"
	"google.golang.org/grpc"
)

type MainDetail struct {
	id          uint64
	series      string
	name        string
	description string
	ipfs        string
	component   ProductInfo
}

type ComponentDetail struct {
	id          uint64
	series      string
	name        string
	description string
	category    string
	layer       uint64
	edition     uint64
	maxedition  uint64
	ipfsHash    string
}

type MarKetPlaceSaleData struct {
	saleid          uint64
	price           uint64
	nftType         string
	mainDetail      MainDetailOnChain
	componentDetail ComponentDetail
}

func (s MainDetailOnChain) MainIsEmpty() bool {
	return reflect.DeepEqual(s, MainDetailOnChain{})
}

func (s ComponentDetail) ComponentIsEmpty() bool {
	return reflect.DeepEqual(s, ComponentDetail{})
}

func GetSingleMainDetail(checkAddr string, id uint64, contractAddr string) cadence.Value {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	address := cadence.NewAddress(flow.HexToAddress(checkAddr))
	args := []cadence.Value{address, cadence.NewUInt64(id)}
	value, err := flowClient.ExecuteScriptAtLatestBlock(ctx, getSingleMainDetail(contractAddr), args)
	if err != nil {
		panic(err)
	}
	return value
}

func GetSingleComponentDetail(checkAddr string, id uint64, contractAddr string) cadence.Value {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	address := cadence.NewAddress(flow.HexToAddress(checkAddr))
	args := []cadence.Value{address, cadence.NewUInt64(id)}
	value, err := flowClient.ExecuteScriptAtLatestBlock(ctx, getcomponentDetail(contractAddr), args)
	if err != nil {
		panic(err)
	}
	return value
}

//这里是hardcode上去了, 回头再改
func Combine_Chain_Database_Main_Detail() []MainDetail {
	var parameter []MainDetail
	body_need := Get_Body_All_Detail_From_Product()
	WC := 0
	CW := 0
	NS := 0
	for i := 0; i < 15500; i = i + 100 {
		fmt.Println(i)
		var main_detail MainDetail
		nftid, err := strconv.ParseUint(strconv.Itoa(i), 10, 64)
		if err != nil {
			panic(err)
		}
		mainDetail := GetSingleMainDetail(SOULMADE_CONTRACT_ADDRESS, nftid, SOULMADE_CONTRACT_ADDRESS)
		main_detail.id = mainDetail.(cadence.Struct).Fields[0].ToGoValue().(uint64)
		main_detail.series = mainDetail.(cadence.Struct).Fields[2].ToGoValue().(string)
		if mainDetail.(cadence.Struct).Fields[2].ToGoValue().(string) == "kiko-witch" {
			if WC < body_need[2].max_edition/100 {
				main_detail.name = body_need[2].name
				main_detail.description = body_need[2].description
				WC += 1
			} else if CW < body_need[1].max_edition/100 {
				main_detail.name = body_need[1].name
				main_detail.description = body_need[1].description
				CW += 1
			} else if NS < body_need[0].max_edition/100 {
				main_detail.name = body_need[0].name
				main_detail.description = body_need[0].description
				NS += 1
			}
		} else if mainDetail.(cadence.Struct).Fields[2].ToGoValue().(string) == "Disordered-FengFeng" {
			main_detail.name = body_need[3].name
			main_detail.description = body_need[3].description
		}
		parameter = append(parameter, main_detail)
	}
	return parameter
}

func GetMainDetailBatchScript(checkAddr string, nftids []uint64, contractAddr string) []MainDetailOnChain {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	address := cadence.NewAddress(flow.HexToAddress(checkAddr))
	argNFTID := []cadence.Value{}
	for _, item := range nftids {
		argNFTID = append(argNFTID, cadence.NewUInt64(item))
	}
	args := []cadence.Value{address, cadence.NewArray(argNFTID)}
	value, err := flowClient.ExecuteScriptAtLatestBlock(ctx, getMainDetailBatch(contractAddr), args)
	if err != nil {
		panic(err)
	}

	mainDetailArray := []MainDetailOnChain{}
	// fmt.Println(value.(cadence.Array).Values)
	for _, item := range value.(cadence.Array).Values {
		mainDetail := MainDetailOnChain{
			id:          item.(cadence.Struct).Fields[0].ToGoValue().(uint64),
			name:        item.(cadence.Struct).Fields[1].ToGoValue().(string),
			series:      item.(cadence.Struct).Fields[2].ToGoValue().(string),
			description: item.(cadence.Struct).Fields[3].ToGoValue().(string),
			ipfs:        item.(cadence.Struct).Fields[4].ToGoValue().(string),
		}
		//Parse Component Detail
		componentDetailArray := []ComponentDetail{}
		cadenceComponent := item.(cadence.Struct).Fields[5]
		for _, componentitem := range cadenceComponent.(cadence.Array).Values {
			componentDetailStruct := ComponentDetail{
				id:          componentitem.(cadence.Struct).Fields[0].ToGoValue().(uint64),
				series:      componentitem.(cadence.Struct).Fields[1].ToGoValue().(string),
				name:        componentitem.(cadence.Struct).Fields[2].ToGoValue().(string),
				description: componentitem.(cadence.Struct).Fields[3].ToGoValue().(string),
				category:    componentitem.(cadence.Struct).Fields[4].ToGoValue().(string),
				layer:       componentitem.(cadence.Struct).Fields[5].ToGoValue().(uint64),
				edition:     componentitem.(cadence.Struct).Fields[6].ToGoValue().(uint64),
				maxedition:  componentitem.(cadence.Struct).Fields[7].ToGoValue().(uint64),
				ipfsHash:    componentitem.(cadence.Struct).Fields[8].ToGoValue().(string),
			}
			componentDetailArray = append(componentDetailArray, componentDetailStruct)
		}
		mainDetail.components = componentDetailArray
		mainDetailArray = append(mainDetailArray, mainDetail)
	}
	return mainDetailArray
}

func Get_Body_All_Detail_From_Product() []ProductInfo {
	var paramter []ProductInfo
	product_info_list := GetProductInfo()
	for _, product_info := range product_info_list {
		if product_info.category.String == "Body" {
			paramter = append(paramter, product_info)
		}
	}
	return paramter
}

func GetMainIDS(checkAddr string, contractAddr string) []uint64 {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	address := cadence.NewAddress(flow.HexToAddress(checkAddr))
	args := []cadence.Value{address}
	fmt.Println("Args", args)
	value, err := flowClient.ExecuteScriptAtLatestBlock(ctx, getMainIDS(contractAddr), args)
	if err != nil {
		fmt.Println("Here is the Error")
		panic(err)
	}
	ids := value.String()
	ids = strings.Replace(ids, "[", "", -1)
	ids = strings.Replace(ids, "]", "", -1)
	ids = strings.Replace(ids, " ", "", -1)
	ids_array := String2Uint64Array(strings.Split(ids, ","))
	return ids_array
}

func GetComponentDetailBatch(checkAddr string, nftids []uint64, contractAddr string) []ComponentDetail {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())

	address := cadence.NewAddress(flow.HexToAddress(checkAddr))
	argNFTID := []cadence.Value{}
	for _, item := range nftids {
		argNFTID = append(argNFTID, cadence.NewUInt64(item))
	}
	args := []cadence.Value{address, cadence.NewArray(argNFTID)}
	value, err := flowClient.ExecuteScriptAtLatestBlock(ctx, getcomponentDetailBatch(contractAddr), args)
	if err != nil {
		panic(err)
	}
	//Parse Get Data
	componentsDetailArray := []ComponentDetail{}
	for _, item := range value.(cadence.Array).Values {
		componentDetailStruct := ComponentDetail{
			id:          item.(cadence.Struct).Fields[0].ToGoValue().(uint64),
			series:      item.(cadence.Struct).Fields[1].ToGoValue().(string),
			name:        item.(cadence.Struct).Fields[2].ToGoValue().(string),
			description: item.(cadence.Struct).Fields[3].ToGoValue().(string),
			category:    item.(cadence.Struct).Fields[4].ToGoValue().(string),
			layer:       item.(cadence.Struct).Fields[5].ToGoValue().(uint64),
			edition:     item.(cadence.Struct).Fields[6].ToGoValue().(uint64),
			maxedition:  item.(cadence.Struct).Fields[7].ToGoValue().(uint64),
			ipfsHash:    item.(cadence.Struct).Fields[8].ToGoValue().(string),
		}
		componentsDetailArray = append(componentsDetailArray, componentDetailStruct)
	}
	return componentsDetailArray
}

func GetPackIDSScript(contractAddr string) []uint64 {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	address := cadence.NewAddress(flow.HexToAddress(SOULMADE_CONTRACT_ADDRESS))
	args := []cadence.Value{address}
	value, err := flowClient.ExecuteScriptAtLatestBlock(ctx, getPackIDS(contractAddr), args)
	if err != nil {
		panic(err)
	}
	ids := value.String()

	ids = strings.Replace(ids, "[", "", -1)
	ids = strings.Replace(ids, "]", "", -1)
	ids = strings.Replace(ids, " ", "", -1)
	ids_arr := String2Uint64Array(strings.Split(ids, ","))
	return ids_arr
}

func GetSinglePackDetailScript(id uint64, contractAddr string) cadence.Value {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	address := cadence.NewAddress(flow.HexToAddress(SOULMADE_CONTRACT_ADDRESS))
	args := []cadence.Value{address, cadence.NewUInt64(id)}
	fmt.Println(args)
	value, err := flowClient.ExecuteScriptAtLatestBlock(ctx, getSinglePackDetail(contractAddr), args)
	if err != nil {
		panic(err)
	}
	return value
}

func GetPackDetailBatchScript(nftids []uint64, contractAddr string) []Pack {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())

	address := cadence.NewAddress(flow.HexToAddress(SOULMADE_CONTRACT_ADDRESS))
	argNFTID := []cadence.Value{}
	for _, item := range nftids {
		argNFTID = append(argNFTID, cadence.NewUInt64(item))
	}
	args := []cadence.Value{address, cadence.NewArray(argNFTID)}
	value, err := flowClient.ExecuteScriptAtLatestBlock(ctx, getBatchPackDetail(contractAddr), args)
	if err != nil {
		panic(err)
	}
	//Parse Get Data
	packDetailArray := []Pack{}
	for _, item := range value.(cadence.Array).Values {
		packDetailStruct := Pack{
			id:       item.(cadence.Struct).Fields[0].ToGoValue().(uint64),
			scarcity: item.(cadence.Struct).Fields[1].ToGoValue().(string),
			series:   item.(cadence.Struct).Fields[2].ToGoValue().(string),
			ipfs:     item.(cadence.Struct).Fields[3].ToGoValue().(string),
		}
		packDetailArray = append(packDetailArray, packDetailStruct)
	}
	return packDetailArray
}

func GetAllMarketPlaceSaleData(contractAddr string) []MarKetPlaceSaleData {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())

	address := cadence.NewAddress(flow.HexToAddress(SOULMADE_CONTRACT_ADDRESS))

	args := []cadence.Value{address}
	value, err := flowClient.ExecuteScriptAtLatestBlock(ctx, getAllSellDataMarketPalce(contractAddr), args)
	if err != nil {
		panic(err)
	}
	//Parse Get Data
	saleDetailArray := []MarKetPlaceSaleData{}
	for _, item := range value.(cadence.Array).Values {
		saleDetailStruct := MarKetPlaceSaleData{
			saleid:  item.(cadence.Struct).Fields[0].ToGoValue().(uint64),
			price:   item.(cadence.Struct).Fields[1].ToGoValue().(uint64),
			nftType: item.(cadence.Struct).Fields[2].ToGoValue().(string),
		}

		//Parse Main
		mainitem := item.(cadence.Struct).Fields[3]
		mainitem = mainitem.(cadence.Optional).Value
		//如果是Component, 则是Nil
		if mainitem == nil {
			//fmt.Println("Main is nil", item.(cadence.Struct).Fields[4])
			componentitem := item.(cadence.Struct).Fields[4].(cadence.Optional).Value
			com_componentDetailStruct := ComponentDetail{
				id:          componentitem.(cadence.Struct).Fields[0].ToGoValue().(uint64),
				series:      componentitem.(cadence.Struct).Fields[1].ToGoValue().(string),
				name:        componentitem.(cadence.Struct).Fields[2].ToGoValue().(string),
				description: componentitem.(cadence.Struct).Fields[3].ToGoValue().(string),
				category:    componentitem.(cadence.Struct).Fields[4].ToGoValue().(string),
				layer:       componentitem.(cadence.Struct).Fields[5].ToGoValue().(uint64),
				edition:     componentitem.(cadence.Struct).Fields[6].ToGoValue().(uint64),
				maxedition:  componentitem.(cadence.Struct).Fields[7].ToGoValue().(uint64),
				ipfsHash:    componentitem.(cadence.Struct).Fields[8].ToGoValue().(string),
			}
			saleDetailStruct.componentDetail = com_componentDetailStruct
		} else {
			mainDetail := MainDetailOnChain{
				id:          mainitem.(cadence.Struct).Fields[0].ToGoValue().(uint64),
				name:        mainitem.(cadence.Struct).Fields[1].ToGoValue().(string),
				series:      mainitem.(cadence.Struct).Fields[2].ToGoValue().(string),
				description: mainitem.(cadence.Struct).Fields[3].ToGoValue().(string),
				ipfs:        mainitem.(cadence.Struct).Fields[4].ToGoValue().(string),
			}
			//Parse Component
			componentDetailArray := []ComponentDetail{}
			cadenceComponent := mainitem.(cadence.Struct).Fields[5]
			for _, componentitem := range cadenceComponent.(cadence.Array).Values {
				main_componentDetailStruct := ComponentDetail{
					id:          componentitem.(cadence.Struct).Fields[0].ToGoValue().(uint64),
					series:      componentitem.(cadence.Struct).Fields[1].ToGoValue().(string),
					name:        componentitem.(cadence.Struct).Fields[2].ToGoValue().(string),
					description: componentitem.(cadence.Struct).Fields[3].ToGoValue().(string),
					category:    componentitem.(cadence.Struct).Fields[4].ToGoValue().(string),
					layer:       componentitem.(cadence.Struct).Fields[5].ToGoValue().(uint64),
					edition:     componentitem.(cadence.Struct).Fields[6].ToGoValue().(uint64),
					maxedition:  componentitem.(cadence.Struct).Fields[7].ToGoValue().(uint64),
					ipfsHash:    componentitem.(cadence.Struct).Fields[8].ToGoValue().(string),
				}
				componentDetailArray = append(componentDetailArray, main_componentDetailStruct)
			}
			mainDetail.components = componentDetailArray
			saleDetailStruct.mainDetail = mainDetail
		}
		saleDetailArray = append(saleDetailArray, saleDetailStruct)
	}

	return saleDetailArray
}

func GetSeriesMainIDArrayAndDetail(series string, onlyBody bool, checkAddr string) ([]uint64, []MainDetailOnChain) {
	mainAllIDSArray := GetMainIDS(checkAddr, SOULMADE_CONTRACT_ADDRESS)
	mainDetailArray := []MainDetailOnChain{}
	gap := 500
	for i := 0; i < len(mainAllIDSArray)/gap; i++ {
		mainIDSArray := mainAllIDSArray[i*gap : (i+1)*gap]
		mainDetailArray = append(mainDetailArray, GetMainDetailBatchScript(checkAddr, mainIDSArray, SOULMADE_CONTRACT_ADDRESS)...)
		fmt.Println("Now Getting the Main data from chain, total number is", len(mainAllIDSArray), i*gap, (i+1)*gap)
	}
	mainDetailArray = append(mainDetailArray, GetMainDetailBatchScript(checkAddr, mainAllIDSArray[len(mainAllIDSArray)/gap*gap:], SOULMADE_CONTRACT_ADDRESS)...)
	needDetailArray := []MainDetailOnChain{}
	need_id := []uint64{}
	if onlyBody == true {
		for _, mainiten := range mainDetailArray {
			if mainiten.series == series && len(mainiten.components) <= 1 {
				need_id = append(need_id, mainiten.id)
				needDetailArray = append(needDetailArray, mainiten)
			}
		}
	} else {
		for _, mainiten := range mainDetailArray {
			if mainiten.series == series {
				need_id = append(need_id, mainiten.id)
				needDetailArray = append(needDetailArray, mainiten)
			}
		}
	}
	return need_id, needDetailArray
}

func GetSeriesComponentIDArrayAndDetail(series string, category string, checkAddr string) ([]uint64, []ComponentDetail) {
	componentAllIDSArray := GetComponentIDS(checkAddr, SOULMADE_CONTRACT_ADDRESS)
	componentDetailArray := []ComponentDetail{}
	gap := 2000
	for i := 0; i < len(componentAllIDSArray)/gap; i++ {
		componentIDSArray := componentAllIDSArray[i*gap : (i+1)*gap]
		componentDetailArray = append(componentDetailArray, GetComponentDetailBatch(SOULMADE_CONTRACT_ADDRESS, componentIDSArray, SOULMADE_CONTRACT_ADDRESS)...)
		fmt.Println("Now Getting the Component data from chain, total number is", len(componentAllIDSArray), i*gap, (i+1)*gap)
	}
	componentDetailArray = append(componentDetailArray, GetComponentDetailBatch(SOULMADE_CONTRACT_ADDRESS, componentAllIDSArray[len(componentAllIDSArray)/gap*gap:], SOULMADE_CONTRACT_ADDRESS)...)
	//拿到符合series的componentDetail和id
	need_info := []ComponentDetail{}
	need_id := []uint64{}
	for _, componentitem := range componentDetailArray {
		if category != "" {
			if componentitem.series == series && componentitem.category == category {
				need_id = append(need_id, componentitem.id)
				need_info = append(need_info, componentitem)
			}
		} else {
			if componentitem.series == series {
				need_id = append(need_id, componentitem.id)
				need_info = append(need_info, componentitem)
			}
		}
	}
	return need_id, need_info
}

func GetIPFSComponentIDArrayAndDetail(ipfs string, address string) ([]uint64, []ComponentDetail) {
	componentAllIDSArray := GetComponentIDS(address, SOULMADE_CONTRACT_ADDRESS)
	componentDetailArray := []ComponentDetail{}
	gap := 2000
	for i := 0; i < len(componentAllIDSArray)/gap; i++ {
		componentIDSArray := componentAllIDSArray[i*gap : (i+1)*gap]
		componentDetailArray = append(componentDetailArray, GetComponentDetailBatch(SOULMADE_CONTRACT_ADDRESS, componentIDSArray, SOULMADE_CONTRACT_ADDRESS)...)
		fmt.Println("Get Component Data From Chain, the total number is", len(componentAllIDSArray), i*gap, (i+1)*gap)
	}
	componentDetailArray = append(componentDetailArray, GetComponentDetailBatch(SOULMADE_CONTRACT_ADDRESS, componentAllIDSArray[len(componentAllIDSArray)/gap*gap:], SOULMADE_CONTRACT_ADDRESS)...)
	need_info := []ComponentDetail{}
	need_id := []uint64{}
	for _, componentitem := range componentDetailArray {
		if componentitem.ipfsHash == ipfs {
			need_id = append(need_id, componentitem.id)
			need_info = append(need_info, componentitem)
		}
	}
	return need_id, need_info
}

func GetIPFSCMainIDArrayAndDetail(ipfs string, address string) ([]uint64, []MainDetailOnChain) {
	mainAllIDSArray := GetMainIDS(address, SOULMADE_CONTRACT_ADDRESS)
	mainDetailArray := []MainDetailOnChain{}
	gap := 500
	for i := 0; i < len(mainAllIDSArray)/gap; i++ {
		mainIDSArray := mainAllIDSArray[i*gap : (i+1)*gap]
		mainDetailArray = append(mainDetailArray, GetMainDetailBatchScript(SOULMADE_CONTRACT_ADDRESS, mainIDSArray, SOULMADE_CONTRACT_ADDRESS)...)
		fmt.Println("Now Getting the Main data from chain, total number is", len(mainAllIDSArray), i*gap, (i+1)*gap)
	}
	mainDetailArray = append(mainDetailArray, GetMainDetailBatchScript(SOULMADE_CONTRACT_ADDRESS, mainAllIDSArray[len(mainAllIDSArray)/gap*gap:], SOULMADE_CONTRACT_ADDRESS)...)
	needDetailArray := []MainDetailOnChain{}
	need_id := []uint64{}
	for _, mainitem := range mainDetailArray {
		if mainitem.ipfs == ipfs {
			need_id = append(need_id, mainitem.id)
			needDetailArray = append(needDetailArray, mainitem)
		}
	}
	return need_id, needDetailArray
}
