import Image from 'next/image';
import React from 'react';
import { InfoCard } from './InfoCard';
import { RoadmapChart } from './RoadmapChart';
import styles from './About.module.scss';

export const About = () => {
  return (
    <div className={styles.about}>
      <div className={styles.aboutus}>
        <div className={styles.content}>
          <div className={styles.title}>About Us</div>
          <div className={styles.desc}>We believe that everyone has a creative mind, and you can make it valuable.</div>
        </div>
      </div>

      <div className={styles.info}>
        <InfoCard
          iconUrl="/new-design/images/about-us-composable.png"
          title="Composable Arts"
          desc="SoulMade is the first composable NFT platform where everyone can unleash their creativity and become a creator as well. 2D images will be our starting point, and more media formats will be added along the way."
        />
        <InfoCard
          iconUrl="/new-design/images/about-us-flow.png"
          title="Built on Flow"
          desc="On Flow blockchain, everyone is able to actually own their assets. In the meanwhile, Flow is one of the most sustainable blockchains out there! Do you know minting NFTs on Flow takes less energy than a Google Search?"
        />
        <InfoCard
          iconUrl="/new-design/images/about-us-global.png"
          title="Global Artists"
          desc="SoulMade has numerous partnerships with global artists of different styles to bring you unique and rare artistic inspiration. Everyone can explore the maze of the art world as a creator, and remake them in your own way."
        />
      </div>
    </div>
  );
};
