import React, { useMemo, useState } from 'react';
import { LazyImage } from '../LazyImage';
import rarityMap from '../../public/ipfs_hash_rarity.json';
import rarityR from '../../public/new-design/images/R.png';
import raritySR from '../../public/new-design/images/SR.png';
import raritySSR from '../../public/new-design/images/SSR.png';
import { Alert, AlertTitle, Fab, Snackbar } from '@mui/material';
import b64toBlob from 'b64-to-blob';
import CloudDownloadOutlinedIcon from '@mui/icons-material/CloudDownloadOutlined';
import { saveAs } from 'file-saver';
import { LoadingGif } from '../LoadingGif/LoadingGif';

import styles from './AssetImage.module.scss';
import { s3 } from '../../services/s3';
import useSWR from 'swr';

const rarityImageMap = { R: rarityR, SR: raritySR, SSR: raritySSR };

const checkImageExists = imagePath => {
  return new Promise((resolve, reject) => {
    s3.listObjectsV2(
      {
        Bucket: process.env.NEXT_PUBLIC_AWS_S3_BUCKET_NAME,
        Prefix: imagePath,
      },
      (err, data) => {
        if (err) {
          reject(err);
        } else {
          const folderExists = data.Contents.length > 0;
          resolve(folderExists);
        }
      },
    );
  });
};

const getImageBlob = imagePath => {
  return new Promise((resolve, reject) => {
    s3.getObject(
      {
        Bucket: process.env.NEXT_PUBLIC_AWS_S3_BUCKET_NAME,
        Key: imagePath,
      },
      async (err, data) => {
        if (err) {
          reject(err);
        } else {
          const buf = data.Body;
          const b64 = buf.toString('base64');
          const blob = b64toBlob(b64, 'image/png');
          resolve(blob);
        }
      },
    );
  });
};

const AssetImage = ({ asset, isOriginal, width, height, rarity, download = false }, ref) => {
  const isMain = asset.nftType === 'SoulMadeMain';
  const formatedAssets = useMemo(
    () => (isMain ? asset.mainDetail.componentDetails : [asset.componentDetail]),
    [asset, isMain],
  );

  const assetImages = formatedAssets
    .sort((a, b) => a.layer - b.layer)
    .map((a, idx) => <LazyImage key={a.id} asset={a} />);

  const rarityLayer = isMain ? null : rarityMap[asset.componentDetail.ipfsHash];

  const [downloading, setDownloading] = useState(false);
  const [alertOpen, setAlertOpen] = useState(false);

  const handleDownload = async () => {
    try {
      const imagePath = `customized/${asset.mainDetail.series.toLocaleLowerCase()}/${asset.mainDetail.ipfsHash}.png`;
      const imageExists = await checkImageExists(imagePath);

      if (!imageExists) {
        setAlertOpen(true);
        return;
      }
      setDownloading(true);
      const blob = await getImageBlob(imagePath);
      saveAs(blob, `${asset.mainDetail.name}.png`);
    } catch (e) {
      console.error(e);
    } finally {
      setDownloading(false);
    }
  };

  return (
    <>
      {downloading && <LoadingGif src="/new-design/gifts/downloading.gif" />}
      <Snackbar
        open={alertOpen}
        autoHideDuration={5000}
        onClose={() => setAlertOpen(false)}
        anchorOrigin={{ vertical: 'top', horizontal: 'center' }}
      >
        <Alert severity="info">
          <AlertTitle>Info</AlertTitle>
          You have to update your nft before downloading.
        </Alert>
      </Snackbar>
      <div
        ref={ref}
        style={{
          display: 'inline-block',
          position: 'relative',
          aspectRatio: '1',
          width,
          height,
          border: `3px solid #2a252c`,
        }}
      >
        {assetImages}
        {rarityLayer ? <LazyImage asset={rarityImageMap[rarityLayer].src} /> : null}

        {download ? (
          <Fab onClick={handleDownload} className={styles.downloadBtn} color="primary">
            <CloudDownloadOutlinedIcon />
          </Fab>
        ) : null}
      </div>
    </>
  );
};

export default React.forwardRef(AssetImage);
