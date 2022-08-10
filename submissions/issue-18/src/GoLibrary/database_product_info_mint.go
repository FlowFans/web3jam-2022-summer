package main

import (
	"database/sql"
	"fmt"

	_ "github.com/lib/pq"
)

func PostGreSqlDemo() *sql.DB {
	var dbName string = ""
	var dbUser string = ""
	var dbPassword string = ""
	var dbHost string = ""
	var dbPort int = 5432

	dsn := fmt.Sprintf("host=%s port=%d user=%s password=%s dbname=%s",
		dbHost, dbPort, dbUser, dbPassword, dbName,
	)
	db, err := sql.Open("postgres", dsn)
	if err != nil {
		panic(err)
	}
	err = db.Ping()
	if err != nil {
		panic(err)
	}
	return db
}

type ProductInfo struct {
	creator_name  string
	series        string
	name          string
	description   string
	categorynosql string
	layernosql    int64
	category      sql.NullString
	layer         sql.NullInt64
	//mint_component用到
	start_ediction int
	end_edition    int
	max_edition    int
	ipfs_hash      string
	profile        sql.NullString
	membership     string
	is_pack        bool
	price          sql.NullFloat64
	//mint_main的时候会用到
	main_number int
}

func GetProductInfo() []ProductInfo {
	db := PostGreSqlDemo()
	rows, err := db.Query("select * from \"test\".product_info order by profile, ipfs_hash")
	if err != nil {
		panic(err)
	}
	defer rows.Close()
	var product_info []ProductInfo
	for rows.Next() {
		var product ProductInfo
		err = rows.Scan(&product.creator_name, &product.series, &product.name, &product.description, &product.category, &product.layer, &product.max_edition, &product.ipfs_hash, &product.profile, &product.membership, &product.is_pack, &product.price)
		if err != nil {
			panic(err)
		}
		product_info = append(product_info, product)
	}
	return product_info
}

func Product_Info_Component_Recombine(product_info_list []ProductInfo) []ProductInfo {
	var paramter []ProductInfo
	for _, product_info := range product_info_list {
		if product_info.layer.Int64 != 0 {
			var j int
			maxEdiction := product_info.max_edition
			for j = 0; j < maxEdiction/100; j++ {
				var product_slice ProductInfo
				product_slice.creator_name = product_info.creator_name
				product_slice.series = product_info.series
				product_slice.name = product_info.name
				product_slice.description = product_info.description
				product_slice.category = product_info.category
				product_slice.layer = product_info.layer
				product_slice.start_ediction = j*100 + 1
				product_slice.end_edition = (j + 1) * 100
				product_slice.max_edition = product_info.max_edition
				product_slice.ipfs_hash = product_info.ipfs_hash
				product_slice.profile = product_info.profile
				product_slice.membership = product_info.membership
				product_slice.is_pack = product_info.is_pack
				product_slice.price = product_info.price
				paramter = append(paramter, product_slice)
			}
			//如果是不是整百,
			if j*100 < maxEdiction {
				var product_slice ProductInfo
				product_slice.creator_name = product_info.creator_name
				product_slice.series = product_info.series
				product_slice.name = product_info.name
				product_slice.description = product_info.description
				product_slice.category = product_info.category
				product_slice.layer = product_info.layer
				product_slice.start_ediction = j*100 + 1
				product_slice.end_edition = product_info.max_edition
				product_slice.max_edition = product_info.max_edition
				product_slice.ipfs_hash = product_info.ipfs_hash
				product_slice.profile = product_info.profile
				product_slice.membership = product_info.membership
				product_slice.is_pack = product_info.is_pack
				product_slice.price = product_info.price
				paramter = append(paramter, product_slice)
			}
		}
	}
	return paramter
}

//Mint Main这一步是按照Body来的, 和Profile无关, 在下面设置的时候, 才设置profile相关的
func Product_Info_Main_Recombine(product_info_list []ProductInfo) []ProductInfo {
	var paramter []ProductInfo
	for _, product_info := range product_info_list {
		if product_info.category.String == "Body" {
			var j int
			for j = 0; j < product_info.max_edition/100; j++ {
				var product_slice ProductInfo
				product_slice.main_number = 100
				product_slice.series = product_info.series
				paramter = append(paramter, product_slice)
			}
			//如果不是max_edition不是整百
			if j*100 < product_info.max_edition {
				var product_slice ProductInfo
				product_slice.main_number = product_info.max_edition % 100
				product_slice.series = product_info.series
				paramter = append(paramter, product_slice)
			}
		}
	}
	return paramter
}

func BatchProductMintComponents(publicKeyNumber int) {
	product_info_list := GetProductInfo()
	info := Product_Info_Component_Recombine(product_info_list)
	for i := 0; i < len(info); i++ {
		keyIndex := i % publicKeyNumber
		BatchMintComponent(keyIndex, info[i])
	}
}

//弥补多Mint的4000个Kiko和400个Arilf
func MakeUp_Kiko_Arilf(info []ProductInfo) []ProductInfo {
	Kiko := 0
	Arilf := 0
	//先删Kiko
	for index, product_info := range info {
		if product_info.series == "kiko-witch" {
			if Kiko < 40 {
				info = append(info[:index], info[index+1:]...)
				Kiko += 1
			}
		} else if product_info.series == "Disordered-FengFeng" {
			if Arilf < 4 {
				info = append(info[:index], info[index+1:]...)
				Arilf += 1
			}
		}
	}
	fmt.Println(Kiko, Arilf)
	return info
}

//Mint完Main之后, 需要去Set一下
// func Batch_Product_Mint_Main(publicKeyNumber int) {
// 	product_info_list := GetProductInfo()
// 	info := Product_Info_Main_Recombine(product_info_list)
// 	//正常来说 不需要makeup
// 	//info = MakeUp_Kiko_Arilf(info)
// 	//fmt.Println(info)
// 	for i := 0; i < len(info); i++ {
// 		// fmt.Println(i, info[i])
// 		keyIndex := i % publicKeyNumber
// 		BatchMintMain(keyIndex, info[i])
// 	}
// }

func Batch_Set_Main(publicKeyNumber int) {
	info := Combine_Chain_Database_Main_Detail()
	for i := 0; i < len(info); i++ {
		keyIndex := i % publicKeyNumber
		BatchSetMain(keyIndex, info[i])
	}
}
