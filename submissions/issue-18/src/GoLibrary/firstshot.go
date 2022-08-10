package main

import (
	"encoding/json"
	"fmt"
	"io/ioutil"
	"math/rand"
	"os"
	"reflect"
	"sort"
	"strconv"
	"strings"
	"time"
)

// in: table
// out: omnist-10 []componentids  omnist []componentids
func prepareComponentID(ids []uint64, omnist10_number int, omnist_number int) ([][]uint64, [][]uint64) {
	omnist := [][]uint64{}
	omnist_10 := [][]uint64{}
	for i := 0; i < omnist_number; i++ {
		value_id, idsreturn := Get_Random_From_Array(ids, 1)
		//delete
		ids = idsreturn
		omnist = append(omnist, value_id)
	}
	for i := 0; i < omnist10_number; i++ {
		value_id_10, idsreturn := Get_Random_From_Array(ids, 10)
		//delete
		ids = idsreturn
		omnist_10 = append(omnist_10, value_id_10)
	}
	return omnist_10, omnist

}

// in: table, ipfs, and the number wanted
// out: the component ids
func GetIpfsComponentIDByNumber(componentdetailArray []ComponentDetail, ipfs string, number int64) []uint64 {
	count := int64(0)
	ids := []uint64{}
	for _, component := range componentdetailArray {
		if component.ipfsHash == ipfs {
			count++
			ids = append(ids, component.id)
			// componentdetailArray = append(componentdetailArray[:index], componentdetailArray[index+1:]...)
			if count == number {
				break
			}
		}
	}
	if count < number {
		fmt.Println("Not Enought Component", ipfs)
	}
	return ids
}

func GetIpfsMainIDByNumber(maindetailArray []MainDetailOnChain, ipfs string, number int64) []uint64 {
	count := int64(0)
	ids := []uint64{}
	for _, mainitem := range maindetailArray {
		if mainitem.ipfs == ipfs {
			count++
			ids = append(ids, mainitem.id)
			// maindetailArray = append(maindetailArray[:index], maindetailArray[index+1:]...)
			if count == number {
				break
			}
		}

	}
	return ids
}

func add_to_mysteryBox(series string, file string, omnist10Number int, omnistNumber int) {
	table := ReadCSVArrangeTable(file, 12)
	_, series_cagetory_componentDetails := GetSeriesComponentIDArrayAndDetail(series, "", SOULMADE_CONTRACT_ADDRESS)
	ids_all := []uint64{}
	sum := int64(0)
	for _, record := range table {
		if record.mystorybox {
			ids := GetIpfsComponentIDByNumber(series_cagetory_componentDetails, record.ipfs, record.mysterybox_quantity)
			// fmt.Println(len(ids), record.mysterybox_quantity, int64(len(ids)) == record.mysterybox_quantity)
			ids_all = append(ids_all, ids...)
			sum += record.mysterybox_quantity
		}
		// fmt.Println(record.ipfs, record.mysterybox_quantity)
	}
	fmt.Println(len(ids_all), sum)
	// fmt.Println(ids_all)
	// fmt.Println(len(ids_all))
	omnist10, omnist := prepareComponentID(ids_all, omnist10Number, omnistNumber)
	for i := 0; i < len(omnist10); i++ {
		for j := 0; j < len(omnist10[i]); j++ {
			if Containuint([]any{omnist}, omnist10[i][j]) {
				fmt.Println("error")
			}
		}
	}
	fmt.Println("-", len(omnist10), len(omnist))
	Mint_Pack_FistShot(100, "Omnist10", 50, omnist10, "")
	Mint_Pack_FistShot(100, "Omnist", 50, omnist, "")
}

