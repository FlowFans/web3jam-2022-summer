package main

import (
	"context"
	"fmt"
	"strings"

	"github.com/onflow/cadence"
	"github.com/onflow/flow-go-sdk"
	"github.com/onflow/flow-go-sdk/client"
	"github.com/onflow/flow-go-sdk/crypto"
	"github.com/onflow/flow-go-sdk/templates"
	"google.golang.org/grpc"
)

func AddAccountDifferentKey(weight int) {
	ctx := context.Background()

	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	if err != nil {
		panic(err)
	}

	acctAddr, acctKey, acctSigner := ServiceAccount(flowClient)

	// Create the new key to add to your account
	myPrivateKey := GenerateKey()
	fmt.Println("myPrivateKey", myPrivateKey)
	fmt.Println("myPublicKey", myPrivateKey.PublicKey())
	myAcctKey := flow.NewAccountKey().
		FromPrivateKey(myPrivateKey).
		SetHashAlgo(crypto.SHA3_256).
		SetWeight(weight)

	addKeyTx := templates.AddAccountKey(acctAddr, myAcctKey)
	//fmt.Println("myAcctKey", myAcctKey)

	referenceBlockID, err := flowClient.GetLatestBlock(ctx, false)

	addKeyTx.SetProposalKey(acctAddr, acctKey.Index, acctKey.SequenceNumber)
	addKeyTx.SetReferenceBlockID(referenceBlockID.ID)
	addKeyTx.SetPayer(acctAddr)

	// Sign the transaction with the new account.
	err = addKeyTx.SignEnvelope(acctAddr, acctKey.Index, acctSigner)
	if err != nil {
		panic(err)
	}

	// Send the transaction to the network.
	err = flowClient.SendTransaction(ctx, *addKeyTx)
	if err != nil {
		panic(err)
	}
	accountAddKeyTx := WaitForSeal(ctx, flowClient, addKeyTx.ID())
	fmt.Printf("Transfer flow transaction %s status: %s\n", addKeyTx.ID(), accountAddKeyTx.Status)
}

func AddAccountSameKey(pk string, numbertoAdd int, extraIndex int) {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	if err != nil {
		panic(err)
	}
	serviceAcctAddr, serviceAcctKey, serviceSigner := ServiceAccountExtraIndex(flowClient, extraIndex)
	referenceBlockID, err := flowClient.GetLatestBlock(ctx, false)
	tx := flow.NewTransaction().
		SetScript([]byte(addKeytx)).
		SetGasLimit(9000).
		SetProposalKey(serviceAcctAddr, serviceAcctKey.Index, serviceAcctKey.SequenceNumber).
		SetReferenceBlockID(referenceBlockID.ID).
		SetPayer(serviceAcctAddr).
		AddAuthorizer(serviceAcctAddr)

	toAddPublicKey := cadence.String(pk)
	if err := tx.AddArgument(toAddPublicKey); err != nil {
		panic(err)
	}

	toAddNumber := cadence.NewInt(numbertoAdd)
	if err := tx.AddArgument(toAddNumber); err != nil {
		panic(err)
	}

	if err := tx.SignEnvelope(serviceAcctAddr, serviceAcctKey.Index, serviceSigner); err != nil {
		panic(err)
	}
	if err := flowClient.SendTransaction(ctx, *tx); err != nil {
		panic(err)
	}
	accountAddSameKeyTx := WaitForSeal(ctx, flowClient, tx.ID())
	if accountAddSameKeyTx.Error != nil {
		fmt.Println("Err2🚩", tx.ID(), accountAddSameKeyTx.Error)
	} else {
		fmt.Printf("keyindex %d Add Account PK transaction %s status: %s\n", extraIndex, tx.ID(), accountAddSameKeyTx.Status)
	}
	// fmt.Printf("keyindex %d Add Account PK transaction %s \n", extraIndex, tx.ID())
	// ch <- extraIndex
}

