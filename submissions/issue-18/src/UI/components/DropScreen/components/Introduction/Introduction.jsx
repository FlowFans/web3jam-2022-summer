import { useMediaQuery } from '@mui/material';
import React from 'react';
import { BackButton } from '../../../../common/BackButton/BackButton';
import { LazyImage } from '../../../../common/LazyImage';
import styles from './Introduction.module.scss';

const AuthorMap = {
  'charles-mastery': 'Charles',
  Omnist: 'Kiko',
};

export const Introduction = ({ title, desc }) => {
  const lessThan768 = useMediaQuery('(max-width: 768px)');

  return (
    <div className={styles.introduction}>
      <div className={styles.imgWrapper}>
        {lessThan768 ? null : <BackButton url="/drops" />}
        <LazyImage asset={`/images/${title}.png`} objectFit="cover" />
      </div>

      <div className={styles.title}>{title}</div>
      <div className={styles.desc}>{`Creator - ${AuthorMap[title]}`}</div>
    </div>
  );
};
