import { CircularProgress } from '@mui/material';
import groupBy from 'lodash.groupby';
import { useRouter } from 'next/router';
import React from 'react';
import { FilterButton } from '../../common/FilterButton/FilterButton';
import { ImageGrid } from '../../common/ImageGrid/ImageGrid';
import { Ribbon } from '../../common/Ribbon/Ribbon';
import { useUserSales } from '../../hooks/queries';
import { useAuthSession } from '../../hooks/useAuthSession';
import styles from './ProfileMarketplaceScreen.module.scss';

export const ProfileMarketplaceScreen = () => {
  const user = useAuthSession();
  const { data: assets = [], loading } = useUserSales(user?.addr);

  const [selectedSeries, setSelectedSeries] = React.useState();

  const seriesFilter = asset => {
    if (!selectedSeries) {
      return true;
    }
    const series = asset?.series || asset.mainDetail?.series || asset.componentDetail?.series;
    return series === selectedSeries;
  };

  const mainAssets = assets.filter(x => x.nftType === 'SoulMadeMain').filter(seriesFilter);
  const componentAssets = assets.filter(x => x.nftType !== 'SoulMadeMain').filter(seriesFilter);

  const groupedAssets = groupBy(componentAssets, 'componentDetail.category');

  // const title = user?.addr === address ? 'My Sales' : `${address}'s Sales`;

  const handleFilterClick = series => {
    if (selectedSeries === series) {
      setSelectedSeries(undefined);
    } else {
      setSelectedSeries(series);
    }
  };

  return (
    <div className={styles.profileMarketplaceScreen}>
      <div className={styles.title}>My Sales</div>

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
        {loading ? (
          <CircularProgress />
        ) : (
          <>
            {mainAssets.length > 0 && (
              <>
                <div className={styles.ribbon}>
                  <Ribbon text="Sets" />
                </div>
                {/* todo(Guisong): remove Body title for now */}
                <ImageGrid key={selectedSeries} title="" assets={mainAssets} />
              </>
            )}

            {componentAssets.length > 0 && (
              <>
                <div className={styles.ribbon}>
                  <Ribbon text="Elements" />
                </div>
                {Object.keys(groupedAssets).map(category => {
                  return groupedAssets[category].length > 0 ? (
                    <ImageGrid key={category} title={category} assets={groupedAssets[category]} />
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
