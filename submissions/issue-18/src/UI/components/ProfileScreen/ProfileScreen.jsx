import { CircularProgress } from '@mui/material';
import groupBy from 'lodash.groupby';
import React from 'react';
import { FilterButton } from '../../common/FilterButton/FilterButton';
import { ImageGrid } from '../../common/ImageGrid/ImageGrid';
import { Ribbon } from '../../common/Ribbon/Ribbon';
import { useUserAssets } from '../../hooks/queries';
import styles from './ProfileScreen.module.scss';

export const ProfileScreen = () => {
  const { data: userAssets = [], loading } = useUserAssets();

  const [selectedSeries, setSelectedSeries] = React.useState();

  const seriesFilter = asset => {
    if (!selectedSeries) {
      return true;
    }
    const series = asset?.series || asset.mainDetail?.series || asset.componentDetail?.series;
    return series === selectedSeries;
  };

  const mainAssets = userAssets.filter(x => x?.nftType === 'SoulMadeMain').filter(seriesFilter);
  const componentAssets = userAssets.filter(x => x?.nftType !== 'SoulMadeMain').filter(seriesFilter);

  const groupedAssets = groupBy(componentAssets, 'componentDetail.category');

  const handleFilterClick = series => {
    if (selectedSeries === series) {
      setSelectedSeries(undefined);
    } else {
      setSelectedSeries(series);
    }
  };

  return (
    <div className={styles.profileScreen}>
      <div className={styles.title}>MY COLLECTION</div>

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
                {/*<ImageGrid key={selectedSeries} title="Body" assets={mainAssets} />*/}
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
