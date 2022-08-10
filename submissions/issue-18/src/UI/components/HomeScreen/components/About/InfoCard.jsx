import React from 'react';
import { LazyImage } from '../../../../common/LazyImage';
import styles from './InfoCard.module.scss';

export const InfoCard = ({ iconUrl, title, desc }) => {
  return (
    <div className={styles.card}>
      <div className={styles.icon}>
        <LazyImage asset={iconUrl} />
      </div>
      <div className={styles.title}>{title}</div>
      <div className={styles.desc}>{desc}</div>
    </div>
  );
};
