import React, { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import { useUserAssets } from '../../hooks/queries';
import { BackButton } from '../../common/BackButton/BackButton';
import { AssetEditCard } from '../../common/AssetEditCard/AssetEditCard';
import { SoulMadeService } from '../../services/SoulMadeService';
import { StatusProgress } from '../../common/StatusProgress/StatusProgress';
import styles from './ProfileAssetScreen.module.scss';
import { useAuthSession } from '../../hooks/useAuthSession';
import { Button, Dialog, DialogActions, DialogContent, DialogTitle, TextField } from '@mui/material';
import { LoadingButton } from '@mui/lab';

const soulMadeService = SoulMadeService.getInstance();

const MetadataEditDialog = ({ open = false, onClose, metadata, onUpdate, updateLoading, onMetadataChange }) => {
  return (
    <Dialog open={open} onClose={onClose}>
      <DialogTitle>Edit metadata</DialogTitle>
      <DialogContent style={{ paddingTop: 8 }}>
        <TextField
          label="Name"
          fullWidth
          margin="normal"
          value={metadata?.name}
          onChange={e => onMetadataChange({ ...metadata, name: e.target.value })}
          variant="outlined"
        />
        <TextField
          label="Description"
          fullWidth
          margin="normal"
          value={metadata?.description}
          onChange={e => onMetadataChange({ ...metadata, description: e.target.value })}
          variant="outlined"
        />
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose} className={styles.editCloseBtn} variant="outlined">
          Cancel
        </Button>
        <LoadingButton loading={updateLoading} autoFocus onClick={onUpdate} className={styles.editOpenBtn}>
          Update
        </LoadingButton>
      </DialogActions>
    </Dialog>
  );
};

