package main

import (
	"fmt"
	"strconv"
	"strings"
)

type ComponentAndMainNeedMint struct {
	series      string
	name        string
	description string
	category    string
	layer       int64
	//mint_component用到
	start_ediction int
	end_edition    int
	max_edition    int
	ipfs_hash      string
	profile        string
	membership     string
	is_pack        string
	price          float64
	//mint_main的时候会用到
	main_number int
}

func GetComponentAndMainNeedMintArray(records [][]string, profileIndex int) []ComponentAndMainNeedMint {
	var paramter []ComponentAndMainNeedMint
	for _, record_info := range records[1:] {
		if record_info[3] != "Profile" {
			layer, err := strconv.ParseInt(record_info[4], 10, 64)
			if err != nil {
				panic(err)
			}
			maxedition, err := strconv.Atoi(record_info[5])
			if err != nil {
				panic(err)
			}
			var j int
			if maxedition > 100 {
				for j = 0; j < maxedition/100; j++ {
					// Mint Directly
					record := ComponentAndMainNeedMint{
						series:         record_info[0],
						name:           record_info[1],
						description:    strings.Replace(strings.Replace(record_info[2], "\"", "", -1), "\n", "", -1),
						category:       record_info[3],
						layer:          layer,
						start_ediction: j*100 + 1,
						end_edition:    (j + 1) * 100,
						max_edition:    maxedition,
						ipfs_hash:      record_info[6],
						profile:        record_info[profileIndex],
					}
					paramter = append(paramter, record)
				}
				//如果是不是整百,
				if j*100 < maxedition {
					record := ComponentAndMainNeedMint{
						series:         record_info[0],
						name:           record_info[1],
						description:    strings.Replace(strings.Replace(record_info[2], "\"", "", -1), "\n", "", -1),
						category:       record_info[3],
						layer:          layer,
						start_ediction: j*100 + 1,
						end_edition:    maxedition,
						max_edition:    maxedition,
						ipfs_hash:      record_info[6],
						profile:        record_info[profileIndex],
					}
					paramter = append(paramter, record)
				}
			} else {
				record := ComponentAndMainNeedMint{
					series:         record_info[0],
					name:           record_info[1],
					description:    strings.Replace(strings.Replace(record_info[2], "\"", "", -1), "\n", "", -1),
					category:       record_info[3],
					layer:          layer,
					start_ediction: 1,
					end_edition:    maxedition,
					max_edition:    maxedition,
					ipfs_hash:      record_info[6],
					profile:        record_info[profileIndex],
				}
				paramter = append(paramter, record)
			}
		}
	}
	return paramter
}

func GetComponentAndMainProfileArray(records [][]string) []ComponentAndMainNeedMint {
	var paramter []ComponentAndMainNeedMint
	for _, record_info := range records[1:] {
		if record_info[3] == "Profile" {
			maxedition, err := strconv.Atoi(record_info[5])
			if err != nil {
				panic(err)
			}
			record := ComponentAndMainNeedMint{
				series:      record_info[0],
				name:        record_info[1],
				description: strings.Replace(strings.Replace(record_info[2], "\"", "", -1), "\n", "", -1),
				ipfs_hash:   record_info[6],
				max_edition: maxedition,
				profile:     record_info[11],
			}
			paramter = append(paramter, record)
		}
	}
	return paramter
}

//
func Get_Dictionary_Ipfs_Profile_From_File(records [][]string, profileIndex int) map[string][]string {
	parameter := make(map[string][]string)
	for _, record_info := range records[1:] {
		if record_info[3] != "Body" {
			profile := record_info[profileIndex]
			parameter[profile] = append(parameter[profile], record_info[6])
		}
	}
	return parameter
}

//无并发
func mint_component(publicKeyNumber int, file string) {
	record_info_list := ReadCsvFile(file)
	// sum := 0
	for index, record_info := range GetComponentAndMainNeedMintArray(record_info_list, 11) {
		// if record_info.end_edition == record_info.max_edition {
		// 	sum += record_info.max_edition
		// }
		// fmt.Println(index, record_info, sum, record_info.profile)
		// fmt.Println("++++++++++++++++++++++++++++++++++++++++++++++++++")
		BatchMintComponentHearts(index%publicKeyNumber, record_info)
	}
}

