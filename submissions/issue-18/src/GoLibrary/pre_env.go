package main

import (
	"crypto/rand"
	"encoding/csv"
	"log"
	"os"
	"strconv"
	"strings"

	"github.com/onflow/flow-go-sdk/crypto"
)

var (
	number int = 100
	ch         = make(chan int, number)

	MAINNET_RPC = "access.mainnet.nodes.onflow.org:9000"
	TESTNET_RPC = "access.devnet.nodes.onflow.org:9000"

	// // MAINNET
	FLOW_TOKEN_ADDRESS         = "0x1654653399040a61"
	FUNGIBLE_TOKEN_ADDRESS     = "0xf233dcee88fe0abe"
	NON_FUNGIBLE_TOKEN_ADDRESS = "0x1d7e57aa55817448"
	NFT_STORE_FRONT_ADDRESS    = "0x4eb8a10cb9f87357"
	SOULMADE_CONTRACT_ADDRESS  = "0x9a57dfe5c8ce609c"

	// TESTNET
	// FLOW_TOKEN_ADDRESS         = "0x7e60df042a9c0868"
	// FUNGIBLE_TOKEN_ADDRESS     = "0x9a0766d93b6608b7"
	// NON_FUNGIBLE_TOKEN_ADDRESS = "0x631e88ae7f1d7c20"
	// NFT_STORE_FRONT_ADDRESS    = "0x94b06cfca1d8a476"
	// SOULMADE_CONTRACT_ADDRESS  = "0xb4187e54e0ed55a8"

)

func GenerateKey() crypto.PrivateKey {
	seed := make([]byte, 32)
	_, _ = rand.Read(seed)
	privateKey, _ := crypto.GeneratePrivateKey(crypto.ECDSA_P256, seed)
	return privateKey
}

//File Operation
func ReadCsvFile(filePath string) [][]string {
	f, err := os.Open(filePath)
	if err != nil {
		log.Fatal("Unable to read input file "+filePath, err)
	}
	defer f.Close()

	csvReader := csv.NewReader(f)
	records, err := csvReader.ReadAll()
	if err != nil {
		log.Fatal("Unable to parse file as CSV for "+filePath, err)
	}
	return records
}

// DiffArray 求两个切片的差集
func DiffArray(a []uint64, b []uint64) []uint64 {
	var diffArray []uint64
	temp := map[uint64]struct{}{}

	for _, val := range b {
		if _, ok := temp[val]; !ok {
			temp[val] = struct{}{}
		}
	}

	for _, val := range a {
		if _, ok := temp[val]; !ok {
			diffArray = append(diffArray, val)
		}
	}
	// fmt.Println(len(diffArray), diffArray)
	return diffArray
}

func String2Uint64Array(strArr []string) []uint64 {
	res := make([]uint64, len(strArr))
	for index, val := range strArr {
		item, err := strconv.ParseUint(val, 10, 64)
		if err != nil {
			panic(err)
		}
		res[index] = item
	}
	return res
}

func contains(s []int, e int) bool {
	for _, a := range s {
		if a == e {
			return true
		}
	}
	return false
}

func Containuint(s []any, e any) bool {
	for _, a := range s {
		if a == e {
			return true
		}
	}
	return false
}

//拿到字典的Key
func getKeys(m map[string][]int) []string {
	// 数组默认长度为map长度,后面append时,不需要重新申请内存和拷贝,效率很高
	j := 0
	keys := make([]string, len(m))
	for k := range m {
		keys[j] = k
		j++
	}
	return keys
}

func Parse_Data_Get_Key(info map[int][]int) []int {
	j := 0
	keys := make([]int, len(info))
	for mainid := range info {
		keys[j] = mainid
		j++
	}
	return keys
}

func Parse_Data_Get_Value(info map[int][]int) [][]int {
	j := 0
	keys := make([][]int, len(info))
	for k := range info {
		keys[j] = info[k]
		j++
	}
	return keys
}

func TransferInt_To_Uint64(array []int) []uint64 {
	var result []uint64
	for _, item := range array {
		result = append(result, uint64(item))
	}
	return result
}

func String_contains(s []string, e string) bool {
	for _, a := range s {
		if a == e {
			return true
		}
	}
	return false
}

func Get_Mim_Length_For_Diction(dict map[string][]uint64) int {
	min := 0
	for _, v := range dict {
		if len(v) < min {
			min = len(v)
		}
	}
	return min
}

// ReadCSVArrangeTable the origin datatable csv
func ReadCSVArrangeTable(file string, dropBoolIndex int) []TableDetail {
	record_info_list := ReadCsvFile(file)
	table := []TableDetail{}
	for _, record := range record_info_list[1:] {
		if record[3] != "Profile" {
			layer, err := strconv.ParseInt(record[4], 10, 64)
			if err != nil {
				panic(err)
			}
			max_edition, err := strconv.ParseInt(record[5], 10, 64)
			if err != nil {
				panic(err)
			}
			free_quantity, err := strconv.ParseInt(record[9], 10, 64)
			if err != nil {
				panic(err)
			}
			// mystery_quantity, err := strconv.ParseInt(record[13], 10, 64)
			// if err != nil {
			// 	panic(err)
			// }

			drop_price := int64(0)
			if record[dropBoolIndex] != "No" {

				price, err := strconv.ParseInt(strings.Replace(strings.Replace(strings.Replace(record[14], "Flow", "", -1), "FLOW", "", -1), " ", "", -1), 10, 64)
				if err != nil {
					panic(err)
				}
				drop_price = price
			}

			drop_quantity, err := strconv.ParseInt(record[13], 10, 64)
			if err != nil {
				panic(err)
			}
			table = append(table, TableDetail{
				series:        record[0],
				name:          record[1],
				description:   strings.Replace(strings.Replace(record[2], "\"", "", -1), "\n", "", -1),
				category:      record[3],
				layer:         layer,
				max_edition:   max_edition,
				ipfs:          record[6],
				file_name:     record[7],
				profile:       record[16],
				free_claim:    record[8] == "Yes",
				free_quantity: free_quantity,
				// mystorybox:     record[12] == "Yes",
				// mysterybox_quantity: mystery_quantity,
				drop_price:    drop_price,
				drop_quantity: drop_quantity,
			})
		}
	}

	return table
}

// ReadCSVArrangeTable the origin datatable csv
func ReadCSVArrangeTableAntihuman(file string, dropBoolIndex int) []TableDetail {
	record_info_list := ReadCsvFile(file)
	table := []TableDetail{}
	for _, record := range record_info_list[1:] {
		if record[3] == "Profile" {
			max_edition, err := strconv.ParseInt(record[5], 10, 64)
			if err != nil {
				panic(err)
			}

			mystery_quantity, err := strconv.ParseInt(record[9], 10, 64)
			if err != nil {
				panic(err)
			}

			table = append(table, TableDetail{
				series:              record[0],
				name:                record[1],
				description:         strings.Replace(strings.Replace(record[2], "\"", "", -1), "\n", "", -1),
				category:            record[3],
				max_edition:         max_edition,
				ipfs:                record[6],
				file_name:           record[7],
				profile:             record[11],
				mysterybox_quantity: mystery_quantity,
			})
		}
	}

	return table
}
