package main

import (
	"context"
	"database/sql"
	"fmt"

	"github.com/onflow/cadence"
	"github.com/onflow/flow-go-sdk"
	"github.com/onflow/flow-go-sdk/client"
	"google.golang.org/grpc"
)

func Get_Profile_Ipfs_Dict() map[string][]string {
	product_info := GetProductInfo()
	ipfs_array := []string{}
	diction_profile_ipfs := make(map[string][]string)
	start_profile := product_info[0].profile.String
	for _, item := range product_info {
		//每次更新Profile则清空{ipfs:[]}
		//这里之所以为空的不变是因为, 后面三个都是null, 不会触及if != 语句
		if item.profile.String != start_profile {
			diction_profile_ipfs[start_profile] = ipfs_array
			start_profile = item.profile.String
			ipfs_array = []string{}
		}
		ipfs_array = append(ipfs_array, item.ipfs_hash)
	}
	// Write_Object_To_Json_File(diction_profile_ipfs, "profile_ipfs_dict.json")
	// fmt.Println(len(diction_profile_ipfs), diction_profile_ipfs)
	return diction_profile_ipfs
}

func Get_Kiko_Complete_Main() []MainDetailOnChain {
	mainAllIDSArray := GetMainIDS(SOULMADE_CONTRACT_ADDRESS, SOULMADE_CONTRACT_ADDRESS)
	mainDetailArray := []MainDetailOnChain{}
	gap := 500
	for i := 0; i < len(mainAllIDSArray)/gap; i++ {
		mainIDSArray := mainAllIDSArray[i*gap : (i+1)*gap]
		mainDetailArray = append(mainDetailArray, GetMainDetailBatchScript(SOULMADE_CONTRACT_ADDRESS, mainIDSArray, SOULMADE_CONTRACT_ADDRESS)...)
		fmt.Println("正在拿取链上数据, 总量", len(mainDetailArray), i*gap, (i+1)*gap)
	}
	mainDetailArray = append(mainDetailArray, GetMainDetailBatchScript(SOULMADE_CONTRACT_ADDRESS, mainAllIDSArray[len(mainAllIDSArray)/gap*gap:], SOULMADE_CONTRACT_ADDRESS)...)
	complete_main := []MainDetailOnChain{}
	for _, item := range mainDetailArray {
		// >2 是我们需要去设置的, 即除了body还有别的
		if len(item.components) > 2 && item.series != "Disordered-FengFeng" {
			complete_main = append(complete_main, item)
		}
	}
	return complete_main
}

func Get_Arilf_Complete_Main() []MainDetailOnChain {
	mainAllIDSArray := GetMainIDS(SOULMADE_CONTRACT_ADDRESS, SOULMADE_CONTRACT_ADDRESS)
	mainDetailArray := []MainDetailOnChain{}
	gap := 500
	for i := 0; i < len(mainAllIDSArray)/gap; i++ {
		mainIDSArray := mainAllIDSArray[i*gap : (i+1)*gap]
		mainDetailArray = append(mainDetailArray, GetMainDetailBatchScript(SOULMADE_CONTRACT_ADDRESS, mainIDSArray, SOULMADE_CONTRACT_ADDRESS)...)
		fmt.Println("正在拿取链上数据, 总量", len(mainDetailArray), i*gap, (i+1)*gap)
	}
	mainDetailArray = append(mainDetailArray, GetMainDetailBatchScript(SOULMADE_CONTRACT_ADDRESS, mainAllIDSArray[len(mainAllIDSArray)/gap*gap:], SOULMADE_CONTRACT_ADDRESS)...)
	complete_main := []MainDetailOnChain{}
	for _, item := range mainDetailArray {
		// >2 是我们需要去设置的, 即除了body还有别的
		if len(item.components) > 2 && item.series == "Disordered-FengFeng" {
			complete_main = append(complete_main, item)
		}
	}
	return complete_main
}

func Contains_Ipfs(component ComponentDetail, profile_dict map[string][]string) (bool, string) {
	for profile, ipfs_array := range profile_dict {
		for _, ipfs_hash := range ipfs_array {
			if component.ipfsHash == ipfs_hash {
				return true, profile
			}
		}
	}
	return false, "0"
}

func Query_Profile_Description(profile string, db *sql.DB) (string, string) {
	query := fmt.Sprintf("select * from \"test\".product_info where name='%s'", profile)
	var product ProductInfo
	err := db.QueryRow(query).Scan(&product.creator_name, &product.series, &product.name, &product.description, &product.category, &product.layer, &product.max_edition, &product.ipfs_hash, &product.profile, &product.membership, &product.is_pack, &product.price)
	if err != nil {
		fmt.Println(query, profile, len(profile))
		panic(err)
	}
	return product.description, product.ipfs_hash
}

