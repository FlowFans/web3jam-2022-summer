import { useRouter } from 'next/router';
import React, { useEffect, useMemo, useState } from 'react';
import { CircularProgress } from '@mui/material';
import { AssetPurchaseCard } from '../../common/AssetPurchaseCard/AssetPurchaseCard';
import { BackButton } from '../../common/BackButton/BackButton';
import { StatusProgress } from '../../common/StatusProgress/StatusProgress';
import { SoulMadeService } from '../../services/SoulMadeService';
import { useBlockDropComponentAssets, useBlockDropMainAssets } from '../../hooks/queries';
import styles from './DropsAssetScreen.module.scss';

const soulMadeService = SoulMadeService.getInstance();

export const DropsAssetScreen = () => {
  const router = useRouter();
  const { series, ipfsHash, nftType, assetId, edition } = router.query;
  const [buyLoading, setBuyLoading] = useState(false);
  const [transaction, setTransaction] = useState();

  const { data: mainAssets = [], loading: mainLoading } = useBlockDropMainAssets(series);
  const { data: componentAssets = [], loading: componentLoading } = useBlockDropComponentAssets(series);

  const allAssets = useMemo(() => [...mainAssets, ...componentAssets], [mainAssets, componentAssets]);
  const loading = mainLoading || componentLoading;

  const productAssets = allAssets
    .filter(x => x.saleData.componentDetail?.ipfsHash === ipfsHash || x.saleData.mainDetail?.ipfsHash === ipfsHash)
    .sort((a, b) => a.saleData?.price - b.saleData?.price);

  const [currentAsset, setCurrentAsset] = useState();

  // when reload, it needs to be set as productAssets[0] is undefined at first render
  useEffect(() => {
    const a =
      productAssets.find(x => {
        return x.nftType === 'SoulMadeMain'
          ? `${x.saleData.id}` === assetId
          : `${x.saleData.componentDetail?.edition}` === edition;
      }) || productAssets[0];
    setCurrentAsset(a);
  }, [assetId, edition, productAssets]);

  const editionCount = productAssets.length;
  const availableItems =
    nftType === 'SoulMadeMain'
      ? productAssets.map(x => ({ left: x.saleData.mainDetail.id, right: x.saleData.price }))
      : productAssets.map(x => ({ left: x.saleData.componentDetail?.edition, right: x.saleData.price }));

  const handleBuy = async value => {
    if (!currentAsset.id) {
      return;
    }

    const series = currentAsset.saleData.mainDetail?.series || currentAsset.saleData.componentDetail?.series;
    const ipfsHash = currentAsset.saleData.mainDetail?.ipfsHash || currentAsset.saleData.componentDetail?.ipfsHash;
    const assetName = currentAsset.saleData.mainDetail?.name || currentAsset.saleData.componentDetail?.name;

    setBuyLoading(true);
    try {
      const res = await soulMadeService.buyAsset(Number(currentAsset.id), nftType, currentAsset.address);
      res.subscribe(t => {
        setTransaction(t);
        if (t.errorMessage) {
          setBuyLoading(false);
        } else {
          if (t.status === 4) {
            setBuyLoading(false);
            router.push(
              `/profile/product/${series}/${ipfsHash}/${currentAsset.id}?nftType=${currentAsset.nftType}&name=${assetName}`,
            );
          }
        }
      });
    } catch (error) {
      console.log(error);
      setBuyLoading(false);
    } finally {
      // setBuyBtnLoading(false);
    }
  };

  const handleItemChange = value => {
    const curAsset = productAssets.find(
      x => (x.saleData.mainDetail?.id || x.saleData.componentDetail?.edition) === value,
    );

    if (!curAsset.id) {
      return;
    }
    const name = curAsset.saleData.mainDetail?.name || curAsset.saleData.componentDetail?.name;
    router.push(
      `/drops/${series}/${ipfsHash}?assetId=${curAsset.saleData.mainDetail?.id}&edition=${curAsset.saleData.componentDetail?.edition}&nftType=${nftType}&name=${name}`,
    );
  };

  return (
    <div className={styles.dropsAssetScreen}>
      {transaction ? <StatusProgress transaction={transaction} /> : null}
      <div className={styles.detail}>
        {loading ? (
          <CircularProgress />
        ) : (
          <>
            {availableItems.length > 0 ? (
              <>
                {/* <div className={styles.nav}>
                  <BackButton onClick={() => router.back()} />
                </div> */}
                <AssetPurchaseCard
                  buyLoading={buyLoading}
                  product={currentAsset}
                  items={availableItems}
                  editionCount={editionCount}
                  onBuy={handleBuy}
                  onSelectItemChange={handleItemChange}
                />
              </>
            ) : null}
          </>
        )}
      </div>
    </div>
  );
};
