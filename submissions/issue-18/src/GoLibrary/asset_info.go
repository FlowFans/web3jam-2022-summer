package main

import (
	"context"
	"database/sql"
	"encoding/json"
	"fmt"
	"io/ioutil"
	"strings"

	"github.com/onflow/cadence"
	"github.com/onflow/flow-go-sdk"
	"github.com/onflow/flow-go-sdk/client"
	"google.golang.org/grpc"
)

// db struct value
type AssetInfo struct {
	componentID int
	mainid      sql.NullInt64
	edition     int
	owner       string
	for_sale    bool
	ipfs        string
	price       sql.NullFloat64
	series      string
	name        string
}

func GetBodyIDFromDB() []AssetInfo {
	db := PostGreSqlDemo()
	rows, err := db.Query("select * from \"test\".asset where name='Neutral Sienna' or name='Warm Chocolate' or name='Fengfeng' or name='Cool White' order by component_nft_id;")
	if err != nil {
		panic(err)
	}
	defer rows.Close()
	var body_asset_info []AssetInfo
	for rows.Next() {
		var asset AssetInfo
		err = rows.Scan(&asset.componentID, &asset.mainid, &asset.edition, &asset.owner, &asset.for_sale, &asset.ipfs, &asset.price, &asset.series, &asset.name)
		if err != nil {
			panic(err)
		}
		body_asset_info = append(body_asset_info, asset)
	}
	return body_asset_info
}

func GetAllComponentFromDBByHash(ipfsHash string, db *sql.DB) []AssetInfo {
	query_str := fmt.Sprintf("select * from \"test\".asset where ipfs_hash='%s';", ipfsHash)
	rows, err := db.Query(query_str)
	if err != nil {
		panic(err)
	}
	defer rows.Close()
	var all_asset_info []AssetInfo
	for rows.Next() {
		var asset AssetInfo
		err = rows.Scan(&asset.componentID, &asset.mainid, &asset.edition, &asset.owner, &asset.for_sale, &asset.ipfs, &asset.price, &asset.series, &asset.name)
		if err != nil {
			panic(err)
		}
		all_asset_info = append(all_asset_info, asset)
	}
	return all_asset_info
}

//将Component的Profile 进行分类, 分成两种, 即Kiko和Arilf. 这里需要从数据库拿data
// 正确拿到 {series : [profile]}
func Get_All_Component_Divide_Profile() map[string][]string {
	//{series : [profile]}
	component_kind := make(map[string][]string)
	kind := []string{"kiko-witch", "Disordered-FengFeng"}

	for _, item := range kind {
		query_str := fmt.Sprintf("select distinct profile from \"test\".product_info where series='%s' and profile is not null and profile !='Background' and category !='Body' and layer is not null order by profile;", item)
		db := PostGreSqlDemo()
		rows, err := db.Query(query_str)
		if err != nil {
			panic(err)
		}
		defer rows.Close()
		profiles := []string{}
		for rows.Next() {
			var profile string
			err = rows.Scan(&profile)
			if err != nil {
				panic(err)
			}
			profiles = append(profiles, profile)
		}
		component_kind[item] = profiles
	}
	// fmt.Println(component_kind)
	return component_kind
}

func Combine_Main_Body_IDS() map[int][]int {
	//15500个 --> 155个
	bodyinfo := GetBodyIDFromDB()
	var bodyinfo_list []AssetInfo
	for i := 0; i < 15500; i += 100 {
		bodyinfo_list = append(bodyinfo_list, bodyinfo[i])
	}
	//155个
	_, maininfo := GetSeriesMainIDArrayAndDetail("Disordered-FengFeng", true, SOULMADE_CONTRACT_ADDRESS)
	//创建字典 {mainid : [bodyid]}
	dicMainBody := make(map[int][]int)
	recordBodyID := []int{}
	for _, mainitem := range maininfo {
		for bodyindex, bodyitem := range bodyinfo_list {
			if bodyitem.name == mainitem.name && contains(recordBodyID, bodyindex) == false {
				dicMainBody[int(mainitem.id)] = append(dicMainBody[int(mainitem.id)], bodyitem.componentID)
				recordBodyID = append(recordBodyID, bodyindex)
				break
			}
		}
	}
	return dicMainBody
}

