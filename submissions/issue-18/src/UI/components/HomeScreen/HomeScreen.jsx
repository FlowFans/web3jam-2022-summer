import React from 'react';
import { LazyImage } from '../../common/LazyImage';
import { About } from './components/About/About';
import { Introduction } from './components/Introduction/Introduction';
import { Subscription } from './components/Subscription/Subscription';
import styles from './HomeScreen.module.scss';

export const HomeScreen = () => {
  return (
    <div className={styles.homeScreen}>
      <Introduction />
      <About />
      <div className={styles.roadmap}>
        <div className={styles.title}>Our Roadmap</div>
        <div className={styles.content}>
          <LazyImage asset="/new-design/images/roadmap.jpeg" />
        </div>
      </div>

      <div className={styles.teaminfo}>
        <div className={styles.title}>Team Info</div>
        <div className={styles.content}>
          <LazyImage asset="/images/team_intro.jpeg" objectFit="cover" />
        </div>
      </div>
      <Subscription />
    </div>
  );
};
