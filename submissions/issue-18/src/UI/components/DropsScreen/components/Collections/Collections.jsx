import Link from 'next/link';
import React from 'react';
import { LazyImage } from '../../../../common/LazyImage';
import styles from './Collections.module.scss';

const Collection = ({ link, img, title, desc }) => {
  return (
    <div className={styles.collection}>
      {link && (
        <Link passHref href={link}>
          <a className={styles.imgA}>
            <LazyImage asset={img} objectFit="cover" />
          </a>
        </Link>
      )}

      <div className={styles.title}>{title}</div>
      <div className={styles.desc}>{desc}</div>
    </div>
  );
};

export const Collections = () => {
  return (
    <div className={styles.collections}>
      <div className={styles.collectionsWrapper}>
        <Collection
          link="/drops/Charles-Mastery?title=charles-mastery"
          img="/images/charles-mastery.png"
          title="Charles Mastery"
        />
      </div>
      <div className={styles.collectionsWrapper}>
        <Collection
          link="/drops/Omnist?title=Omnist"
          img="/images/omnist-banner.png"
          title="Omnist"
          // desc="Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor."
        />
      </div>
    </div>
  );
};