func transferFlow(extraIndex int, to string, amount string) {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	if err != nil {
		panic(err)
	}
	serviceAcctAddr, serviceAcctKey, serviceSigner := ServiceAccountExtraIndex(flowClient, extraIndex)
	referenceBlockID, err := flowClient.GetLatestBlock(ctx, false)
	tx := flow.NewTransaction().
		SetScript([]byte(transferScript)).
		SetGasLimit(9000).
		SetProposalKey(serviceAcctAddr, serviceAcctKey.Index, serviceAcctKey.SequenceNumber).
		SetReferenceBlockID(referenceBlockID.ID).
		SetPayer(serviceAcctAddr).
		AddAuthorizer(serviceAcctAddr)
	toAmount, err := cadence.NewUFix64(amount)
	if err != nil {
		panic(err)
	}
	toAddress := cadence.NewAddress(flow.HexToAddress(to))

	if err := tx.AddArgument(toAmount); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(toAddress); err != nil {
		panic(err)
	}

	if err := tx.SignEnvelope(serviceAcctAddr, serviceAcctKey.Index, serviceSigner); err != nil {
		panic(err)
	}

	if err := flowClient.SendTransaction(ctx, *tx); err != nil {
		panic(err)
	}

	accountTransferFlowTxRes := WaitForSeal(ctx, flowClient, tx.ID())
	fmt.Printf("Transfer flow transaction %s status: %s\n", tx.ID(), accountTransferFlowTxRes.Status)
}

//Get All the ComponentID In For Special Address
func GetComponentIDS(checkaddr string, contractAddr string) []uint64 {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	address := cadence.NewAddress(flow.HexToAddress(checkaddr))
	args := []cadence.Value{address}
	value, err := flowClient.ExecuteScriptAtLatestBlock(ctx, getComponentIDS(contractAddr), args)
	if err != nil {
		panic(err)
	}
	ids := value.String()
	ids = strings.Replace(ids, "[", "", -1)
	ids = strings.Replace(ids, "]", "", -1)
	ids = strings.Replace(ids, " ", "", -1)
	ids_array := String2Uint64Array(strings.Split(ids, ","))
	return ids_array
	// return value.String()
}

func GetSellingComponentID(checkAddr string, contractAddr string) string {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())

	address := cadence.NewAddress(flow.HexToAddress(checkAddr))
	args := []cadence.Value{address}
	value, err := flowClient.ExecuteScriptAtLatestBlock(ctx, getSellingComponentIDS(contractAddr), args)
	if err != nil {
		panic(err)
	}
	return value.String()
}

func GetSellingMainID(checkAddr string, contractAddr string) string {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())

	address := cadence.NewAddress(flow.HexToAddress(checkAddr))
	args := []cadence.Value{address}
	value, err := flowClient.ExecuteScriptAtLatestBlock(ctx, getSellingMainIDS(contractAddr), args)
	if err != nil {
		panic(err)
	}
	return value.String()
}

func ServiceAccount(flowClient *client.Client) (flow.Address, *flow.AccountKey, crypto.Signer) {
	//servicePrivateKeySigAlgp := crypto.StringToHashAlgorithm(crypto.ECDSA_P256.String())
	privateKey, err := crypto.DecodePrivateKeyHex(crypto.ECDSA_P256, senderPriv)
	if err != nil {
		panic(err)
	}

	addr := flow.HexToAddress(senderAddress)
	acc, err := flowClient.GetAccount(context.Background(), addr)
	if err != nil {
		panic(err)
	}

	accountKey := acc.Keys[0]
	signer := crypto.NewInMemorySigner(privateKey, accountKey.HashAlgo)
	return addr, accountKey, signer
}

func ServiceAccountExtraIndex(flowClient *client.Client, extra int) (flow.Address, *flow.AccountKey, crypto.Signer) {
	//servicePrivateKeySigAlgp := crypto.StringToHashAlgorithm(crypto.ECDSA_P256.String())
	privateKey, err := crypto.DecodePrivateKeyHex(crypto.ECDSA_P256, senderPriv)
	if err != nil {
		panic(err)
	}
	addr := flow.HexToAddress(senderAddress)
	acc, err := flowClient.GetAccount(context.Background(), addr)
	if err != nil {
		panic(err)
	}

	accountKey := acc.Keys[0+extra]
	signer := crypto.NewInMemorySigner(privateKey, accountKey.HashAlgo)
	return addr, accountKey, signer
}
