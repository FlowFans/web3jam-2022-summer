import { CircularProgress } from '@mui/material';
import groupBy from 'lodash.groupby';
import React from 'react';
import { FilterButton } from '../../common/FilterButton/FilterButton';
import { ImageGrid } from '../../common/ImageGrid/ImageGrid';
import { Ribbon } from '../../common/Ribbon/Ribbon';
import { useBlockMarketplaceComponentProducts, useBlockMarketplaceMainProducts } from '../../hooks/queries';
import styles from './MarketplaceScreen.module.scss';

export const MarketplaceScreen = () => {
  const [selectedSeries, setSelectedSeries] = React.useState();

  const seriesFilter = asset => {
    if (!selectedSeries) {
      return true;
    }
    const series = asset.saleData?.mainDetail?.series || asset.saleData?.componentDetail?.series;
    return series === selectedSeries;
  };

  // 这里获取的其实不是product，只是从一串assets中取出了一项，当成product来用
  // 所以Drops页面不能显示价格，因为product没有价格，这里的价格是从assets中取出的
  // 原因是我们没有更好的数据结构来表示product
  // 目前很多组件都基于asset的数据结构构造
  // 如果自己构造product的数据结构，那么就需要对大量组件进行重构
  const { data: mainAssets = [], loading: mainLoading } = useBlockMarketplaceMainProducts();
  const { data: componentAssets = [], loading: componentLoading } = useBlockMarketplaceComponentProducts();

  const baseAssets = mainAssets.filter(x => x.address !== '0xb7c0554713fe2a52').filter(seriesFilter);
  const elementAssets = componentAssets.filter(x => x.address !== '0xb7c0554713fe2a52').filter(seriesFilter);

  const groupedAssets = groupBy(componentAssets, 'saleData.componentDetail.category');

  const handleFilterClick = series => {
    if (selectedSeries === series) {
      setSelectedSeries(undefined);
    } else {
      setSelectedSeries(series);
    }
  };

  return (
    <div className={styles.marketplaceScreen}>
      <div className={styles.title}>Marketplace</div>

      <div className={styles.filter}>
        <FilterButton
          onClick={handleFilterClick}
          selected={selectedSeries === 'AntiHuman-Demons'}
          series="AntiHuman-Demons"
          imgSrc="/new-design/images/antihuman-filter.png"
        />

        <FilterButton
          onClick={handleFilterClick}
          selected={selectedSeries === 'Charles-Mastery'}
          series="Charles-Mastery"
          imgSrc="/new-design/images/charles-filter.png"
        />

        <FilterButton
          onClick={handleFilterClick}
          selected={selectedSeries === 'Omnist'}
          series="Omnist"
          imgSrc="/new-design/images/kiko-filter.png"
        />
      </div>

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
                <ImageGrid key={selectedSeries} title="" assets={baseAssets.map(x => x.saleData)} />
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
