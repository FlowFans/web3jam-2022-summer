import {
  ImageList,
  ImageListItem,
  ImageListItemBar,
  ListSubheader,
  IconButton,
  useMediaQuery,
  Button,
} from '@mui/material';
import React, { useState } from 'react';
import InfoIcon from '@mui/icons-material/Info';
import Image from 'next/image';
import AssetImage from '../AssetImage';
import { useRouter } from 'next/router';
import Link from 'next/link';

const useImageGridCols = () => {
  const largerThan640 = useMediaQuery('(min-width: 640px)');
  const largerThan768 = useMediaQuery('(min-width: 768px)');
  const largerThan1024 = useMediaQuery('(min-width: 1024px)');
  const largerThan1280 = useMediaQuery('(min-width: 1280px)');

  if (largerThan1280) {
    return 4;
  } else if (largerThan1024) {
    return 3;
  } else if (largerThan768) {
    return 3;
  } else if (largerThan640) {
    return 2;
  }
  return 2;
};

const getAssetLink = (asPath, pathname, nftType, series, ipfsHash, assetId, edition, name) => {
  const queryString = `nftType=${nftType}&name=${encodeURIComponent(name)}`;
  const baseUrl = asPath.split('?')[0];

  switch (pathname) {
    case '/profile':
      return `/profile/product/${series}/${ipfsHash}/${assetId}?${queryString}`;

    case '/profile/marketplace':
      return `/marketplace/${ipfsHash}?assetId=${assetId}&edition=${edition}&${queryString}`;

    case '/profile/[address]':
      return `/marketplace/${ipfsHash}?assetId=${assetId}&edition=${edition}&${queryString}`;

    case '/drops/[series]':
      return `/drops/${series}/${ipfsHash}?assetId=${assetId}&edition=${edition}&${queryString}`;
    default:
      return `${baseUrl}/${ipfsHash}?${queryString}`;
  }
};

export const ImageGrid = ({ title, assets, noPrice = true }) => {
  const router = useRouter();
  const { pathname, asPath } = router;
  const cols = useImageGridCols();

  const [renderedAssets, setRenderedAssets] = useState(assets.slice(0, cols * 2));

  const handleLoadMore = () => {
    setRenderedAssets(assets.slice(0, renderedAssets.length + cols * 2));
  };

  return (
    <div style={{ width: '100%', padding: '0 0 50px', display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
      <ImageList sx={{ width: '100%', padding: '20px', maxWidth: '1024px' }} gap={8} cols={cols}>
        <ImageListItem key="Subheader" cols={cols}>
          <ListSubheader sx={{ backgroundColor: 'transparent', fontSize: '36px', padding: 0 }} component="div">
            {title}
          </ListSubheader>
        </ImageListItem>

        {renderedAssets.map((asset, idx) => {
          const { id, nftType, price } = asset;
          const name = asset.componentDetail?.name || asset.mainDetail?.name;
          const series = asset.componentDetail?.series || asset.mainDetail?.series;
          const ipfsHash = asset.componentDetail?.ipfsHash || asset.mainDetail?.ipfsHash;
          const edition = asset.componentDetail?.edition || asset.mainDetail?.edition;
          const subtitleText = noPrice
            ? edition
              ? `Edition ${edition}`
              : `ID ${id}`
            : `${currency(price).value} FLOW`;
          const assetLink = getAssetLink(asPath, pathname, nftType, series, ipfsHash, id, edition, name);

          return (
            <Link key={asset.id} href={assetLink}>
              <a>
                <ImageListItem key={asset.id} sx={{ aspectRatio: '1' }}>
                  <AssetImage asset={asset} isOriginal={asset.nftType === 'SoulMadeMain'} />

                  <ImageListItemBar
                    title={name}
                    //subtitle={subtitleText}
                    position="below"
                    // actionIcon={
                    //   <IconButton sx={{ color: 'rgba(255, 255, 255, 0.54)' }}>
                    //     <InfoIcon />
                    //   </IconButton>
                    // }
                  />
                </ImageListItem>
              </a>
            </Link>
          );
        })}
      </ImageList>
      {assets.length > renderedAssets.length && (
        <div style={{ width: '100%', textAlign: 'center' }}>
          <Button onClick={handleLoadMore}>Load More</Button>
        </div>
      )}
    </div>
  );
};
