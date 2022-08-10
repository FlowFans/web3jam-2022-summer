import Image from 'next/image';
import React from 'react';
import { LazyImage } from '../../../../common/LazyImage';
import styles from './Subscription.module.scss';

export const Subscription = () => {
  return (
    <div className={styles.subscription}>
      {/* <div className={styles.title}>Subscribe to our Discord Channel</div> */}
      <div className={styles.input}>
        <div style={{ width: 60, height: 60, position: 'relative' }}>
          <LazyImage asset="/new-design/images/discord-logo-logodownload-download-logotipos-1.png" />
        </div>

        <a href="https://discord.gg/xtqqXCKW9B" target="_blank" rel="noreferrer" style={{ marginTop: 16 }}>
          <button>Join Our Discord</button>
        </a>
      </div>
    </div>
  );
};
