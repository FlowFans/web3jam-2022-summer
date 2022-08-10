import React, { useState } from 'react';
import currency from 'currency.js';
import AssetImage from '../AssetImage';
import { Option, Select } from '../Select/Select';
import styles from './AssetPurchaseCard.module.scss';
import Link from 'next/link';
import { useAuthSession } from '../../hooks/useAuthSession';
import { Button } from '@mui/material';
import { LoadingButton } from '@mui/lab';
import { useRouter } from 'next/router';

export const AssetPurchaseCard = ({
  product,
  onBuy,
  onWithdraw,
  items,
  editionCount,
  buyLoading,
  withdrawLoading,
  onSelectItemChange,
}) => {
  const user = useAuthSession();
  const router = useRouter();
  const { assetId, edition } = router.query;

  const productDetail = product?.saleData?.mainDetail || product?.saleData?.componentDetail;
  const allEditionCount = productDetail?.maxEdition;

  const assetAddress = product?.address;

  const selectedItem = items.find(x => `${x.left}` === edition || `${x.left}` === assetId) || items[0];

  const isOwner = assetAddress === user?.addr;

  const handleChange = value => {
    onSelectItemChange(value);
  };

  return (
    <div className={styles.assetPurchaseCard}>
      <div className={styles.content}>
        <div className={styles.img}>
          <div className={styles.wrapper}>
            {product?.saleData ? (
              <AssetImage
                asset={product.saleData}
                isOriginal={product?.nftType === 'SoulMadeMain'}
                width="100%"
                height="100%"
              />
            ) : null}
          </div>
        </div>
        <div className={styles.info}>
          <div className={styles.top}>
            <div className={styles.subtitle}>{productDetail?.series}</div>
            <div className={styles.title}>{productDetail?.name}</div>
            <div className={styles.price}>{currency(product?.saleData?.price).value} FLOW</div>
            <div className={styles.description}>{productDetail?.description}</div>
            <div className={styles.userInfo}>
              {/* <div className={styles.creator}>
                <label>Creator</label>
                <span>Peili</span>
              </div> */}
              <div className={styles.owner}>
                <label>Owner</label>
                <span>
                  {assetAddress ? (
                    <Link href={isOwner ? '/profile' : `/profile/${assetAddress}`}>
                      {isOwner ? 'Yourself' : assetAddress}
                    </Link>
                  ) : null}
                </span>
              </div>
            </div>
          </div>
          <div className={styles.bottom}>
            <div className={styles.actions}>
              <Select
                label={product?.nftType === 'SoulMadeMain' ? 'NFT ID' : 'NFT EDITION'}
                onChange={handleChange}
                value={selectedItem.left}
                className={styles.select}
              >
                {items.map(x => {
                  return (
                    <Option key={x.left} value={x.left}>
                      <div style={{ display: 'flex', width: '80%', justifyContent: 'space-between' }}>
                        <div>{x.left}</div>
                        <div>{`${currency(x.right).value} FLOW`}</div>
                      </div>
                    </Option>
                  );
                })}
              </Select>
              {assetAddress === user?.addr ? (
                <LoadingButton
                  loading={withdrawLoading}
                  onClick={() => onWithdraw(product.id, product.nftType)}
                  className={styles.btn}
                >
                  Withdraw from Marketplace
                </LoadingButton>
              ) : (
                <LoadingButton loading={buyLoading} onClick={() => onBuy(selectedItem.left)} className={styles.btn}>
                  Buy now
                </LoadingButton>
              )}
            </div>
          </div>
        </div>
      </div>
      {/* <div className={styles.num}>
        {product?.nftType === 'SoulMadeMain' ? null : `${editionCount} left out of ${allEditionCount}`}
      </div> */}
    </div>
  );
};