func Check_Main_Body_Detail_Name() {
	dicMainBody := Combine_Main_Body_IDS()
	for mainid, bodyid_list := range dicMainBody {
		main_detail := GetSingleMainDetail(SOULMADE_CONTRACT_ADDRESS, uint64(mainid), SOULMADE_CONTRACT_ADDRESS)
		main_detail_name := main_detail.(cadence.Struct).Fields[1].ToGoValue().(string)
		component_detail := GetSingleComponentDetail(SOULMADE_CONTRACT_ADDRESS, uint64(bodyid_list[0]), SOULMADE_CONTRACT_ADDRESS)
		component_detail_name := component_detail.(cadence.Struct).Fields[2].ToGoValue().(string)
		if main_detail_name != component_detail_name {
			fmt.Println(mainid, bodyid_list, "IS wrong")
		} else {
			fmt.Println(mainid, bodyid_list, "IS right")
		}
	}
}

func Get_Number_Componentid_From_Array(array [][]int) int {
	sum := 0
	for _, item := range array {
		sum += len(item)
	}
	return sum
}

//mainid, componentid 都是 一个一个的 尤其是comonent, 并不是多个
// 下面的那个Update才是正版✔
//等长  都是100个
func Update_Asset_Info(mainid []int, componentid []int, db *sql.DB) {
	valueStrings := make([]string, 0, len(mainid))
	slot := 2
	valueArgs := make([]interface{}, 0, len(mainid)*slot)
	for i := 0; i < len(mainid); i++ {
		valueStrings = append(valueStrings, fmt.Sprintf("($%d,$%d)", i*slot+1, i*slot+2))
		valueArgs = append(valueArgs, mainid[i])
		valueArgs = append(valueArgs, componentid[i])
	}
	stmt := fmt.Sprintf("update \"test\".asset set main_nft_id= cast (tmp.main_id as integer) from (values %s) as tmp (main_id, component_id) where component_nft_id= cast (tmp.component_id as integer);", strings.Join(valueStrings, ","))
	_, err := db.Exec(stmt, valueArgs...)
	if err != nil {
		panic(err)
	}
}

//
func Update_Asset_Info_By_Component(mainid []int, componentids [][]int, db *sql.DB) {
	valueStrings := make([]string, 0, len(mainid))
	slot := 2
	component_number := Get_Number_Componentid_From_Array(componentids)
	valueArgs := make([]interface{}, 0, component_number*slot)
	count := 0
	for i := 0; i < len(mainid); i++ {
		componentid := componentids[i]
		for j := 0; j < len(componentid); j++ {
			valueStrings = append(valueStrings, fmt.Sprintf("($%d,$%d)", count*slot+1, count*slot+2))
			valueArgs = append(valueArgs, mainid[i])
			valueArgs = append(valueArgs, componentid[j])
			count += 1
		}
	}
	stmt := fmt.Sprintf("update \"test\".asset set main_nft_id= cast (tmp.main_id as integer) from (values %s) as tmp (main_id, component_id) where component_nft_id= cast (tmp.component_id as integer);", strings.Join(valueStrings, ","))
	_, err := db.Exec(stmt, valueArgs...)
	if err != nil {
		panic(err)
	}
}

func Write_Object_To_Json_File(object any, file_name string) {
	buff, err := json.Marshal(object)
	if err != nil {
		panic(err)
	}
	err = ioutil.WriteFile(file_name, buff, 0644)
	if err != nil {
		panic(err)
	}
}