func AddToMysteryBoxAntihuman(series string, file string) {
	// table := ReadCSVArrangeTableAntihuman(file, 11)
	// for _, item := range table {
	// 	fmt.Println(item)
	// 	fmt.Println("=======================")
	// }
	// count := 0
	// batch := 50
	main_to_pack_ids, _ := GetSeriesMainIDArrayAndDetail(series, false, SOULMADE_CONTRACT_ADDRESS)
	// main_to_pack_ids := []uint64{}
	// for _, tableitem := range table {
	// 	small_count := 0
	// 	for _, mainitem := range mainArray {
	// 		if mainitem.ipfs == tableitem.ipfs {
	// 			main_to_pack_ids = append(main_to_pack_ids, mainitem.id)
	// 			count += 1
	// 			small_count++
	// 			if small_count >= int(tableitem.mysterybox_quantity) {
	// 				break
	// 			}
	// 		}
	// 	}
	// }
	fmt.Println(len(main_to_pack_ids), main_to_pack_ids)

	for index, itemid := range main_to_pack_ids {
		Mint_Pack_Tx(index%100, "", series, "", []uint64{itemid}, []uint64{})
	}

}

//except body else
func sell_drop(file string, series string, publicKeyNumber int) {
	table := ReadCSVArrangeTable(file, 12)
	_, series_cagetory_componentDetails := GetSeriesComponentIDArrayAndDetail(series, "", SOULMADE_CONTRACT_ADDRESS)
	_, seriesMain := GetSeriesMainIDArrayAndDetail(series, false, SOULMADE_CONTRACT_ADDRESS)
	count := 0
	for _, item := range table {
		if item.drop_price != 0 && item.category != "Body" {
			ids := GetIpfsComponentIDByNumber(series_cagetory_componentDetails, item.ipfs, item.drop_quantity)
			// fmt.Println(item.drop_price, item.drop_quantity, len(ids), item.ipfs, int64(len(ids)) == item.drop_quantity)
			price := strconv.Itoa(int(item.drop_price))
			if strings.Contains(price, ".") == false {
				price = price + ".0"
			}
			for _, id := range ids {
				fmt.Println(id, price)
				Sell_Single_Market_Place_Tx(count%publicKeyNumber, id, price, "SoulMadeComponent")
				count += 1
			}
		} else if item.drop_price != 0 && item.category == "Body" {
			ids := GetIpfsMainIDByNumber(seriesMain, item.ipfs, item.drop_quantity)
			// fmt.Println(item.drop_price, item.drop_quantity, len(ids), item.ipfs, int64(len(ids)) == item.drop_quantity)
			price := strconv.Itoa(int(item.drop_price))
			if strings.Contains(price, ".") == false {
				price = price + ".0"
			}
			for _, id := range ids {
				Sell_Single_Market_Place_Tx(count%publicKeyNumber, id, price, "SoulMadeMain")
				count += 1
			}
		}
	}
	fmt.Println(count)
}

func FreeClaimAddAmount(series string, amount int) {
	_, series_cagetory_componentDetails := GetSeriesComponentIDArrayAndDetail(series, "", SOULMADE_CONTRACT_ADDRESS)
	ids_all := []uint64{}
	for _, item := range series_cagetory_componentDetails {
		if item.ipfsHash != "QmWjphuCPWSyKvwBsA8ijU4XKCuwBkUL5S8PzGpysqkrXH" && item.ipfsHash != "QmdpdatvL9J5woxmZCavW3WPUbbGTdp5wicLswU1qMvaKF" {
			ids_all = append(ids_all, item.id)
		}
		// if item.free_claim && item.category != "Body" {
		// 	ids := GetIpfsComponentIDByNumber(series_cagetory_componentDetails, item.ipfs, item.free_quantity)
		// 	ids_all = append(ids_all, ids...)

		// }
	}
	// fmt.Println(len(ids_all))

	mainids, _ := GetSeriesMainIDArrayAndDetail(series, false, SOULMADE_CONTRACT_ADDRESS)

	mainids_all, _ := Get_Random_From_Array(mainids, amount)
	componentids_all, _ := Get_Random_From_Array(ids_all, amount)
	fmt.Println(len(mainids_all), len(componentids_all))
	Mint_Free_Pack_FistShot(100, "Omnist", mainids_all, ids_all)

}

