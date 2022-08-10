package main

import (
	"context"
	"fmt"

	"github.com/onflow/cadence"
	"github.com/onflow/flow-go-sdk"
	"github.com/onflow/flow-go-sdk/client"
	"google.golang.org/grpc"
)

func BatchMintComponentHearts(extraIndex int, data ComponentAndMainNeedMint) {
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
	category, err := cadence.NewString(data.category)
	if err != nil {
		panic(err)
	}
	layer := cadence.NewUInt64(uint64(data.layer))
	if err != nil {
		panic(err)
	}
	endEdiction := cadence.NewUInt64(uint64(data.end_edition))
	if err != nil {
		panic(err)
	}
	startEdiction := cadence.NewUInt64(uint64(data.start_ediction))
	if err != nil {
		panic(err)
	}
	maxEdiction := cadence.NewUInt64(uint64(data.max_edition))
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

func DepositComponentToSingleMainTX(extraIndex int, mainid uint64, componentids []uint64) {
	ctx := context.Background()
	flowClient, err := client.New(MAINNET_RPC, grpc.WithInsecure())
	if err != nil {
		panic(err)
	}
	serviceAcctAddr, serviceAcctKey, serviceSigner := ServiceAccountExtraIndex(flowClient, extraIndex)
	referenceBlockID, err := flowClient.GetLatestBlock(ctx, false)
	tx := flow.NewTransaction().
		SetScript([]byte(deposit_components_to_main_single(SOULMADE_CONTRACT_ADDRESS))).
		SetGasLimit(9999).
		SetProposalKey(serviceAcctAddr, serviceAcctKey.Index, serviceAcctKey.SequenceNumber).
		SetReferenceBlockID(referenceBlockID.ID).
		SetPayer(serviceAcctAddr).
		AddAuthorizer(serviceAcctAddr)

	main_id := cadence.NewUInt64(mainid)
	componentID := []cadence.Value{}
	for _, item := range componentids {
		componentID = append(componentID, cadence.NewUInt64(item))
	}
	componentIds := cadence.NewArray(componentID)
	if err := tx.AddArgument(main_id); err != nil {
		panic(err)
	}
	if err := tx.AddArgument(componentIds); err != nil {
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

//return the series all mainid and componentid
//Just Fit Antihuman and hearts, because they are two kind of body
func Match_Body_And_Main(series string, MainIpfs string, BodyIpfs string, include bool) ([]uint64, [][]uint64) {
	_, series_mainDetails := GetSeriesMainIDArrayAndDetail(series, true, SOULMADE_CONTRACT_ADDRESS)
	_, series_cagetory_componentDetails := GetSeriesComponentIDArrayAndDetail(series, "Body", SOULMADE_CONTRACT_ADDRESS)
	mainids := []uint64{}
	componentids := [][]uint64{}
	if include == true {
		for _, mainitem := range series_mainDetails {
			if mainitem.ipfs == MainIpfs {
				mainids = append(mainids, mainitem.id)
			}
		}
		for _, componentitem := range series_cagetory_componentDetails {
			if componentitem.ipfsHash == BodyIpfs {
				componentids = append(componentids, []uint64{componentitem.id})
			}
		}
	} else {
		for _, mainitem := range series_mainDetails {
			if mainitem.ipfs != MainIpfs {
				mainids = append(mainids, mainitem.id)
			}
		}
		for _, componentitem := range series_cagetory_componentDetails {
			if componentitem.ipfsHash != BodyIpfs {
				componentids = append(componentids, []uint64{componentitem.id})
			}
		}
	}
	return mainids, componentids
}

func Match_Body_And_Main_ForAll(series string) ([]uint64, [][]uint64) {
	_, series_mainDetails := GetSeriesMainIDArrayAndDetail(series, true, SOULMADE_CONTRACT_ADDRESS)
	_, series_cagetory_componentDetails := GetSeriesComponentIDArrayAndDetail(series, "Body", SOULMADE_CONTRACT_ADDRESS)
	mainids := []uint64{}
	componentids := [][]uint64{}
	for _, mainitem := range series_mainDetails {
		if len(mainitem.components) == 0 {
			mainids = append(mainids, mainitem.id)
		}
	}
	for _, componentitem := range series_cagetory_componentDetails {
		componentids = append(componentids, []uint64{componentitem.id})
	}
	return mainids, componentids
}

func DepositSeriesBody(series string, PKNumber int, batch int) {
	mainids, componenetids := Match_Body_And_Main_ForAll(series)
	fmt.Println(len(mainids), len(componenetids))
	if len(mainids) == len(componenetids) {
		if len(mainids) <= batch {
			for i := 0; i < len(mainids); i++ {
				go DepositComponentToSingleMainTX(i, mainids[i], componenetids[i])
			}
			for i := 0; i < len(mainids); i++ {
				<-ch
			}
		} else {
			i := 0
			for i < len(mainids)/batch {
				for j := 0; j < batch; j++ {
					go DepositComponentToSingleMainTX((i*batch+j)%PKNumber, mainids[i*batch+j], componenetids[i*batch+j])
				}
				for j := 0; j < batch; j++ {
					<-ch
				}
				i += 1
			}
			if i*batch < len(mainids) {
				for j := 0; j < len(mainids)-i*batch; j++ {
					go DepositComponentToSingleMainTX((i*batch+j)%PKNumber, mainids[i*batch+j], componenetids[i*batch+j])
				}
				for j := 0; j < len(mainids)-i*batch; j++ {
					<-ch
				}
			}
		}
	} else {
		fmt.Println("Error: mainids and componenetids not match")
	}
}

func IpfsContains(s []string, str string) bool {
	for _, v := range s {
		if v == str {
			return true
		}
	}
	return false
}

func makeuplittle(series string) {
	_, series_mainDetails := GetSeriesMainIDArrayAndDetail(series, true, SOULMADE_CONTRACT_ADDRESS)
	mainids := []uint64{}
	// fmt.Println(len(series_mainDetails))
	for _, mainitem := range series_mainDetails {
		if mainitem.ipfs == "" {
			mainids = append(mainids, mainitem.id)
		}
	}
	fmt.Println(len(mainids))
	// small_batch := 50
	// for j := 0; j < len(mainids)/small_batch; j++ {
	// 	for k := 0; k < small_batch; k++ {
	// 		go Set_Main_Tx((j*small_batch+k)%200, mainids[j*small_batch+k], "Mermaid Prince", "QmS8TeQiXSL8SPs3pDGrWXyB3EX82aBE3EqgeEuBAs8RBT", "Mermaids are actually genderless until they meet their soulmate. The mermaid prince wandering on the moon, who will you meet then?")
	// 	}
	// 	for k := 0; k < small_batch; k++ {
	// 		<-ch
	// 	}
	// }
}

func Get_Dic_Keys(mymap map[string][]uint64) []string {
	keys := make([]string, len(mymap))
	i := 0
	for k := range mymap {
		keys[i] = k
		i++
	}
	return keys
}

func MatchComponentAndMain(series string, file string) ([]uint64, [][]uint64) {
	mainids := []uint64{}
	componentids := [][]uint64{}
	_, series_mainDetails := GetSeriesMainIDArrayAndDetail(series, false, SOULMADE_CONTRACT_ADDRESS)
	_, series_cagetory_componentDetails := GetSeriesComponentIDArrayAndDetail(series, "", SOULMADE_CONTRACT_ADDRESS)
	profile_ipfs_dic := Get_Dictionary_Ipfs_Profile_From_File(ReadCsvFile(file), 11)
	fmt.Println("GM", len(series_cagetory_componentDetails), len(series_mainDetails))
	// for index, item := range profile_ipfs_dic {
	// 	fmt.Println(index, item)
	// }
	// fmt.Println("================================")
	for profile, value := range profile_ipfs_dic {
		// fmt.Println("++++++++++++++++++++++++++++++")
		// fmt.Println(profile)
		start := len(mainids)
		for _, mainitem := range series_mainDetails {
			if IpfsContains(value, mainitem.ipfs) {
				mainids = append(mainids, mainitem.id)
			}
		}
		// fmt.Println("Mainids", mainids)
		end := len(mainids)

		category_profile_dic := map[string][]uint64{}
		for _, componentitem := range series_cagetory_componentDetails {
			if IpfsContains(value, componentitem.ipfsHash) {
				category_profile_dic[componentitem.category] = append(category_profile_dic[componentitem.category], componentitem.id)
			}
		}
		// fmt.Println(category_profile_dic)

		for key, value := range category_profile_dic {
			if end-start != len(value) {
				fmt.Println(profile, end-start, key, len(value), "❌")
			} else {
				fmt.Println(profile, end-start, key, len(value))
			}
		}

		if len(category_profile_dic) > 0 {
			keys := Get_Dic_Keys(category_profile_dic)
			fmt.Println("Keys🤩", keys)
			for j := 0; j < len(category_profile_dic[keys[0]]); j++ {
				componenet_temp := []uint64{}
				for i := 0; i < len(keys); i++ {
					componenet_temp = append(componenet_temp, category_profile_dic[keys[i]][j])
				}
				componentids = append(componentids, componenet_temp)
			}
		}
		fmt.Println("-----------------------")
	}
	fmt.Println(len(mainids), mainids[0], len(componentids), componentids[0])
	return mainids, componentids
}

func MatchComponentAndMainSingle(series string, file string) ([]uint64, [][]uint64) {
	mainids := []uint64{}
	componentids := [][]uint64{}
	_, series_mainDetails := GetSeriesMainIDArrayAndDetail(series, false, SOULMADE_CONTRACT_ADDRESS)
	_, series_cagetory_componentDetails := GetSeriesComponentIDArrayAndDetail(series, "", SOULMADE_CONTRACT_ADDRESS)
	profile_ipfs_dic := Get_Dictionary_Ipfs_Profile_From_File(ReadCsvFile(file), 11)
	for _, value := range profile_ipfs_dic {
		if IpfsContains(value, "QmSmVRAqpsz4Y2pPEugfuuPCmDCbYqtfSU3ibm3BFHwVvd") == false &&
			IpfsContains(value, "QmbJZJEqxsAfJDdRDhvmSW8iGffj77UEzQkow3xy3gB4Xk") == false &&
			IpfsContains(value, "Qma1jggmKspqmAAMovNHDxmLq9XAhzmYzmtJZcEuzxeqmt") == false &&
			IpfsContains(value, "QmYzHYePdfUzui7NsuZvQkzKo36y7DkwquPJ9v79W4GUSP") == false &&
			IpfsContains(value, "QmXxwAnCG5KD6Wh2e6LBcNeEtVEJ8Qx76PhAz9PLH538wa") == false &&
			IpfsContains(value, "QmaUPdNkpAhgB2dfjksW5L6eMYMspcBHTcRMvgXEdTNKsc") == false &&
			IpfsContains(value, "QmP5tGdtvCW5evJMYJAEM2Q2hXg4XM6kZi7MTKf7XV2vuv") == false &&
			IpfsContains(value, "QmexHmTxNp6YJiqxTEY5RBWp8objWSHhFGiR4higvABFwm") == false &&
			IpfsContains(value, "QmZRwasRwdUC3zpBQKVZ2kYExvEnU6qjao36Qx4mfhhsi7") == false &&

			IpfsContains(value, "QmY2bnxJS5zdZp9qSayLnf8TwTJp7htzPBoYEFCKrRYSRz") == false &&
			IpfsContains(value, "QmWzdzAwLrpCb6c78MjuqL2wFkTNFeJBsVAbCW9c4mdDdU") == false &&
			IpfsContains(value, "QmdTG5wm9sMHhf1RgEUrY9o2noNuz1cbtEQBKw6EmLyGqh") == true &&
			IpfsContains(value, "QmRBEgqzKY4axkqACPXUppi1jQKs8HBgSsTnV1Zu2aEhok") == false &&
			IpfsContains(value, "QmZq6R2sjiRKz5K4NRCS7iCxMP3g5a33uj8PPCAyUQaino") == false &&
			IpfsContains(value, "QmWeLHYhyfNYuDVSAXQBs8d3prvVrsu1KfqprRX3mhAa8P") == false {
			for _, mainitem := range series_mainDetails {
				if IpfsContains(value, mainitem.ipfs) {
					mainids = append(mainids, mainitem.id)
					break
				}
			}
			category_profile_dic := map[string][]uint64{}
			for _, componentitem := range series_cagetory_componentDetails {
				if IpfsContains(value, componentitem.ipfsHash) {
					category_profile_dic[componentitem.category] = append(category_profile_dic[componentitem.category], componentitem.id)
				}
			}

			if len(category_profile_dic) > 0 {
				keys := Get_Dic_Keys(category_profile_dic)
				componenet_temp := []uint64{}
				for i := 0; i < len(keys); i++ {
					componenet_temp = append(componenet_temp, category_profile_dic[keys[i]][0])
				}
				componentids = append(componentids, componenet_temp)
			}
		}
	}
	return mainids, componentids
}

func DepositSeriesComponenet(series string, file string, PKNumber int, batch int) {
	mainids, componenetids := MatchComponentAndMain(series, file)
	fmt.Println(len(mainids), len(componenetids))
	// Count Componenet Number
	// count := 0
	// for _, value := range componenetids {
	// 	count += len(value)
	// }
	// fmt.Println("Component Number 💕", count)

	if len(mainids) == len(componenetids) {
		//if smaller than batch=50
		if len(mainids) <= batch {
			for i := 0; i < len(mainids); i++ {
				go DepositComponentToSingleMainTX(i, mainids[i], componenetids[i])
			}
			for i := 0; i < len(mainids); i++ {
				<-ch
			}
		} else {
			i := 0
			for i < len(mainids)/batch {
				for j := 0; j < batch; j++ {
					go DepositComponentToSingleMainTX((i*batch+j)%PKNumber, mainids[i*batch+j], componenetids[i*batch+j])
				}
				for j := 0; j < batch; j++ {
					<-ch
				}
				i++
			}
			if i*batch < len(mainids) {
				for j := 0; j < len(mainids)-i*batch; j++ {
					go DepositComponentToSingleMainTX((i*batch+j)%PKNumber, mainids[i*batch+j], componenetids[i*batch+j])
				}
				for j := 0; j < len(mainids)-i*batch; j++ {
					<-ch
				}
			}
		}
	} else {
		fmt.Println("The Main And Componenet Number is not Right❌")
	}
}
