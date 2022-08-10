import React from 'react';
import ModeEditIcon from '@mui/icons-material/ModeEdit';
import AssetImage from '../AssetImage';
import { Composer } from '../Composer/Composer';
import styles from './AssetEditCard.module.scss';
import { Button } from '@mui/material';
import { SellField } from '../SellField/SellField';
import { LoadingButton } from '@mui/lab';
import { NftTransferField } from '../NftTransferField/NftTransferField';

export const AssetEditCard = ({
  asset,
  previewAsset,
  allAssets,
  composerOpen,
  mintLoading,
  sellLoading,
  transferLoading,
  onEditClick,
  onCancelClick,
  onMintClick,
  onElementRemove,
  onElementSelect,
  onMetadataEditClick,
  onSellAmountChange,
  onAssetSell,
  onAssetTransfer,
  onTransferAddressChange,
}) => {
  const assetDetail = asset.mainDetail ? asset.mainDetail : asset.componentDetail;

  return (
    <div className={styles.assetEditCard}>
      <div className={styles.row}>
        <div className={styles.content}>
          <div className={styles.img}>
            <div className={styles.wrapper}>
              {previewAsset ? (
                <AssetImage
                  download={!composerOpen && asset.nftType === 'SoulMadeMain'}
                  asset={previewAsset}
                  isOriginal={asset.nftType === 'SoulMadeMain'}
                  width="100%"
                  height="100%"
                />
              ) : null}
            </div>
          </div>
          <div className={styles.editSection}>
            {composerOpen && previewAsset ? (
              <Composer
                asset={previewAsset}
                allAssets={allAssets}
                onRemove={onElementRemove}
                onSelect={onElementSelect}
              />
            ) : (
              <>
                <div className={styles.top}>
                  <div className={styles.subtitle}>
                    <span>{assetDetail.series}</span>
                    {asset.nftType === 'SoulMadeMain' && (
                      <ModeEditIcon onClick={onMetadataEditClick} className={styles.editIcon} />
                    )}
                  </div>
                  <div className={styles.title}>{assetDetail.name}</div>
                  <div className={styles.description}>{assetDetail.description}</div>
                  {/* <div className={styles.userInfo}>
                    <div className={styles.creator}>
                      <label>Creator</label>
                      <span>Maja Johansson</span>
                    </div>
                    <div className={styles.owner}>
                      <label>Owner</label>
                      <span>Maja Johansson</span>
                    </div>
                  </div> */}
                </div>
                <div className={styles.bottom}>
                  <div className={styles.bottom1}>
                    {asset.nftType === 'SoulMadeMain' ? (
                      <div className={styles.infoActions}>
                        <Button className={styles.btn} onClick={onEditClick}>
                          Edit
                        </Button>
                      </div>
                    ) : null}
                    <SellField onChange={onSellAmountChange} onSell={onAssetSell} loading={sellLoading} />
                  </div>

                  <div
                    className={styles.bottom2}
                    style={{ justifyContent: asset.nftType === 'SoulMadeMain' ? 'end' : 'inherit' }}
                  >
                    <NftTransferField
                      onChange={onTransferAddressChange}
                      onTransfer={onAssetTransfer}
                      loading={transferLoading}
                    />
                  </div>
                </div>
              </>
            )}
          </div>
        </div>
      </div>

      {composerOpen ? (
        <div className={styles.composer}>
          <div className={styles.placeholder} />
          <div className={styles.composerActions}>
            <Button sx={{ marginRight: '8px', flex: 1, padding: '15px 0' }} variant="outlined" onClick={onCancelClick}>
              Cancel
            </Button>
            <LoadingButton
              sx={{ marginLeft: '8px', flex: 1, padding: '15px 0' }}
              loading={mintLoading}
              onClick={onMintClick}
            >
              Update
            </LoadingButton>
          </div>
        </div>
      ) : null}
    </div>
  );
};
