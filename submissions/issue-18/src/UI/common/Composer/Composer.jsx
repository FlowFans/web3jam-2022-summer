import { Tab, Tabs } from '@mui/material';
import React from 'react';
import { AssetTabs } from '../AssetTabs/AssetTabs';
import { LazyImage } from '../LazyImage';
import styles from './Composer.module.scss';

function TabPanel(props) {
  const { children, value, index, ...other } = props;

  return (
    <div hidden={value !== index} {...other} style={{ height: '100%' }}>
      {value === index && <div className={styles.panel}>{children}</div>}
    </div>
  );
}

export const Composer = ({ asset, allAssets, onRemove, onSelect }) => {
  const [value, setValue] = React.useState(0);

  const [selectedAsset, setSelectedAsset] = React.useState(
    asset.mainDetail.componentDetails[0].category === 'Background',
  );

  const handleChange = (event, newValue) => {
    setValue(newValue);
  };

  const bodyAsset = asset.mainDetail.componentDetails.find(x => x.category === 'Body');
  const elementAssets = asset.mainDetail.componentDetails.filter(x => x.category !== 'Body');

  const handleAssetSelect = assetId => {
    const selectedA = allAssets.find(x => x.id === assetId);
    setSelectedAsset(selectedA);
    onSelect(assetId);
  };

  const handleAssetRemove = assetId => {
    setSelectedAsset(undefined);
    onRemove(assetId);
  };

  const handleCategoryTabChange = category => {
    const selectedA = asset.mainDetail.componentDetails.find(x => x.category === category);
    setSelectedAsset(selectedA);
  };

  return (
    <div className={styles.composer}>
      <Tabs value={value} onChange={handleChange} classes={{ root: styles.root, indicator: styles.indicator }}>
        <Tab label="Base" className={styles.tab} disableRipple={true} classes={{ selected: styles.selected }} />
        <Tab label="Elements" className={styles.tab} disableRipple={true} classes={{ selected: styles.selected }} />
      </Tabs>
      <TabPanel value={value} index={0}>
        <div className={styles.base}>
          <div className={styles.img}>
            <LazyImage asset={bodyAsset} />
          </div>
          <div className={styles.info}>
            <div className={styles.item}>
              <div className={styles.title}>Collection</div>
              <div className={styles.text}>{bodyAsset.series}</div>
            </div>
            <div className={styles.item}>
              <div className={styles.title}>Body</div>
              <div className={styles.text}>{bodyAsset.name}</div>
            </div>
            <div className={styles.item}>
              <div className={styles.title}>Edition</div>
              <div className={styles.text}>{bodyAsset.edition}</div>
            </div>
          </div>
        </div>
      </TabPanel>
      <TabPanel value={value} index={1}>
        <div className={styles.elements}>
          <AssetTabs
            selectedAssets={elementAssets}
            assets={allAssets.sort((a, b) => a.layer - b.layer)}
            onRemove={handleAssetRemove}
            onSelect={handleAssetSelect}
            onTabChange={handleCategoryTabChange}
          />
        </div>

        {selectedAsset ? (
          <div className={styles.info}>
            <div className={styles.item}>
              <div className={styles.title}>Name</div>
              <div className={styles.text}>{selectedAsset.name}</div>
            </div>
            <div className={styles.item}>
              <div className={styles.title}>ID</div>
              <div className={styles.text}>{selectedAsset.id}</div>
            </div>
          </div>
        ) : null}
      </TabPanel>
    </div>
  );
};
