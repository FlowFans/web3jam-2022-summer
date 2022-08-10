import { Box, CircularProgress, Tab, Tabs, Typography } from '@mui/material';
import groupBy from 'lodash.groupby';
import { useRouter } from 'next/router';
import React from 'react';
import { FilterButton } from '../../common/FilterButton/FilterButton';
import { ImageGrid } from '../../common/ImageGrid/ImageGrid';
import { Ribbon } from '../../common/Ribbon/Ribbon';
import { useUserAssetsByAddress, useUserSales } from '../../hooks/queries';
import styles from './ProfileOtherScreen.module.scss';

function TabPanel(props) {
  const { children, value, index, ...other } = props;

  return (
    <div hidden={value !== index} {...other}>
      {value === index && children}
    </div>
  );
}

export const ProfileOtherScreen = () => {
  const router = useRouter();
  const { address } = router.query;

  const [value, setValue] = React.useState(0);
  const [selectedSeries, setSelectedSeries] = React.useState();

  const seriesFilter = asset => {
    if (!selectedSeries) {
      return true;
    }
    const series = asset?.series || asset.mainDetail?.series || asset.componentDetail?.series;
    return series === selectedSeries;
  };

  const { data: userSales = [], loading: userSalesLoading } = useUserSales(address);
  const { data: userAssets = [], loading: userAssetsLoading } = useUserAssetsByAddress(address);

  const mainUserSales = userSales.filter(x => x?.nftType === 'SoulMadeMain').filter(seriesFilter);
  const componentUserSales = userSales.filter(x => x?.nftType !== 'SoulMadeMain').filter(seriesFilter);
  const groupedUserSales = groupBy(componentUserSales, 'componentDetail.category');

  const mainUserAssets = userAssets.filter(x => x?.nftType === 'SoulMadeMain').filter(seriesFilter);
  const componentUserAssets = userAssets.filter(x => x?.nftType !== 'SoulMadeMain').filter(seriesFilter);
  const groupedUserAssets = groupBy(componentUserAssets, 'componentDetail.category');

  const handleChange = (event, newValue) => {
    setValue(newValue);
  };

  const handleFilterClick = series => {
    if (selectedSeries === series) {
      setSelectedSeries(undefined);
    } else {
      setSelectedSeries(series);
    }
  };

  return (
    <div className={styles.profileOtherScreen}>
      <div className={styles.title}>{address}</div>

      <Box sx={{ borderBottom: 1, borderColor: 'divider', width: '100%' }}>
        <Tabs value={value} onChange={handleChange} centered>
          <Tab label="Sales" />
          <Tab label="Collections" />
        </Tabs>
      </Box>

      <TabPanel className={styles.tabPanel} value={value} index={0}>
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
          {userSalesLoading ? (
            <CircularProgress />
          ) : (
            <>
              {mainUserSales.length > 0 && (
                <>
                  <div className={styles.ribbon}>
                    <Ribbon text="Sets" />
                  </div>
                  <ImageGrid key={selectedSeries} title="" assets={mainUserSales} />
                </>
              )}

              {componentUserSales.length > 0 && (
                <>
                  <div className={styles.ribbon}>
                    <Ribbon text="Elements" />
                  </div>
                  {Object.keys(groupedUserSales).map(category => {
                    return groupedUserSales[category].length > 0 ? (
                      <ImageGrid key={category} title={category} assets={groupedUserSales[category]} />
                    ) : null;
                  })}
                </>
              )}
            </>
          )}
        </div>
      </TabPanel>
      <TabPanel className={styles.tabPanel} value={value} index={1}>
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
          {userAssetsLoading ? (
            <CircularProgress />
          ) : (
            <>
              {mainUserAssets.length > 0 && (
                <>
                  <div className={styles.ribbon}>
                    <Ribbon text="Sets" />
                  </div>
                  <ImageGrid key={selectedSeries} title="" assets={mainUserAssets} />
                </>
              )}

              {componentUserAssets.length > 0 && (
                <>
                  <div className={styles.ribbon}>
                    <Ribbon text="Elements" />
                  </div>
                  {Object.keys(groupedUserAssets).map(category => {
                    return groupedUserAssets[category].length > 0 ? (
                      <ImageGrid key={category} title={category} assets={groupedUserAssets[category]} />
                    ) : null;
                  })}
                </>
              )}
            </>
          )}
        </div>
      </TabPanel>
    </div>
  );
};
