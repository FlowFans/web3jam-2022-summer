import { Tab, Tabs } from '@mui/material';
import groupBy from 'lodash.groupby';
import React from 'react';
import cx from 'classnames';
import { CloseIcon } from './CloseIcon';
import styles from './AssetTabs.module.scss';
import { LazyImage } from '../LazyImage';

const TabPanel = props => {
  const { children, value, category, ...other } = props;

  return (
    <div hidden={value !== category} {...other} style={{ height: '100%' }}>
      {value === category && <div className={styles.panel}>{children}</div>}
    </div>
  );
};

export const AssetTabs = ({ selectedAssets, assets, onSelect, onRemove, onTabChange }) => {
  const categoryAssets = Object.entries(groupBy(assets, 'category')).map(x => ({ category: x[0], assets: x[1] }));
  const [value, setValue] = React.useState(categoryAssets[0]?.category);

  const selectedAssetIds = selectedAssets.map(x => x.id);

  const handleChange = (event, newValue) => {
    setValue(newValue);
    onTabChange(newValue);
  };

  return (
    <div className={styles.assetTabs}>
      <Tabs
        value={value}
        onChange={handleChange}
        classes={{ indicator: styles.indicator }}
        scrollButtons="auto"
        variant="scrollable"
      >
        {categoryAssets.map(x => {
          return (
            <Tab
              key={x.category}
              value={x.category}
              label={x.category}
              className={styles.tab}
              disableRipple={true}
              classes={{ selected: styles.selected }}
            />
          );
        })}
      </Tabs>

      {categoryAssets.map(x => {
        return (
          <TabPanel key={x.category} category={x.category} value={value}>
            <div className={styles.imgs}>
              {x.assets.map(asset => {
                const isSelected = selectedAssetIds.includes(asset.id);

                return (
                  <div
                    key={asset.id}
                    className={cx(styles.img, { [styles.selected]: isSelected })}
                    onClick={() => onSelect(asset.id)}
                  >
                    {isSelected ? <CloseIcon onClick={() => onRemove(asset.id)} /> : null}
                    <LazyImage asset={asset} />
                  </div>
                );
              })}
            </div>
          </TabPanel>
        );
      })}
    </div>
  );
};
