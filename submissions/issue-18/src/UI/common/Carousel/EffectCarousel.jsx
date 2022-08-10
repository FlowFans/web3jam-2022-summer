import React from 'react';
import SwiperCore, { Pagination, Grid, Navigation, EffectCoverflow } from 'swiper';
import { Swiper, SwiperSlide } from 'swiper/react';
import 'swiper/css';
import 'swiper/css/grid';
import 'swiper/css/pagination';
import 'swiper/css/navigation';
import 'swiper/css/effect-coverflow';
import styles from './Carousel.module.scss';
import { useMediaQuery } from '@mui/material';
import { LazyImage } from '../LazyImage';

SwiperCore.use([Grid, Pagination, Navigation, EffectCoverflow]);

// const NextButton = ({ inactive = false }) => {
//   return (
//     <div className={styles.nextButton}>
//       <Arrow inactive={inactive} />
//     </div>
//   );
// };

// const PrevButton = ({ inactive }) => {
//   return (
//     <div className={styles.prevButton}>
//       <Arrow direction="left" inactive={inactive} />
//     </div>
//   );
// };

export const EffectCarousel = ({ title, items, url, activeIndex, onSwiper }) => {
  const lessThan768 = useMediaQuery('(max-width: 768px)');

  const imageSize = lessThan768 ? 300 : 568;

  return (
    <div className={styles.effectCarousel}>
      <Swiper
        onSlideChange={swiper => onSwiper(swiper.activeIndex)}
        initialSlide={activeIndex}
        className={styles.swiper}
        effect={'coverflow'}
        grabCursor={true}
        centeredSlides={true}
        slidesPerView="auto"
        coverflowEffect={{
          rotate: 50,
          stretch: -10,
          depth: 100,
          modifier: 1,
          slideShadows: false,
        }}
        modules={[EffectCoverflow, Pagination]}
      >
        {items.map((item, idx) => (
          <SwiperSlide key={idx} className={styles.swiperSlider}>
            <div style={{ width: imageSize, height: imageSize, position: 'relative' }}>
              <LazyImage asset={item.img} />
            </div>
            <div className={styles.text}>{item.title}</div>
            <div className={styles.text}>{item.price}</div>
          </SwiperSlide>
        ))}
      </Swiper>
    </div>
  );
};
