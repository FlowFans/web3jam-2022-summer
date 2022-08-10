import React from 'react';
import { Collections } from './components/Collections/Collections';
import { Introduction } from './components/Introduction/Introduction';
import styles from './DropsScreen.module.scss';

export const DropsScreen = () => {
  return (
    <div className={styles.dropsScreen}>
      <Introduction />
      <Collections />
    </div>
  );
};
