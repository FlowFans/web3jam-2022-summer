package main

import (
	"context"
	"encoding/csv"
	"fmt"
	"io/ioutil"
	"log"
	"os"
	"path/filepath"
	"strconv"
	"strings"

	"github.com/onflow/cadence"
	"github.com/onflow/flow-go-sdk"
	"github.com/onflow/flow-go-sdk/client"
	"google.golang.org/grpc"
)

//File Operation
func readCsvFile(filePath string) [][]string {
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

//replace Space in Name field
func replaceSpace(str string) string {
	str = strings.Replace(str, " ", "", -1)
	return str
}

//Format data and write it to file
func writeFile(filePath string, records [][]string) {
	var i int
	str := "*"
	f, err := os.Create(filePath)
	if err != nil {
		panic(err)
	}
	defer f.Close()
	for i = 0; i < len(records); i++ {
		if strings.Contains(records[i][len(records[i])-2], " ") {
			records[i][len(records[i])-2] = replaceSpace(records[i][len(records[i])-2])
		}
		_, err := f.WriteString(strconv.Itoa(i) + "\n" + strings.Join(records[i][:], "---") + "🚩" + strconv.Itoa(len(records[i])) + "\n" + strings.Repeat(str, 10) + "\n")
		if err != nil {
			panic(err)
		}
	}
	fmt.Println("Write File had Done💕")
}

func GetAllFiles(dirPth string) (files []string, err error) {
	fis, err := ioutil.ReadDir(filepath.Clean(filepath.ToSlash(dirPth)))
	if err != nil {
		return nil, err
	}
	for _, f := range fis {
		_path := filepath.Join(dirPth, f.Name())
		if f.IsDir() {
			fs, _ := GetAllFiles(_path)
			files = append(files, fs...)
			continue
		}
		// 指定格式
		switch filepath.Ext(f.Name()) {
		case ".png":
			files = append(files, _path)
		}
	}
	return files, nil
}

func BatchMintComponent(extraIndex int, data ProductInfo) {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	if err != nil {
		panic(err)
	}
	serviceAcctAddr, serviceAcctKey, serviceSigner := ServiceAccountExtraIndex(flowClient, extraIndex)
	referenceBlockID, err := flowClient.GetLatestBlock(ctx, false)
	tx := flow.NewTransaction().
		SetScript([]byte(mintcomponents(SOULMADE_CONTRACT_ADDRESS))).
		SetGasLimit(9999).
		SetProposalKey(serviceAcctAddr, serviceAcctKey.Index, serviceAcctKey.SequenceNumber).
		SetReferenceBlockID(referenceBlockID.ID).
		SetPayer(serviceAcctAddr).
		AddAuthorizer(serviceAcctAddr)

	series, err := cadence.NewString(data.series)
	if err != nil {
		panic(err)
	}
	name, err := cadence.NewString(data.name)
	if err != nil {
		panic(err)
	}
	description, err := cadence.NewString(data.description)
	if err != nil {
		panic(err)
	}
	category, err := cadence.NewString(data.category.String)
	if err != nil {
		panic(err)
	}
	layernumber, err := strconv.ParseUint(strconv.FormatInt(data.layer.Int64, 10), 10, 64)
	if err != nil {
		panic(err)
	}
	layer := cadence.NewUInt64(layernumber)
	if err != nil {
		panic(err)
	}
	endEdictionnumber, err := strconv.ParseUint(strconv.Itoa(data.end_edition), 10, 64)
	if err != nil {
		panic(err)
	}
	endEdiction := cadence.NewUInt64(endEdictionnumber)
	if err != nil {
		panic(err)
	}
	startEdictionnumber, err := strconv.ParseUint(strconv.Itoa(data.start_ediction), 10, 64)
	if err != nil {
		panic(err)
	}
	startEdiction := cadence.NewUInt64(startEdictionnumber)
	if err != nil {
		panic(err)
	}
	maxEdictionnumber, err := strconv.ParseUint(strconv.Itoa(data.max_edition), 10, 64)
	if err != nil {
		panic(err)
	}
	maxEdiction := cadence.NewUInt64(maxEdictionnumber)
	if err != nil {
		panic(err)
	}
	ipfsName, err := cadence.NewString(data.ipfs_hash)
	if err != nil {
		panic(err)
	}

	if err := tx.AddArgument(series); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(name); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(description); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(category); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(layer); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(startEdiction); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(endEdiction); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(maxEdiction); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(ipfsName); err != nil {
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

func BatchMintMain(extraIndex int, data ComponentAndMainNeedMint) {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	if err != nil {
		panic(err)
	}
	serviceAcctAddr, serviceAcctKey, serviceSigner := ServiceAccountExtraIndex(flowClient, extraIndex)
	referenceBlockID, err := flowClient.GetLatestBlock(ctx, false)
	tx := flow.NewTransaction().
		SetScript([]byte(batchMintMain(SOULMADE_CONTRACT_ADDRESS))).
		SetGasLimit(9999).
		SetProposalKey(serviceAcctAddr, serviceAcctKey.Index, serviceAcctKey.SequenceNumber).
		SetReferenceBlockID(referenceBlockID.ID).
		SetPayer(serviceAcctAddr).
		AddAuthorizer(serviceAcctAddr)
	series, err := cadence.NewString(data.series)
	if err != nil {
		panic(err)
	}

	numberMinted := cadence.NewUInt64(uint64(data.main_number))
	if err != nil {
		panic(err)
	}
	// add
	if err := tx.AddArgument(numberMinted); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(series); err != nil {
		panic(err)
	}
	//send tx
	if err := tx.SignEnvelope(serviceAcctAddr, serviceAcctKey.Index, serviceSigner); err != nil {
		panic(err)
	}
	if err := flowClient.SendTransaction(ctx, *tx); err != nil {
		panic(err)
	}
	fmt.Println("KeyIndex", extraIndex, "Key Sequence", serviceAcctKey.SequenceNumber, "Tx ID", tx.ID().String())
}

func BatchSetMain(extraIndex int, data MainDetail) {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	if err != nil {
		panic(err)
	}
	serviceAcctAddr, serviceAcctKey, serviceSigner := ServiceAccountExtraIndex(flowClient, extraIndex)
	referenceBlockID, err := flowClient.GetLatestBlock(ctx, false)
	tx := flow.NewTransaction().
		SetScript([]byte(batchSetMain(SOULMADE_CONTRACT_ADDRESS))).
		SetGasLimit(9999).
		SetProposalKey(serviceAcctAddr, serviceAcctKey.Index, serviceAcctKey.SequenceNumber).
		SetReferenceBlockID(referenceBlockID.ID).
		SetPayer(serviceAcctAddr).
		AddAuthorizer(serviceAcctAddr)

	startid := cadence.NewUInt64(data.id)
	endid := cadence.NewUInt64(data.id + 99)
	name, err := cadence.NewString(data.name)
	if err != nil {
		panic(err)
	}
	ipfs, err := cadence.NewString("")
	if err != nil {
		panic(err)
	}
	description, err := cadence.NewString(data.description)
	if err != nil {
		panic(err)
	}
	if err := tx.AddArgument(startid); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(endid); err != nil {
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
	fmt.Println("KeyIndex", extraIndex, "Key Sequence", serviceAcctKey.SequenceNumber, "Tx ID", tx.ID().String())
}