func FreeClaim(file string, series string, needMain bool, needComponent bool) {
	table := ReadCSVArrangeTable(file, 16)
	componentids_all := []uint64{}
	mainids_all := []uint64{}
	if needComponent == true {
		_, series_cagetory_componentDetails := GetSeriesComponentIDArrayAndDetail(series, "", SOULMADE_CONTRACT_ADDRESS)

		for _, item := range table {
			if item.free_claim && item.category != "Body" {
				ids := GetIpfsComponentIDByNumber(series_cagetory_componentDetails, item.ipfs, item.free_quantity)
				componentids_all = append(componentids_all, ids...)

			}
		}
	}

	if needMain == true {
		_, seriesMain := GetSeriesMainIDArrayAndDetail(series, false, SOULMADE_CONTRACT_ADDRESS)
		for _, item := range table {
			if item.free_claim && item.category == "Body" {
				ids := GetIpfsMainIDByNumber(seriesMain, item.ipfs, 25)
				mainids_all = append(mainids_all, ids...)
			}

		}

	}
	fmt.Println(len(componentids_all), len(mainids_all))

	// mainids_all, _ = Get_Random_From_Array(mainids_all, 1000)
	// fmt.Println(len(mainids_all), len(ids_all))
	Mint_Free_Pack_FistShot(100, "Charles-Mastery", mainids_all, componentids_all)

}

func PeliFriendSell() {
	mainids, componentids := MatchComponentAndMainSingle("Omnist", "./google_sheet/KikoForClaim.csv")
	// fmt.Println(len(mainids), len(componentids))
	fmt.Println(mainids, componentids)
	TransferNFTTx(0, componentids[0], "0xe8c2580c65e2eb80", "SoulMadeComponent")
	TransferNFTTx(1, mainids, "0xe8c2580c65e2eb80", "SoulMadeMain")
	// Mint_Pack_Tx(1, "", "Omnist10", "", []uint64{mainids[0]}, componentids[0])
	// Mint_Pack_Tx(2, "", "Omnist10", "", []uint64{mainids[1]}, componentids[1])
	// Mint_Pack_Tx(3, "", "Omnist10", "", []uint64{mainids[2]}, componentids[2])
	// PackToNFTStoreFrontTx(1, 9279, "10.0")
	// PackToNFTStoreFrontTx(2, 9277, "10.0")
}

func PeliTransfer(series string) {
	// ids, componentArray := GetSeriesComponentIDArrayAndDetail(series, "", SOULMADE_CONTRACT_ADDRESS)
	// fmt.Println(len(ids), len(componentArray))
	// count := 0
	// for _, item := range componentArray {
	// 	if item.ipfsHash == "QmejXaTUKknnZYaD6ZqVxV52ShxULLhci36Z3QgDSXKtYQ" ||
	// 		item.ipfsHash == "QmRCyZjy1smG9YkwmF25jLWX9VYZn4pwwY27BYBVMxV6qW" ||
	// 		item.ipfsHash == "QmeXT8Dysfsp5UeD7c4wjQ5qF39TVmX4NxVKC5SXzfe693" ||
	// 		item.ipfsHash == "QmYUg7Ds4ZRDdiBANwLJ1aidpCLHs2wrPfaGQTkAbM9S88" {
	// 		// TransferNFTTx(count%100, []uint64{item.id}, "0xcaea391d9fa0e9df", "SoulMadeComponent")
	// 		count += 1
	// 		fmt.Println(count)
	// 	}
	// }
	_, series_mainDetails := GetSeriesMainIDArrayAndDetail(series, false, SOULMADE_CONTRACT_ADDRESS)
	count := 0
	for _, item := range series_mainDetails {
		if item.ipfs == "QmS8TeQiXSL8SPs3pDGrWXyB3EX82aBE3EqgeEuBAs8RBT" {
			// TransferNFTTx(count, []uint64{item.id}, "0xcaea391d9fa0e9df", "SoulMadeMain")
			fmt.Println("profile1", item)
			count += 1
			fmt.Println("=================")
		}
		// else if item.ipfs == "QmS8TeQiXSL8SPs3pDGrWXyB3EX82aBE3EqgeEuBAs8RBT" {
		// 	fmt.Println("profile3", item)
		// 	fmt.Println("=================")
		// } else {

		// }
	}

}

