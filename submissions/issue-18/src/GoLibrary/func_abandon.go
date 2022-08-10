package main

func AbandonMain(publicKeyNumber int, series string) {
	ids, _ := GetSeriesMainIDArrayAndDetail(series, false, SOULMADE_CONTRACT_ADDRESS)
	i := 0
	batch := 50
	for i < len(ids)/batch {
		ids_batch := ids[i*batch : (i+1)*batch]
		TransferNFTTx(i%publicKeyNumber, ids_batch, "0xc2ac4a40ad8f2c7f", "SoulMadeMain")
		i += 1
	}
	if len(ids)%batch > 0 {
		ids_batch := ids[len(ids)/batch*batch:]
		TransferNFTTx(i%publicKeyNumber, ids_batch, "0xc2ac4a40ad8f2c7f", "SoulMadeMain")

	}
}

func AbandomComponent(publicKeyNumber int, series string) {
	ids, _ := GetSeriesComponentIDArrayAndDetail(series, "", SOULMADE_CONTRACT_ADDRESS)
	i := 0
	batch := 50
	for i < len(ids)/batch {
		ids_batch := ids[i*batch : (i+1)*batch]
		TransferNFTTx(i%publicKeyNumber, ids_batch, "0xc2ac4a40ad8f2c7f", "SoulMadeComponent")
		i += 1
	}
	if len(ids)%batch > 0 {
		ids_batch := ids[len(ids)/batch*batch:]
		TransferNFTTx(i%publicKeyNumber, ids_batch, "0xc2ac4a40ad8f2c7f", "SoulMadeComponent")

	}
}