func Set_Main_Tx(extraIndex int, nftid uint64, nftname string, nftipfs string, nftdescription string) {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	if err != nil {
		panic(err)
	}
	serviceAcctAddr, serviceAcctKey, serviceSigner := ServiceAccountExtraIndex(flowClient, extraIndex)
	referenceBlockID, err := flowClient.GetLatestBlock(ctx, false)
	tx := flow.NewTransaction().
		SetScript([]byte(SetMain(SOULMADE_CONTRACT_ADDRESS))).
		SetGasLimit(9999).
		SetProposalKey(serviceAcctAddr, serviceAcctKey.Index, serviceAcctKey.SequenceNumber).
		SetReferenceBlockID(referenceBlockID.ID).
		SetPayer(serviceAcctAddr).
		AddAuthorizer(serviceAcctAddr)

	id := cadence.NewUInt64(nftid)
	name, err := cadence.NewString(nftname)
	if err != nil {
		panic(err)
	}
	ipfs, err := cadence.NewString(nftipfs)
	if err != nil {
		panic(err)
	}
	description, err := cadence.NewString(nftdescription)
	if err != nil {
		panic(err)
	}
	if err := tx.AddArgument(id); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(name); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(ipfs); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(description); err != nil {
		panic(err)
	}
	if err := tx.SignEnvelope(serviceAcctAddr, serviceAcctKey.Index, serviceSigner); err != nil {
		panic(err)
	}
	if err := flowClient.SendTransaction(ctx, *tx); err != nil {
		panic(err)
	}
	fmt.Printf("keyindex %d Add Account PK transaction %s\n", extraIndex, tx.ID())
	// txStatus := WaitForSeal(ctx, flowClient, tx.ID())
	// if txStatus.Error != nil {
	// 	fmt.Println("Err2🚩", tx.ID(), txStatus.Error)
	// } else {
	// 	fmt.Printf("keyindex %d Add Account PK transaction %s status: %s\n", extraIndex, tx.ID(), txStatus.Status)
	// }
	// ch <- extraIndex
}

func Get_Kiko_Changed_Mainid_And_Name_Description() map[uint64][]string {
	maindetails := Get_Kiko_Complete_Main()
	db := PostGreSqlDemo()
	profile_dict := Get_Profile_Ipfs_Dict()
	need_info := make(map[uint64][]string)
	for _, item := range maindetails {
		for _, component := range item.components {
			if component.category != "Body" && component.category != "Background" {
				contain, profile := Contains_Ipfs(component, profile_dict)
				if contain == true {
					//profile name
					need_info[item.id] = append(need_info[item.id], profile)
					//profile description
					description, ipfs := Query_Profile_Description(profile, db)
					need_info[item.id] = append(need_info[item.id], description)
					need_info[item.id] = append(need_info[item.id], ipfs)
					break
				}
			}
		}
	}
	// Write_Object_To_Json_File(need_info, "need_info.json")
	return need_info
}

func Get_Arilf_Changed_Mainid_And_Name_Description() map[uint64][]string {
	maindetails := Get_Arilf_Complete_Main()
	db := PostGreSqlDemo()
	profile_dict := Get_Profile_Ipfs_Dict()
	need_info := make(map[uint64][]string)
	for _, item := range maindetails {
		for _, component := range item.components {
			if component.category != "Body" && component.category != "Background" {
				contain, profile := Contains_Ipfs(component, profile_dict)
				if contain == true {
					//profile name
					need_info[item.id] = append(need_info[item.id], profile)
					//profile description
					description, ipfs := Query_Profile_Description(profile, db)
					need_info[item.id] = append(need_info[item.id], description)
					need_info[item.id] = append(need_info[item.id], ipfs)
					break
				}
			}
		}
	}
	// fmt.Println(len(need_info))
	// Write_Object_To_Json_File(need_info, "need_info.json")
	return need_info
}

func Parse_Data_Get_Des_Key_Name_Des(info map[uint64][]string) ([]uint64, []string, []string, []string) {
	j := 0
	keys := make([]uint64, len(info))
	names := make([]string, len(info))
	description := make([]string, len(info))
	ipfs := make([]string, len(info))

	for mainid, item := range info {
		names[j] = item[0]
		description[j] = item[1]
		ipfs[j] = item[2]
		keys[j] = mainid
		j++
	}
	return keys, names, description, ipfs
}

func Kiko_Async_Set_Main(publicKeyNumber int) {
	mainid_name_des := Get_Kiko_Changed_Mainid_And_Name_Description()
	mainids, names, descriptions, ipfs := Parse_Data_Get_Des_Key_Name_Des(mainid_name_des)
	//fmt.Println(len(mainids), len(names), len(descriptions), len(ipfs))

	//分两批来做
	// 一批
	batch := 50
	for i := 0; i < len(mainid_name_des)/batch; i++ {
		for j := 0; j < batch; j++ {
			go Set_Main_Tx((i*batch+j)%publicKeyNumber, mainids[i*batch+j], names[i*batch+j], ipfs[i*batch+j], descriptions[i*batch+j])
		}
		for k := 0; k < batch; k++ {
			<-ch
		}
	}
	// 二批
	left := len(mainid_name_des) - len(mainid_name_des)/batch*batch
	for i := 0; i < left; i++ {
		go Set_Main_Tx((i+len(mainid_name_des)/batch*batch)%publicKeyNumber, mainids[i+len(mainid_name_des)/batch*batch], names[i+len(mainid_name_des)/batch*batch], ipfs[i+len(mainid_name_des)/batch*batch], descriptions[i+len(mainid_name_des)/batch*batch])
	}
	for k := 0; k < left; k++ {
		<-ch
	}
}

func Arilf_Async_Set_Main(publicKeyNumber int) {
	mainid_name_des := Get_Arilf_Changed_Mainid_And_Name_Description()
	mainids, names, descriptions, ipfs := Parse_Data_Get_Des_Key_Name_Des(mainid_name_des)
	//fmt.Println(len(mainids), len(names), len(descriptions), len(ipfs))

	//分两批来做
	// 一批
	batch := 50
	for i := 0; i < len(mainid_name_des)/batch; i++ {
		for j := 0; j < batch; j++ {
			go Set_Main_Tx((i*batch+j)%publicKeyNumber, mainids[i*batch+j], names[i*batch+j], ipfs[i*batch+j], descriptions[i*batch+j])
		}
		for k := 0; k < batch; k++ {
			<-ch
		}
	}
}
