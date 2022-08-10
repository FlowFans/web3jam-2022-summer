/*
 * Flow Go SDK
 *
 * Copyright 2019-2020 Dapper Labs, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

package main

import (
	"context"
	"encoding/hex"
	"fmt"
	"time"

	"google.golang.org/grpc"

	"github.com/onflow/cadence"
	jsoncdc "github.com/onflow/cadence/encoding/json"
	"github.com/onflow/flow-go-sdk"
	"github.com/onflow/flow-go-sdk/client"
	"github.com/onflow/flow-go-sdk/crypto"
)

type Contract struct {
	Name   string
	Source string
}

// SourceBytes returns the UTF-8 encoded source code (Source) of the contract.
func (c Contract) SourceBytes() []byte {
	return []byte(c.Source)
}

// SourceHex returns the UTF-8 encoded source code (Source) of the contract as a hex string.
func (c Contract) SourceHex() string {
	return hex.EncodeToString(c.SourceBytes())
}

func WaitForSeal(ctx context.Context, c *client.Client, id flow.Identifier) *flow.TransactionResult {
	result, err := c.GetTransactionResult(ctx, id)
	if err != nil {
		panic(err)
	}

	fmt.Printf("Waiting for transaction %s to be sealed...\n", id)

	for result.Status != flow.TransactionStatusSealed {
		time.Sleep(time.Second)
		result, err = c.GetTransactionResult(ctx, id)
		if err != nil {
			panic(err)
		}
	}
	return result
}

func CreateAccount(accountKeys []*flow.AccountKey, contracts []Contract, payer flow.Address) *flow.Transaction {
	publicKeys := make([]cadence.Value, len(accountKeys))

	for i, accountKey := range accountKeys {
		keyHex := hex.EncodeToString(accountKey.Encode())
		publicKeys[i] = cadence.String(keyHex)
	}

	contractKeyPairs := make([]cadence.KeyValuePair, len(contracts))

	for i, contract := range contracts {
		contractKeyPairs[i] = cadence.KeyValuePair{
			Key:   cadence.String(contract.Name),
			Value: cadence.String(contract.SourceHex()),
		}
	}

	cadencePublicKeys := cadence.NewArray(publicKeys)
	cadenceContracts := cadence.NewDictionary(contractKeyPairs)

	return flow.NewTransaction().
		SetScript([]byte(createAccountTemplate)).
		AddAuthorizer(payer).
		AddRawArgument(jsoncdc.MustEncode(cadencePublicKeys)).
		AddRawArgument(jsoncdc.MustEncode(cadenceContracts))
}

func CreateAccountDemo(RPC string) {
	ctx := context.Background()
	flowClient, err := client.New(RPC, grpc.WithInsecure())
	if err != nil {
		panic(err)
	}

	serviceAcctAddr, serviceAcctKey, serviceSigner := ServiceAccount(flowClient)
	privateKey := GenerateKey()
	fmt.Println("PrivateKey", privateKey)
	fmt.Println("PublicKey", privateKey.PublicKey())

	myAcctKey := flow.NewAccountKey().
		FromPrivateKey(privateKey).
		SetHashAlgo(crypto.SHA3_256).
		SetWeight(flow.AccountKeyWeightThreshold)

	referenceBlockID, err := flowClient.GetLatestBlock(ctx, false)
	createAccountTx := CreateAccount([]*flow.AccountKey{myAcctKey}, nil, serviceAcctAddr)
	createAccountTx.SetProposalKey(
		serviceAcctAddr,
		serviceAcctKey.Index,
		serviceAcctKey.SequenceNumber,
	)
	createAccountTx.SetReferenceBlockID(referenceBlockID.ID)
	createAccountTx.SetPayer(serviceAcctAddr)

	// Sign the transaction with the service account, which already exists
	// All new accounts must be created by an existing account
	err = createAccountTx.SignEnvelope(serviceAcctAddr, serviceAcctKey.Index, serviceSigner)
	if err != nil {
		panic(err)
	}

	// Send the transaction to the network
	err = flowClient.SendTransaction(ctx, *createAccountTx)
	if err != nil {
		panic(err)
	}

	accountCreationTxRes := WaitForSeal(ctx, flowClient, createAccountTx.ID())

	var myAddress flow.Address

	for _, event := range accountCreationTxRes.Events {
		if event.Type == flow.EventAccountCreated {
			accountCreatedEvent := flow.AccountCreatedEvent(event)
			myAddress = accountCreatedEvent.Address()
		}
	}
	fmt.Println("Account created with address:", myAddress.Hex())
}
