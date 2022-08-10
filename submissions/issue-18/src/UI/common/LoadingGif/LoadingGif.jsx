import Image from 'next/image';
import React from 'react';
import styles from './LoadingGif.module.scss';

export const LoadingGif = ({ src }) => {
  return (
    <div className={styles.wrapper}>
      <Image src={src} alt="gif" height={500} width={500} />
    </div>
  );
};
