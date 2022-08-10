package main

import (
	"context"
	"fmt"

	"github.com/onflow/cadence"
	"github.com/onflow/flow-go-sdk"
	"github.com/onflow/flow-go-sdk/client"
	"google.golang.org/grpc"
)

func WithdrawComponentFromMainTx(extraIndex int, mainid uint64, categoryList []string) {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	if err != nil {
		panic(err)
	}
	serviceAcctAddr, serviceAcctKey, serviceSigner := ServiceAccountExtraIndex(flowClient, extraIndex)
	referenceBlockID, err := flowClient.GetLatestBlock(ctx, false)
	tx := flow.NewTransaction().
		SetScript([]byte(withdraw_component_from_main(SOULMADE_CONTRACT_ADDRESS))).
		SetGasLimit(9999).
		SetProposalKey(serviceAcctAddr, serviceAcctKey.Index, serviceAcctKey.SequenceNumber).
		SetReferenceBlockID(referenceBlockID.ID).
		SetPayer(serviceAcctAddr).
		AddAuthorizer(serviceAcctAddr)

	mainid_chain := cadence.NewUInt64(mainid)
	argList := []cadence.Value{}
	for _, item := range categoryList {
		item_str, err := cadence.NewString(item)
		if err != nil {
			panic(err)
		}
		argList = append(argList, item_str)
	}
	if err := tx.AddArgument(mainid_chain); err != nil {
		panic(err)
	}

	if err := tx.AddArgument(cadence.NewArray(argList)); err != nil {
		panic(err)
	}
	if err := tx.SignEnvelope(serviceAcctAddr, serviceAcctKey.Index, serviceSigner); err != nil {
		panic(err)
	}
	if err := flowClient.SendTransaction(ctx, *tx); err != nil {
		panic(err)
	}
	txStatus := WaitForSeal(ctx, flowClient, tx.ID())
	if txStatus.Error != nil {
		fmt.Println("Err2🚩", tx.ID(), txStatus.Error)
	} else {
		fmt.Printf("keyindex %d Add Account PK transaction %s status: %s\n", extraIndex, tx.ID(), txStatus.Status)
	}
	ch <- extraIndex
}

//传入Mainid, 传出该Mainid下的CategoryList
func Get_CategoryList_by_Mainid(mainid uint64) []string {
	main_detail := GetSingleMainDetail(SOULMADE_CONTRACT_ADDRESS, mainid, SOULMADE_CONTRACT_ADDRESS)
	cagerory_list := []string{}
	components := main_detail.(cadence.Struct).Fields[5].(cadence.Array).Values
	for _, item := range components {
		cagerory_list = append(cagerory_list, item.(cadence.Struct).Fields[4].ToGoValue().(string))
	}
	return cagerory_list
}

func Async_WithDraw_Component(publicKeyNumber int) {
	index := 0
	batch := 100
	for index < 15500 {
		for i := 0; i < batch; i++ {
			go WithdrawComponentFromMainTx((index+i)%publicKeyNumber, uint64(index+i), Get_CategoryList_by_Mainid(uint64(index+i)))
		}
		for i := 0; i < batch; i++ {
			<-ch
		}
		index = index + batch
	}

}