func verify() {
	profile_ipfs_dic := Get_Dictionary_Ipfs_Profile_From_File(ReadCsvFile("./google_sheet/KikoForClaim.csv"), 11)
	_, series_mainDetails := GetSeriesMainIDArrayAndDetail("Omnist", false, "0xab41270ee6b2b689")
	count := 0
	for _, item := range series_mainDetails {
		ipfs_array := []string{}
		for _, component := range item.components {
			ipfs_array = append(ipfs_array, component.ipfsHash)
		}

		for profile, profile_ipfs := range profile_ipfs_dic {
			sort.Strings(profile_ipfs)
			sort.Strings(ipfs_array)
			if reflect.DeepEqual(profile_ipfs, ipfs_array) {
				fmt.Println(profile, item.id)
				count += 1
				fmt.Println("======================", count)
			}
		}
	}
}

func select_rarity(rarity string) []string {
	jsonFile, err := os.Open("./Resource/rarity.json")
	if err != nil {
		panic(err)
	}
	defer jsonFile.Close()

	needIpfs := []string{}

	byteValue, _ := ioutil.ReadAll(jsonFile)
	var result map[string]interface{}
	json.Unmarshal(byteValue, &result)

	for key, value := range result {
		if value == rarity {
			needIpfs = append(needIpfs, key)
		}
	}
	return needIpfs
}

func GiveawayRandomOmnist(address_array []string, number []int) {
	ipfs_array := select_rarity("R")
	count := 0
	rand.Seed(time.Now().Unix()) // initialize global pseudo random generator
	for address_index, address := range address_array {
		for i := 0; i < number[address_index]; i++ {
			random_ipfs := ipfs_array[rand.Intn(len(ipfs_array))]
			count += 1
			TranferNftByIpfs(count, random_ipfs, address, 1, "SoulMadeComponent")
		}
	}
}

func GiveawayRandom3D(address_array []string, number []int) {
	ipfs_array := []string{"QmQBBteEMB5qG51U8vmMrFc788DiR8mNumhSPtCn6365rS", "QmRCyZjy1smG9YkwmF25jLWX9VYZn4pwwY27BYBVMxV6qW"}
	count := 0
	rand.Seed(time.Now().Unix()) // initialize global pseudo random generator
	for address_index, address := range address_array {
		for i := 0; i < number[address_index]; i++ {
			random_ipfs := ipfs_array[rand.Intn(len(ipfs_array))]
			count += 1
			TranferNftByIpfs(count, random_ipfs, address, 1, "SoulMadeMain")
		}
	}
}

func CheckMain(series string) {
	ids_all, mainarray := GetSeriesMainIDArrayAndDetail(series, false, SOULMADE_CONTRACT_ADDRESS)
	fmt.Println(len(mainarray), ids_all)
	// for i := 0; i < len(changeids); i++ {
	// 	if i < 5 {
	// 		Set_Main_Tx(i%100, changeids[i], "Ghost Messenger", "QmR9gC562KvcchPeojUedXJFumTw7qre3GUk2yCijcRYiS", "Ghost Messenger is a ninja and also a ‘in-between’ beings traveling between the dead and the living to accept clandestine missions.")
	// 	} else {
	// 		Set_Main_Tx(i%100, changeids[i], "Emperor", "QmYvPiubX514kFCcsCHQUpU6KZaYFmPPzaNdNK9hE1bMRV", "He was born a emperor, but the chaplain predicted that when he was 50 years old, the entire country would be engulfed in flames.")
	// 	}
	// }

}