func mint_main(publicKeyNumber int, file string, mint_batch int) {
	record_info_list := ReadCsvFile(file)
	data_list := GetComponentAndMainNeedMintArray(record_info_list, 11)
	main_number := 0
	main_series := ""
	// sum := 0
	for _, record_info := range data_list {
		if record_info.category == "Body" && record_info.end_edition == record_info.max_edition {
			main_number += record_info.max_edition
			main_series = record_info.series
		}
	}
	fmt.Println("main_number:", main_number, "main_series:", main_series)
	j := 0
	for j < main_number/mint_batch {
		BatchMintMain(j%publicKeyNumber, ComponentAndMainNeedMint{
			series:      main_series,
			main_number: mint_batch,
		})
		j++
	}
	if (main_number % mint_batch) != 0 {
		BatchMintMain(j%publicKeyNumber, ComponentAndMainNeedMint{
			series:      main_series,
			main_number: main_number % mint_batch,
		})
	}
}

func set_main(publicKeyNumber int, series string, file string) {
	// Hearts
	record_info_list := ReadCsvFile(file)
	profile_data := GetComponentAndMainProfileArray(record_info_list)
	mainIds, mainDetails := GetSeriesMainIDArrayAndDetail(series, false, SOULMADE_CONTRACT_ADDRESS)
	fmt.Println(len(mainIds), len(profile_data))
	index1 := 0
	index2 := 0

	// tem_need := []ComponentAndMainNeedMint{}
	for index, item := range profile_data {
		if item.profile == "Skink" {
			index1 = index
		}
	}
	profile_data = append(profile_data[:index1], profile_data[index1+1:]...)

	for index, item := range profile_data {
		if item.profile == "Buddha" {
			index2 = index
		}
	}

	profile_data = append(profile_data[:index2], profile_data[index2+1:]...)
	// fmt.Println(tem_need)
	// fmt.Println("______________")
	for _, item := range profile_data {
		fmt.Println(item.profile, item.max_edition)

	}
	// fmt.Println("================")

	needmainids := []uint64{}
	count := 0
	for _, item := range mainDetails {
		if item.ipfs != "QmNNmb79CjcdNTGQs1FxU7g3rcuDaGFRYFt6hsHVYBt4LX" && item.ipfs != "Qmc5sTakyru1uVVeZyhjZugRPFFkgbJ7Lni3hPFVqJx7GS" {
			needmainids = append(needmainids, item.id)
			count += 1
		}
	}
	fmt.Println(count, len(needmainids))

	// for index, item := range needmainids {

	// 	Set_Main_Tx(index%publicKeyNumber, item, profile_data[0].name, profile_data[0].ipfs_hash, profile_data[0].description)
	// 	// if index < 15 {
	// 	// 	Set_Main_Tx(index%publicKeyNumber, item, tem_need[0].name, tem_need[0].ipfs_hash, tem_need[0].description)
	// 	// } else {
	// 	// 	Set_Main_Tx(index%publicKeyNumber, item, tem_need[1].name, tem_need[1].ipfs_hash, tem_need[1].description)
	// 	// }
	// }

	// for _, item := range profile_data {
	// 	fmt.Println(item.series, item.name, item.description, item.ipfs_hash, item.max_edition)
	// }

	batchbefore := 0
	for i, record_info := range profile_data {
		batchnow := record_info.max_edition
		if i == 0 {
			batchbefore = batchnow
		}
		// if batch > 50 {
		// 	small_batch := 50
		// 	for j := 0; j < batch/small_batch; j++ {
		// 		for k := 0; k < small_batch; k++ {
		// 			go Set_Main_Tx((i*batch+j*small_batch+k)%publicKeyNumber, mainIds[i*batch+j*small_batch+k], record_info.name, record_info.ipfs_hash, record_info.description)
		// 		}
		// 		for k := 0; k < small_batch; k++ {
		// 			<-ch
		// 		}
		// 	}

		// } else {
		// 	for j := 0; j < batchnow; j++ {
		// 		Set_Main_Tx((i*batch+j)%publicKeyNumber, needmainids[i*batch+j], record_info.name, record_info.ipfs_hash, record_info.description)
		// 	}
		// }
		for j := 0; j < batchnow; j++ {
			Set_Main_Tx((i*batchbefore+j)%publicKeyNumber, needmainids[i*batchbefore+j], record_info.name, record_info.ipfs_hash, record_info.description)
		}
		batchbefore = batchnow
	}
}

func SetMainByBody(publicKeyNumber int, series string) {
	mainIds, maindetails := GetSeriesMainIDArrayAndDetail(series, false, SOULMADE_CONTRACT_ADDRESS)
	for index, id := range mainIds {
		ipfs := ""
		name := ""
		description := ""

		for _, detail := range maindetails[index].components {
			if detail.category == "Body" {
				ipfs = detail.ipfsHash
				name = detail.name
				description = detail.description
			}
		}
		Set_Main_Tx(index%publicKeyNumber, id, name, ipfs, description)
		fmt.Println(index, id, name, ipfs, description)
	}

	//fmt.Println(mainIds)
}