export const ProfileAssetScreen = () => {
  const router = useRouter();
  const { series, ipfsHash, nftType, assetId } = router.query;
  const user = useAuthSession();
  const { data: userAssets = [] } = useUserAssets();

  const asset = userAssets.find(
    x =>
      x.series === series &&
      x.nftType === nftType &&
      (x.mainDetail?.ipfsHash === ipfsHash || x.componentDetail?.ipfsHash === ipfsHash) &&
      assetId === `${x.id}`,
  );

  console.log(asset);

  const [previewAsset, setPreviewAsset] = useState(asset);

  useEffect(() => {
    if (asset) {
      setPreviewAsset(asset);
    }
  }, [asset]);

  const [composerOpen, setComposerOpen] = useState(false);
  const [mintLoading, setMintLoading] = useState(false);

  const [sellAmount, setSellAmount] = useState();
  const [sellLoading, setSellLoading] = useState();

  const [transferAddress, setTransferAddress] = useState();
  const [transferLoading, setTransferLoading] = useState();

  const { mutate } = useUserAssets();
  const [transaction, setTransaction] = useState();

  const [updateMetadataLoading, setUpdateMetadataLoading] = useState(false);
  const [updateMetadataDialogOpen, setUpdateMetadataDialogOpen] = useState(false);
  const [metadata, setMetadata] = useState();

  const userElementAssets = userAssets
    .filter(x => x.nftType === 'SoulMadeComponent' && x.componentDetail.series === series)
    .map(x => x.componentDetail);

  const elementAssets = asset?.mainDetail?.componentDetails.filter(x => x.category !== 'Body') || [];

  const handleEditClick = () => {
    setComposerOpen(true);
  };

  const handleMetadataEditClick = () => {
    const assetDetail = asset?.mainDetail || asset?.componentDetail;
    setUpdateMetadataDialogOpen(true);
    setMetadata({
      name: assetDetail.name,
      description: assetDetail.description,
    });
  };

  const handleRemoveElement = assetId => {
    const newPreviewAssets = previewAsset.mainDetail.componentDetails.filter(x => x.id !== assetId);
    const newPreviewAsset = {
      ...previewAsset,
      mainDetail: { ...previewAsset.mainDetail, componentDetails: newPreviewAssets },
    };
    setPreviewAsset(newPreviewAsset);
  };

  const handleSelectElement = assetId => {
    const previewAssetIds = previewAsset.mainDetail.componentDetails.map(x => x.id);
    const previewAssetCategories = previewAsset.mainDetail.componentDetails.map(x => x.category);
    if (previewAssetIds.includes(assetId)) {
      return;
    }

    const clickedAsset = [...userElementAssets, ...elementAssets].find(x => x.id === assetId);

    if (previewAssetCategories.includes(clickedAsset.category)) {
      // change asset in the category
      const restAssets = previewAsset.mainDetail.componentDetails.filter(x => x.category !== clickedAsset.category);
      const newPreviewAsset = {
        ...previewAsset,
        mainDetail: {
          ...previewAsset.mainDetail,
          componentDetails: [...restAssets, clickedAsset],
        },
      };
      setPreviewAsset(newPreviewAsset);
    } else {
      // add asset to the category
      const newPreviewAsset = {
        ...previewAsset,
        mainDetail: {
          ...previewAsset.mainDetail,
          componentDetails: [...previewAsset.mainDetail.componentDetails, clickedAsset],
        },
      };
      setPreviewAsset(newPreviewAsset);
    }
  };

  const handleSellAmountChange = e => {
    const value = e.target.value;
    setSellAmount(value);
  };

  const handleAssetSell = async () => {
    if (!asset.id || !sellAmount) {
      return;
    }
    setSellLoading(true);

    try {
      const res = await soulMadeService.sellAsset(asset.id, Number(sellAmount).toFixed(2), asset.nftType);
      res.subscribe(async t => {
        setTransaction(t);
        if (t.errorMessage) {
          setSellLoading(false);
        } else {
          if (t.status === 4) {
            setSellLoading(false);
            setTransaction(undefined);
            await mutate();
            router.push(`/profile/${user?.addr}/marketplace`);
          }
        }
      });
    } catch (error) {
      console.error(error);
      setSellLoading(false);
    } finally {
    }
  };

  const handleTransferAddressChange = e => {
    const value = e.target.value;
    setTransferAddress(value);
  };

  const handleAssetTransfer = async () => {
    if (!asset.id || !transferAddress) {
      return;
    }
    setTransferLoading(true);

    try {
      const res = await soulMadeService.assetTransfer(asset.id, transferAddress, asset.nftType);
      res.subscribe(async t => {
        setTransaction(t);
        if (t.errorMessage) {
          setTransferLoading(false);
        } else {
          if (t.status === 4) {
            setTransferLoading(false);
            setTransaction(undefined);
            await mutate();
            router.push(`/profile`);
          }
        }
      });
    } catch (error) {
      console.error(error);
      setTransferLoading(false);
    } finally {
    }
  };

  const handleCancel = () => {
    setPreviewAsset(asset);
    setComposerOpen(false);
  };

  const handleMint = async () => {
    setMintLoading(true);
    const defaultAssets = asset.mainDetail.componentDetails;
    const previewAssets = previewAsset.mainDetail.componentDetails;

    const defaultAssetsDict = defaultAssets.reduce((acc, asset) => {
      acc[asset.category] = asset.id;
      return acc;
    }, {});
    const previewAssetsDict = previewAssets.reduce((acc, asset) => {
      acc[asset.category] = asset.id;
      return acc;
    }, {});

    const result = [];
    for (let category in previewAssetsDict) {
      if (!defaultAssetsDict[category]) {
        result.push({ key: category, value: previewAssetsDict[category] });
      } else {
        if (defaultAssetsDict[category] !== previewAssetsDict[category]) {
          result.push({ key: category, value: previewAssetsDict[category] });
        }
      }
    }

    for (let category in defaultAssetsDict) {
      if (!previewAssetsDict[category]) {
        result.push({ key: category, value: null });
      }
    }

    const mergeImagesRes = await fetch('/api/mergeImages', {
      method: 'POST',
      mode: 'cors',
      cache: 'no-cache',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ assets: previewAssets }),
    });

    const { ipfsHash } = await mergeImagesRes.json();

    console.log(ipfsHash);

    try {
      const res = await soulMadeService.updateComponents(result, asset.id, ipfsHash);
      res.subscribe(t => {
        setTransaction(t);
        if (t.errorMessage) {
          setMintLoading(false);
        } else {
          if (t.status === 4) {
            setMintLoading(false);
            mutate();
            setTransaction(undefined);
            router.replace(`/profile/product/${series}/${ipfsHash}/${assetId}?nftType=${nftType}`);
          }
        }
        console.log(t);
      });
    } catch (e) {
      console.error(e);
      setMintBtnLoading(false);
    } finally {
      // setMintBtnLoading(false);
    }
  };

  const handleUpdateMetadata = async info => {
    setUpdateMetadataLoading(true);

    try {
      const assetDetail = asset.mainDetail ? asset.mainDetail : asset.componentDetail;
      const res = await soulMadeService.updateInfo(assetDetail.id, info.name, info.description);
      res.subscribe(t => {
        setTransaction(t);
        if (t.errorMessage) {
          setUpdateMetadataLoading(false);
        } else {
          if (t.status === 4) {
            setUpdateMetadataLoading(false);
            setUpdateMetadataDialogOpen(false);
            setTransaction(undefined);
            mutate();
          }
        }
        console.log(t);
      });
    } catch (error) {
      console.error(error);
      setUpdateMetadataLoading(false);
    } finally {
    }
  };

  return (
    <div className={styles.profileAssetScreen}>
      {transaction ? <StatusProgress transaction={transaction} /> : null}

      <MetadataEditDialog
        updateLoading={updateMetadataLoading}
        open={updateMetadataDialogOpen}
        onClose={() => setUpdateMetadataDialogOpen(false)}
        metadata={metadata}
        onMetadataChange={setMetadata}
        onUpdate={() => handleUpdateMetadata(metadata)}
      />

      <div className={styles.detail}>
        {asset ? (
          <>
            {/* <div className={styles.nav}>
              <BackButton url={`/profile/${user?.addr}`} />
            </div> */}
            <AssetEditCard
              asset={asset}
              previewAsset={previewAsset}
              allAssets={[...userElementAssets, ...elementAssets]}
              composerOpen={composerOpen}
              mintLoading={mintLoading}
              sellLoading={sellLoading}
              transferLoading={transferLoading}
              onEditClick={handleEditClick}
              onCancelClick={handleCancel}
              onMintClick={handleMint}
              onElementRemove={handleRemoveElement}
              onElementSelect={handleSelectElement}
              onSellAmountChange={handleSellAmountChange}
              onAssetSell={handleAssetSell}
              onMetadataEditClick={handleMetadataEditClick}
              onAssetTransfer={handleAssetTransfer}
              onTransferAddressChange={handleTransferAddressChange}
            />
          </>
        ) : null}
      </div>
    </div>
  );
};