//传进来100个 {mainid: [bodyid], mainid: [bodyid]}
//data是用来mint的, mainid和componetid是用来更新数据库的
func DepositBodyToMainTx(extraIndex int, data map[int][]int) {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	if err != nil {
		panic(err)
	}
	serviceAcctAddr, serviceAcctKey, serviceSigner := ServiceAccountExtraIndex(flowClient, extraIndex)
	referenceBlockID, err := flowClient.GetLatestBlock(ctx, false)
	tx := flow.NewTransaction().
		SetScript([]byte(deposit_components_to_main_batch(SOULMADE_CONTRACT_ADDRESS))).
		SetGasLimit(9999).
		SetProposalKey(serviceAcctAddr, serviceAcctKey.Index, serviceAcctKey.SequenceNumber).
		SetReferenceBlockID(referenceBlockID.ID).
		SetPayer(serviceAcctAddr).
		AddAuthorizer(serviceAcctAddr)

	keypairs := make([]cadence.KeyValuePair, len(data))
	count := 0
	for mainid, bodyId_list := range data {
		keypairs[count] = cadence.KeyValuePair{
			Key: cadence.NewUInt64(uint64(mainid)),
			Value: cadence.NewArray([]cadence.Value{
				cadence.NewUInt64(uint64(bodyId_list[0])),
			}),
		}
		count += 1
	}
	des_main_body_dic := cadence.NewDictionary(keypairs)
	if err := tx.AddArgument(des_main_body_dic); err != nil {
		panic(err)
	}
	if err := tx.SignEnvelope(serviceAcctAddr, serviceAcctKey.Index, serviceSigner); err != nil {
		panic(err)
	}
	if err := flowClient.SendTransaction(ctx, *tx); err != nil {
		panic(err)
	}
	// // deposit_body_to_main_tx := WaitForSeal(ctx, flowClient, tx.ID())
	// if deposit_body_to_main_tx.Error != nil {
	// 	fmt.Println("Error 🚩", extraIndex, tx.ID())
	// } else {
	// 	// update dataset
	// 	// Update_Asset_Info(mainids, componentids, db)
	// 	fmt.Println("KeyIndex", extraIndex, "Key Sequence", serviceAcctKey.SequenceNumber, "Tx ID", tx.ID().String())
	// }
	fmt.Println("KeyIndex", extraIndex, "Key Sequence", serviceAcctKey.SequenceNumber, "Tx ID", tx.ID().String())
	//ch <- extraIndex
}

func DepositComponentToMainBatchTx(extraIndex int, data map[int][]int, parallel bool) {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	if err != nil {
		panic(err)
	}
	serviceAcctAddr, serviceAcctKey, serviceSigner := ServiceAccountExtraIndex(flowClient, extraIndex)
	referenceBlockID, err := flowClient.GetLatestBlock(ctx, false)
	tx := flow.NewTransaction().
		SetScript([]byte(deposit_components_to_main_batch(SOULMADE_CONTRACT_ADDRESS))).
		SetGasLimit(9999).
		SetProposalKey(serviceAcctAddr, serviceAcctKey.Index, serviceAcctKey.SequenceNumber).
		SetReferenceBlockID(referenceBlockID.ID).
		SetPayer(serviceAcctAddr).
		AddAuthorizer(serviceAcctAddr)

	keypairs := make([]cadence.KeyValuePair, len(data))
	count := 0
	for mainid, bodyId_list := range data {
		var temp []cadence.Value
		for _, bodyid := range bodyId_list {
			temp = append(temp, cadence.NewUInt64(uint64(bodyid)))
		}

		keypairs[count] = cadence.KeyValuePair{
			Key:   cadence.NewUInt64(uint64(mainid)),
			Value: cadence.NewArray(temp),
		}
		count += 1
	}
	des_main_body_dic := cadence.NewDictionary(keypairs)
	if err := tx.AddArgument(des_main_body_dic); err != nil {
		panic(err)
	}
	if err := tx.SignEnvelope(serviceAcctAddr, serviceAcctKey.Index, serviceSigner); err != nil {
		panic(err)
	}
	if err := flowClient.SendTransaction(ctx, *tx); err != nil {
		panic(err)
	}

	if parallel {
		deposit_body_to_main_tx := WaitForSeal(ctx, flowClient, tx.ID())
		if deposit_body_to_main_tx.Error != nil {
			fmt.Println("Error 🚩", extraIndex, tx.ID())
		} else {
			fmt.Println("KeyIndex", extraIndex, "Key Sequence", serviceAcctKey.SequenceNumber, "Tx ID", tx.ID().String())

		}
		ch <- extraIndex
	} else {
		fmt.Println("KeyIndex", extraIndex, "Key Sequence", serviceAcctKey.SequenceNumber, "Tx ID", tx.ID().String())
	}

}

func Deposit_Body_To_Main(publicKeyNumber int) {
	dic_ids := Combine_Main_Body_IDS()
	// fmt.Println(dic_ids)
	// db := PostGreSqlDemo()
	count := 0
	batch := 5
	for mainid, body_ids := range dic_ids {
		mainidArray := make([]int, 0, 100)
		// componentidAarray := make([]int, 0, 100)
		new_mainid_bodyids := make(map[int][]int)
		for i := 0; i < 100; i++ {
			new_mainid_bodyids[mainid+i] = append(new_mainid_bodyids[mainid+i], body_ids[0]+i)
			mainidArray = append(mainidArray, mainid+i)
			// componentidAarray = append(componentidAarray, body_ids[0]+i)
		}
		count += 1
		for j := 0; j < 100/batch; j++ {
			DepositBodyToMainTx(count%publicKeyNumber+j*300, Get_Key_Pair(new_mainid_bodyids, mainidArray[j*batch:(j+1)*batch]))
		}
	}

	// for i := 0; i < len(dic_ids); i++ {
	// 	<-ch
	// }
}

