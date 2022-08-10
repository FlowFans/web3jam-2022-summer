package main

func pre_main() {
	// 	//1.先随便新建一个账户
	CreateAccountDemo(TESTNET_RPC)

	// 	//2. 地址添加同一个publicKey. 这个地址是本来一直在发送tx的地址,
	// 	//如果想要给新建的account添加pk的话,
	// 	//需要去env.go里面改一下address和privatekey
	// 	//Parameter: publickKey, numberAdd
	// AddAccountSameKey("7c3f5cf44dcd26d81978521ec20b6a2cba8ccdb4617051498f6521c983b1b338062db703f8b71fee5355fe5885a99bb081f97385a91a1cbde9f8d5579ec1b7c4", 99, 0)

	// // 3. 添加不同的密钥对
	// AddAccountDifferentKey(1000)
}

func database_main() {
	// 	//4. Batch Mint Components
	// 	//Bathc Mint之前需要走一步setup_admin_account, 我直接用flowCLI做的, 没有用go✔
	// 	//还需要把account里面的合约弄好
	// 	//Parameter: publicKeyNumber
	// BatchProductMintComponents(100)

}

func main() {
	pre_main()

	// 6. Batch Mint main
	// Batch_Product_Mint_Main(100)
	// fmt.Println(GetSingleMainDetail(0))
	// Batch_Product_Mint_Main(100)
	// Batch_Set_Main(100)

	// demo()
	// Deposit_Body_To_Main(100)

	//并发的一个demo 用add_key
	//Demo_Async_Add_Same_Key(number)
	// Update_Asset_Info()

	// 7.deposit body to main and update asset table
	// Deposit_Body_To_Main(300)
	// Deposit_Component_Exceplt_Arilf_BackGround_To_Main(1000)

	// Async_WithDraw_Component(number)

	// 9. 挑选1300个Main 以及 2600个Component  + Arilf 的300个Main
	// Mint_Pack_Kiko(number)
	// Mint_Pack_Arilf(number)

	// 10 Pack
	// Pack_To_NFTStoreFront(number, "kiko-witch", 1300, "20.0")
	// Pack_To_NFTStoreFront(number, "Disordered-FengFeng", 300, "12.0")

	// 11, set 完整main的name和descroption
	// Get_Changed_Mainid_And_Name_Description()
	// Kiko_Async_Set_Main(number)
	// Arilf_Async_Set_Main(number)
	// Get_Arilf_Sell_Main_Id()
	// Sell_Arilf_Main(number)
	// fmt.Println(7855 - 7855/50*50)

	// 12.上架Marktplace
	// Get_Sell_Main_Id()

	// Sell_Kiko_Main(number)
	// Get_Profile_Ipfs_Dict()2
	// Get_Arilf_Changed_Mainid_And_Name_Description()
	// fmt.Println(len(Get_Arilf_Complete_Main()))
	// Get_Sell_Main_Id()
	// Get_Changed_Mainid_And_Name_Description()
	//Get_Component_Details_Array_Plust_ID_Price("kiko-witch")

	// Sell_Component(number)
	// Get_Arilf_Complete_Main()
	// 撤销MarketPlace
	// BatchCancelSellingMain()
	// BatchCancelSellingComponent()

	//NFtStoreFront 撤销掉
	// UnSell_NFTStoreFront(number)

	// OpenPack

	// Get_All_MarketPlace_SaleData()

	// BatchCancelSellingMain()
	// BatchCancelSellingComponent()
	// Sell_Component(number)
	// Sell_Kiko_Main(number)

	// 6.8
	// mint_component(100, "./google_sheet/Antihuman.csv")

	// mint_component(100, "./google_sheet/SelfLoveBabe.csv")
	// mint_main(100, "./google_sheet/SelfLoveBabe.csv", 100)
	// set_main(number, "SelfLoveBabe", "./google_sheet/SelfLoveBabe.csv")
	// DepositSeriesBody("SelfLoveBabe", number, 50, "", "", false)
	// DepositSeriesComponenet("SelfLoveBabe", "./google_sheet/SelfLoveBabe.csv", number, 50)

	// set_main(100, "AntiHuman-Demons", "./google_sheet/Antihuman.csv")
	// makeuplittle("AntiHuman-Demons")

	// Get_Dictionary_Ipfs_Profile_From_File(ReadCsvFile("./google_sheet/Hearts.csv"))
	// DepositSeriesBody("AntiHuman-Demons", number, 50)
	// MatchComponentAndMain("AntiHuman-Demons", "./google_sheet/Antihuman.csv")
	// MatchComponentAndMain("Hearts&Flowers", "./google_sheet/Hearts.csv")
	// DepositSeriesComponenet("Hearts&Flowers", "./google_sheet/Hearts.csv", number, 40)
	// DepositSeriesComponenet("AntiHuman-Demons", "./google_sheet/Antihuman.csv", number, 50)

	// Drop, 即 Marketplace界面
	// StartNewSeries

	// mint_component(100, "./google_sheet/SelfLoveBabe.csv")
	// mint_main(100, "./google_sheet/SelfLoveBabe.csv", 100)
	// set_main(number, "SelfLoveBabe", "./google_sheet/SelfLoveBabe.csv")
	// DepositSeriesBody("SelfLoveBabe", number, 50, "", "", false)
	// DepositSeriesComponenet("SelfLoveBabe", "./google_sheet/SelfLoveBabe.csv", number, 50)
	// Mint_Pack(number, "AntiHuman-Demons", 100, 50)
	// Pack_To_NFTStoreFront(number, "SelfLoveBabe", 50, "15.0", 50)
	// Pack_To_NFTStoreFront(number, "AntiHuman-Demons", 50, "7.5", 50)
	// Pack_To_NFTStoreFront(number, "Hearts&Flowers", 50, "30.0", 50)

	//撤销这三个series的nftstorefront
	// fmt.Println(GetNFTStoreFrontPackOnSellListingIDs(SOULMADE_CONTRACT_ADDRESS, SOULMADE_CONTRACT_ADDRESS))
	// fmt.Println(GetListringPackSeriesDetailByListringID(SOULMADE_CONTRACT_ADDRESS, SOULMADE_CONTRACT_ADDRESS, 97251190))
	// UnSell_NFTStoreFront_Series(number, "AntiHuman-Demons", "Hearts&Flowers", "SelfLoveBabe")
	// UnSell_NFTStoreFront_Series(number, "Hearts&Flowers")
	// UnSell_NFTStoreFront_Series(number, "SelfLoveBabe")

	// mysterybox nftstorefront
	// add_to_mysteryBox("Omnist", "./google_sheet/KikoForClaim.csv", 730, 570)
	// MysteryBox_To_NFTStoreFront(number, "Omnist10", "10.0", "")
	// MysteryBox_To_NFTStoreFront(number, "Omnist", "2.0", "")
	// MysteryBox_To_NFTStoreFront(number, "Omnist", "10.0", 300, 700)

	//drop
	// sell_drop("./google_sheet/KikoForClaim.csv", "Omnist", number)
	// BatchCancelSellingComponent()

	// FreeClaim("./google_sheet/KikoForClaim.csv", "Omnist")
	// FreeClaimAddAmount("Omnist", 1000)

	// TranferNftByIpfs(number, "QmNUQG1KDQbmLQpt6JJZ5NMcwdqkV2hDZSkmHVvsZCJpo9", "0x91b508315be4545c", 1, "SoulMadeComponent")
	// MultiPeopleTranferIPFSNFT(number, "QmdpdatvL9J5woxmZCavW3WPUbbGTdp5wicLswU1qMvaKF", []string{"0xcaea391d9fa0e9df", "0xaaaa"}, []int{3, 2})

	// PeliFriendSell()
	// PeliTransfer("AntiHuman-Demons")
	// verify()

	// GiveawayRandomOmnist([]string{"0x624f20b8670d9ed5"}, []int{1})

	// GiveawayRandom3D([]string{"0x0ae196a000170b10"}, []int{1})

	// sell_drop("./google_sheet/Charles.csv", "Charles-Mastery", number)
	// FreeClaim("./google_sheet/Charles.csv", "Charles-Mastery", true, false)
	// AbandonMain(number, "Charles-Mastery")
	// AbandomComponent(number, "Charles-Mastery")

	// mint_component(number, "./google_sheet/Antihuman.csv")
	// mint_main(number, "./google_sheet/Antihuman.csv", 50)
	// DepositSeriesBody("AntiHuman-Demons", number, 50)
	// SetMainByBody(number, "AntiHuman-Demons")
	// set_main(100, "AntiHuman-Demons", "./google_sheet/Antihuman.csv")
	// DepositSeriesComponenet("AntiHuman-Demons", "./google_sheet/Antihuman.csv", number, 50)
	// AddToMysteryBoxAntihuman("AntiHuman-Demons", "./google_sheet/Antihuman.csv")
	// MysteryBox_To_NFTStoreFront(number, "AntiHuman-Demons", "58.0", "")

	// CancelSellNFTStoreFrontBySeries(number, "AntiHuman-Demons")
	// OpenPackForSeries(number, "AntiHuman-Demons")

	// CheckMain("AntiHuman-Demons")

}
