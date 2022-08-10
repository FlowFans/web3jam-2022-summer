import Image from 'next/image';
import React, { useState } from 'react';
import { Introduction } from './components/Introduction/Introduction';
import { MysteryBoxCarousel } from './components/MysteryBoxCarousel/MysteryBoxCarousel';
import styles from './MysteryBoxScreen.module.scss';

const GiftCard = ({ src, desc }) => {
  return (
    <div
      style={{
        display: 'flex',
        flexDirection: 'column',
        margin: '16px',
        width: 300,
        height: 300,
        position: 'relative',
      }}
    >
      <Image src={src} alt="gift" objectFit="contain" layout="fill" />
      <div style={{ marginTop: 16, display: 'flex', justifyContent: 'center ' }}>{desc}</div>
    </div>
  );
};

export const MysteryBoxScreen = () => {
  const [curBox, setCurBox] = useState(1);
  const [timeUp, setTimeUp] = useState(false);

  const handleTimeUp = () => {
    setTimeUp(true);
  };

  const handleSwiper = idx => {
    setCurBox(idx);
  };

  return (
    <div className={styles.mysteryBoxScreen}>
      <Introduction curBox={curBox} onTimeUp={handleTimeUp} />
      <MysteryBoxCarousel onSwiper={handleSwiper} timeUp={timeUp} />

      {curBox === 1 ? (
        <div
          style={{ width: '100%', display: 'flex', justifyContent: 'center', position: 'relative', flexWrap: 'wrap' }}
        >
          <GiftCard src="/new-design/gifts/hat_back.png" desc="" />
          <GiftCard src="/new-design/gifts/hat_front.png" desc="" />
          <GiftCard src="/new-design/gifts/whiteT_1.png" desc="" />
          <GiftCard src="/new-design/gifts/whiteT_2.png" desc="" />
          <GiftCard src="/new-design/gifts/whiteT_3.png" desc="" />
        </div>
      ) : null}
    </div>
  );
};