func Get_Key_Pair(diction map[int][]int, keys []int) map[int][]int {
	var result = make(map[int][]int)
	for _, key := range keys {
		result[key] = diction[key]
	}
	return result
}

func Get_Profile_Min_Component_Length(info map[string]map[string][]int) map[string]map[string][]int {
	info_update := make(map[string]map[string][]int)
	for profile_name, ipfs_components_id_diction := range info {
		//1000是我随便取的 , 遇见比他小的就更新
		min_length := 1000
		ipfs_components_ids_update := make(map[string][]int)
		//这一步拿到拿到当前profile最小的
		for _, component_ids := range ipfs_components_id_diction {
			if len(component_ids) < min_length {
				min_length = len(component_ids)
			}
		}
		for ipfs, component_ids := range ipfs_components_id_diction {
			ipfs_components_ids_update[ipfs] = component_ids[:min_length]
		}
		info_update[profile_name] = ipfs_components_ids_update
	}
	return info_update
}

//传进来 {ipfs:[componentid11,componentid12],ipfs2:[componentid21,componentid22]}
//顺序重新排列
//传出去 [[componentid11,component21],[componentid12,component22]]
func Divide_ipfs_components_id(info map[string][]int) [][]int {
	components := [][]int{}
	info_keys := getKeys(info)
	for index := 0; index < len(info[info_keys[0]]); index++ {
		single_component := []int{}
		for _, key := range info_keys {
			single_component = append(single_component, info[key][index])
		}
		components = append(components, single_component)
	}
	return components
}

// 返回的是 [ mainid:[componentid1,componentid2],mainid:[componentid1,componentid2] ]
// 后面再把background放到fengfengdoll里面
func Get_Every_Component_And_Main() []map[int][]int {
	// //get main info from chain
	// //这是155个数据
	_, maininfo := GetSeriesMainIDArrayAndDetail("Disordered-FengFeng", false, SOULMADE_CONTRACT_ADDRESS)
	//将main_id 按照种类分成两种, 即Kiko和Arilf
	//main_kind_id = {"kiko":[mainid],"arilf":[mainid]}
	mainid_kind_id := make(map[string][]uint64)
	for _, main_item := range maininfo {
		mainid_kind_id[main_item.series] = append(mainid_kind_id[main_item.series], main_item.id)
	}

	//get product info from database
	//{ profile : { ipfs1:[],ipfs2:[],ipfs3:[] }, profile2 : {ipfs1:[], ipfs2:[], ipfs3:[]} }
	db := PostGreSqlDemo()
	diction_profile_ipfs_componentsid := make(map[string]map[string][]int)
	ipfs_components_ids := make(map[string][]int)
	// 因为sql中query用的就是order by,所以正常来说 第一个Profile为Agata
	temp_info := GetProductInfo()
	start_profile := temp_info[0].profile.String
	for _, item := range GetProductInfo() {
		//这里需要把Arilf的Background和 两者的Body过滤掉, 因为这些是需要单独处理的
		//两个系列Body 以及 Arilf的Background都需要单独处理一下
		if item.category.String != "Body" && (item.category.String != "Background" || item.series != "Disordered-FengFeng") {
			//每次更新Profile则清空{ipfs:[]}
			if item.profile.String != start_profile {
				start_profile = item.profile.String
				ipfs_components_ids = make(map[string][]int)
			}
			// //再来一重for循环从asset取componentid
			for _, item := range GetAllComponentFromDBByHash(item.ipfs_hash, db) {
				ipfs_components_ids[item.ipfs] = append(ipfs_components_ids[item.ipfs], item.componentID)
			}
			diction_profile_ipfs_componentsid[item.profile.String] = ipfs_components_ids
		}
	}

	//按照最少的那个整理完成 {profile:{ipfs:[],ipfs:[]}}
	diction_profile_ipfs_componentsid = Get_Profile_Min_Component_Length(diction_profile_ipfs_componentsid)
	// Write_Object_To_Json_File(diction_profile_ipfs_componentsid, "filename.json")
	//字典数组
	// need_info = [{mainid:[componentid1,componentid2]},{mainid:[componentid1,componentid2]}]
	need_info := []map[int][]int{}
	//{series : mainid_used}
	main_id_used_count := make(map[string]int, 2)
	//{series : [profile]}
	series_profile_pair := Get_All_Component_Divide_Profile()
	//fmt.Println(series_profile_pair)
	//Start Work
	for series_name, profile_array := range series_profile_pair {
		// fmt.Println(series_name, profile_array)
		main_id_used_count[series_name] = 0
		for _, profile_item := range profile_array {
			for profile_name, ipfs_components_id_array := range diction_profile_ipfs_componentsid {
				if profile_item == profile_name {
					//这里需要判断一下是否为0
					if len(ipfs_components_id_array) != 0 {
						// fmt.Println(ipfs_components_id_array)
						divide_ipfs_components_ids := Divide_ipfs_components_id(ipfs_components_id_array)
						// fmt.Println(series_name, main_id_used_count[series_name], profile_name, len(divide_ipfs_components_ids))
						mainid := mainid_kind_id[series_name][main_id_used_count[series_name]]
						// flag_mainid := mainid
						main_components_id_pair := make(map[int][]int)
						for _, component_id_item := range divide_ipfs_components_ids {
							// if mainid == 4069 {
							// 	fmt.Println(profile_name, component_id_item, main_id_used_count)
							// 	fmt.Println(mainid_kind_id[series_name])
							// 	fmt.Println(flag_mainid, main_id_used_count[series_name], series_name, main_id_used_count)
							// 	fmt.Println("*********************************8")
							// }
							main_components_id_pair[int(mainid)] = component_id_item
							//已经拿到当前profile的 component id 并且按照最小长度 分门别类了, 开始凑成字典, 即 {mainid:[componentid],mainid:[componentid]}
							mainid += 1
							// 每100个换一波
							if mainid%100 == 0 {
								if main_id_used_count[series_name] != len(mainid_kind_id[series_name])-1 {
									main_id_used_count[series_name] += 1
									// fengfengdoll报错是因为 把background算成单个的了
									// fmt.Println(main_id_used_count[series_name])
									mainid = mainid_kind_id[series_name][main_id_used_count[series_name]]
								}
							}
						}
						need_info = append(need_info, main_components_id_pair)
					}
				}
			}
		}
	}
	// Write_Object_To_Json_File(need_info, "need_info.json")
	// fmt.Println(need_info)
	return need_info
}

