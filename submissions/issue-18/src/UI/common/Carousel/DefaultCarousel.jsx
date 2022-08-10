import Image from 'next/image';
import React, { useRef, useState, useEffect } from 'react';
import SwiperCore, { Pagination, Grid, Navigation } from 'swiper';
import { Swiper, SwiperSlide, useSwiper } from 'swiper/react';
import 'swiper/css';
import 'swiper/css/grid';
import 'swiper/css/pagination';
import 'swiper/css/navigation';
import cx from 'classnames';
import { Button } from '../Button/Button';
import { Arrow } from '../Arrow/Arrow';
import Link from 'next/link';
import AssetImage from '../AssetImage';
import { useRouter } from 'next/router';
import { MdKeyboardArrowDown, MdKeyboardArrowUp } from 'react-icons/md';
import styles from './Carousel.module.scss';
import { useMediaQuery } from '@mui/material';
import * as currency from 'currency.js';

SwiperCore.use([Grid, Pagination, Navigation]);

const NextButton = React.forwardRef(({ inactive = false }, ref) => {
  return (
    <div className={styles.nextButton}>
      <Arrow inactive={inactive} ref={ref} />
    </div>
  );
});

NextButton.displayName = 'NextButton';

const PrevButton = React.forwardRef(({ inactive, onClick }, ref) => {
  return (
    <div className={styles.prevButton} onClick={onClick}>
      <Arrow direction="left" inactive={inactive} ref={ref} />
    </div>
  );
});

PrevButton.displayName = 'PrevButton';

const getAssetLink = (asPath, pathname, nftType, series, ipfsHash, assetId, name) => {
  const queryString = `nftType=${nftType}&name=${name}`;
  const baseUrl = asPath.split('?')[0];

  switch (pathname) {
    case '/profile/[address]':
      return `/profile/product/${series}/${ipfsHash}/${assetId}?${queryString}`;

    case '/profile/[address]/marketplace':
      return `/marketplace/${ipfsHash}?${queryString}`;

    default:
      return `${baseUrl}/${ipfsHash}?${queryString}`;
  }
};

const LinkImage = ({ asset, noPrice }) => {
  const router = useRouter();
  const { pathname, asPath } = router;
  const { id, nftType, price } = asset;

  const name = asset.componentDetail?.name || asset.mainDetail?.name;
  const series = asset.componentDetail?.series || asset.mainDetail?.series;
  const ipfsHash = asset.componentDetail?.ipfsHash || asset.mainDetail?.ipfsHash;
  const edition = asset.componentDetail?.edition || asset.mainDetail?.edition;
  const subtitleText = noPrice ? (edition ? `Edition ${edition}` : `ID ${id}`) : `${currency(price).value} FLOW`;
  const assetLink = getAssetLink(asPath, pathname, nftType, series, ipfsHash, id, name);
  return (
    <Link href={assetLink}>
      <a>
        <AssetImage asset={asset} isOriginal={asset.nftType === 'SoulMadeMain'} width={300} height={300} />
        <div className={styles.imageText}>{name}</div>
        <div className={styles.imageText}> {subtitleText}</div>
      </a>
    </Link>
  );
};

const DefaultViewAllLayout = ({ title, assets, noPrice, onView }) => {
  const lessThan1280 = useMediaQuery('(max-width: 1280px)');

  return (
    <div className={styles.viewAllGrid}>
      <div className={styles.header}>
        <div className={styles.left}>
          <div className={styles.title}>{title}</div>
          {lessThan1280 ? null : (
            <Button size="sm" onClick={onView}>
              <span style={{ marginRight: 8 }}>View Less</span>
              <MdKeyboardArrowUp size={32} />
            </Button>
          )}
        </div>
        <div className={styles.right}>
          <div className={styles.num}>{assets.length}</div>
        </div>
      </div>
      {assets.map((asset, idx) => {
        return (
          <div key={idx} className={styles.viewAllGridItem}>
            <LinkImage asset={asset} noPrice={noPrice} />
          </div>
        );
      })}
    </div>
  );
};

const DefaultCarouselLayout = ({ title, assets, noPrice, onView }) => {
  const prevRef = useRef(null);
  const nextRef = useRef(null);
  const pageNumRef = useRef(null);
  const lessThan1280 = useMediaQuery('(max-width: 1280px)');
  return (
    <div>
      <div className={styles.header}>
        <div className={styles.left}>
          <div className={styles.title}>{title}</div>
          {lessThan1280 ? null : (
            <Button size="sm" onClick={onView}>
              <span style={{ marginRight: 8 }}>View All</span>
              <MdKeyboardArrowDown size={32} />
            </Button>
          )}
        </div>
        <div className={styles.right}>
          <div className={styles.nav}>
            <PrevButton ref={prevRef} />
            <div className={styles.pageNum} ref={pageNumRef} />
            <NextButton ref={nextRef} />
          </div>
        </div>
      </div>
      <Swiper
        pagination={{
          type: 'fraction',
          el: `.${styles.pageNum}`,
        }}
        className={styles.swiper}
        slidesPerView="auto"
        spaceBetween={40}
        onInit={swiper => {
          swiper.params.navigation.prevEl = prevRef?.current || null;
          swiper.params.navigation.nextEl = nextRef?.current || null;
          swiper.params.pagination.el = pageNumRef?.current;
          swiper.navigation.init();
          swiper.navigation.update();
          swiper.pagination.init();
          swiper.pagination.render();
          swiper.pagination.update();
        }}
      >
        {assets.map((asset, idx) => {
          return (
            <SwiperSlide key={idx} className={styles.swiperSlide}>
              <LinkImage asset={asset} noPrice={noPrice} />
            </SwiperSlide>
          );
        })}
      </Swiper>
    </div>
  );
};

export const DefaultCarousel = ({ title, assets = [], noPrice }) => {
  const [viewAll, setViewAll] = useState(false);

  const handleView = () => {
    setViewAll(!viewAll);
  };

  return (
    <div className={styles.defaultCarousel}>
      {viewAll ? (
        <DefaultViewAllLayout title={title} assets={assets} noPrice={noPrice} onView={handleView} />
      ) : (
        <DefaultCarouselLayout title={title} assets={assets} noPrice={noPrice} onView={handleView} />
      )}
    </div>
  );
};
