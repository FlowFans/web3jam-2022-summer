import { CircularProgress } from '@mui/material';
import groupBy from 'lodash.groupby';
import { useRouter } from 'next/router';
import React from 'react';
import { ImageGrid } from '../../common/ImageGrid/ImageGrid';
import { Ribbon } from '../../common/Ribbon/Ribbon';
import { useBlockDropMainProducts, useBlockDropComponentProducts } from '../../hooks/queries';
import { Introduction } from './components/Introduction/Introduction';
import styles from './DropScreen.module.scss';

export const DropScreen = () => {
  const router = useRouter();
  const { query } = router;

  // 这里获取的其实不是product，只是从一串assets中取出了一项，当成product来用
  // 所以Drops页面不能显示价格，因为product没有价格，这里的价格是从assets中取出的
  // 原因是我们没有更好的数据结构来表示product
  // 目前很多组件都基于asset的数据结构构造
  // 如果自己构造product的数据结构，那么就需要对大量组件进行重构
  const { data: mainAssets = [], loading: mainLoading } = useBlockDropMainProducts(query.series);
  const { data: componentAssets = [], loading: componentLoading } = useBlockDropComponentProducts(query.series);

  const baseAssets = mainAssets.filter(x => {
    const ser = x.saleData.mainDetail?.series || x.saleData.componentDetail?.series;
    return ser === query.series;
  });

  const elementAssets = componentAssets.filter(x => {
    const ser = x.saleData.mainDetail?.series || x.saleData.componentDetail?.series;
    return ser === query.series;
  });

  const groupedAssets = groupBy(elementAssets, 'saleData.componentDetail.category');

  return (
    <div className={styles.dropScreen}>
      <Introduction title={query.title} desc="Creator - Kiko" />

      <div className={styles.assetsSection}>
        {mainLoading || componentLoading ? (
          <CircularProgress />
        ) : (
          <>
            {baseAssets.length > 0 && (
              <>
                <div className={styles.ribbon}>
                  <Ribbon text="Sets" />
                </div>
                {/* todo(Guisong): remove Body title for now */}
                <ImageGrid title="" assets={baseAssets.map(x => x.saleData)} />
              </>
            )}

            {elementAssets.length > 0 && (
              <>
                <div className={styles.ribbon}>
                  <Ribbon text="Elements" />
                </div>
                {Object.keys(groupedAssets).map(category => {
                  return groupedAssets[category].length > 0 ? (
                    <ImageGrid key={category} title={category} assets={groupedAssets[category].map(x => x.saleData)} />
                  ) : null;
                })}
              </>
            )}
          </>
        )}
      </div>
    </div>
  );
};
