import Image from 'next/image';
import React from 'react';
import cx from 'classnames';
import { s3Loader } from '../../config';
import styles from './LazyImage.module.scss';

export const LazyImage = ({ asset, objectFit = 'contain' }) => {
  const [loaded, setLoaded] = React.useState(false);

  if (typeof asset === 'string') {
    return (
      <Image
        className={cx(styles.lazyImage, { [styles.loaded]: loaded })}
        priority={true}
        quality={1}
        src={asset}
        layout="fill"
        objectFit={objectFit}
        alt="image"
        onLoadingComplete={() => {
          setLoaded(true);
        }}
      />
    );
  }

  return (
    <Image
      className={cx(styles.lazyImage, { [styles.loaded]: loaded })}
      priority={true}
      quality={1}
      loader={({ src }) => s3Loader({ series: asset.series, src, isOriginal: asset.nftType === 'SoulMadeMain' })}
      src={asset.ipfsHash}
      layout="fill"
      objectFit={objectFit}
      alt={asset.name}
      onLoadingComplete={() => {
        setLoaded(true);
      }}
    />
  );
};