func Async_Check(index int, item map[int][]int) {
	for mainid, componentids := range item {
		fmt.Println(index, mainid)
		maindetail := GetSingleMainDetail(SOULMADE_CONTRACT_ADDRESS, uint64(mainid), SOULMADE_CONTRACT_ADDRESS)
		series := maindetail.(cadence.Struct).Fields[2].ToGoValue().(string)
		componentids_need := TransferInt_To_Uint64(componentids)
		componentStruct_Array := GetComponentDetailBatch(SOULMADE_CONTRACT_ADDRESS, componentids_need, SOULMADE_CONTRACT_ADDRESS)
		category_array := []string{}
		for _, item := range componentStruct_Array {
			if item.series != series || String_contains(category_array, item.category) {
				fmt.Println(mainid, item.id, series, item.series, "❌")
			}
			category_array = append(category_array, item.category)
		}
	}
	ch <- index
}

//Check Main_ID : [ComponentID] 是不是会有
//1. cagegory 重复
//2. Series不对的情况
func Check_MainID_componentID(info []map[int][]int) {
	batch := 5
	index := 0
	for index < 35 {
		for i := 0; i < batch; i++ {
			go Async_Check(index, info[index])
			index += 1
		}
		for i := 0; i < batch; i++ {
			<-ch
		}
	}
}

func Deposit_Component_Exceplt_Arilf_BackGround_To_Main(publicKeyNumber int) {
	mainid_componentids_diction_array := Get_Every_Component_And_Main()
	count := 0
	gap := 10
	for _, item := range mainid_componentids_diction_array {
		keys := Parse_Data_Get_Key(item)
		values := Parse_Data_Get_Value(item)
		for i := 0; i < len(item)/gap; i++ {
			keys_values_pair := make(map[int][]int)
			gap_keys := keys[i*gap : (i+1)*gap]
			gap_values := values[i*gap : (i+1)*gap]
			for j := 0; j < len(gap_keys); j++ {
				keys_values_pair[gap_keys[j]] = gap_values[j]
			}
			DepositComponentToMainBatchTx(count%publicKeyNumber, keys_values_pair, false)
			count += 1
		}
		fmt.Println("Now count is", count, "💕")
	}
	fmt.Println(count)
}
