import Image from 'next/image';
import React from 'react';
import FlipCountdown from '@rumess/react-flip-countdown';
import styles from './Introduction.module.scss';
import { useMediaQuery } from '@mui/material';

const Step = ({ img, title, desc }) => {
  return (
    <div className={styles.step}>
      <Image src={img} alt={title} width={76} height={76} />
      <div className={styles.stepTitle}>{title}</div>
      <div className={styles.stepDesc}>{desc}</div>
    </div>
  );
};

export const Introduction = ({ curBox, onTimeUp }) => {
  const isDesktop = useMediaQuery('(min-width: 1024px)');

  return (
    <div className={styles.introduction}>
      <div className={styles.title}>Mystery Box</div>
      <div className={styles.desc}>Find the ultra rare sets </div>
      {curBox === 1 ? (
        <div className={styles.countdown}>
          <FlipCountdown
            endAtZero
            size={isDesktop ? 'medium' : 'small'}
            hideYear
            hideMonth
            endAt={'2022-07-28T21:00:00.000+08:00'} // Date/Time
            onTimeUp={onTimeUp}
          />
        </div>
      ) : null}

      <div className={styles.steps}>
        {/* <Step img="/images/logo_bi@2x.png" title="STEP 4" desc="Share or sell it" /> */}
      </div>
    </div>
  );
};
