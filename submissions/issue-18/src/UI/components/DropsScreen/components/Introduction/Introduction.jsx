import React from 'react';
import { LazyImage } from '../../../../common/LazyImage';
import styles from './Introduction.module.scss';

const Step = ({ img, title, desc }) => {
  return (
    <div className={styles.step}>
      <div style={{ width: 160, height: 160, position: 'relative' }}>
        <LazyImage asset={img} />
      </div>
      <div className={styles.stepTitle}>{title}</div>
      <div className={styles.stepDesc}>{desc}</div>
    </div>
  );
};

export const Introduction = () => {
  return (
    <div className={styles.introduction}>
      <div className={styles.title}>Drops</div>
      <div className={styles.desc}>Each drop has a soul, choose the one that matches your taste. </div>
      <div className={styles.steps}>
        <Step
          img="/new-design/images/drop_step1.png"
          title="STEP 1"
          desc="Find the base body we prepared for you or buy a new one if you prefer a different color or shape"
        />
        <Step
          img="/gifs/drop_step2.gif"
          title="STEP 2"
          desc="Have fun choosing the accessories or other elements to put on the body"
        />
        <Step img="/gifs/drop_step3.gif" title="STEP 3" desc="Each combination can be disassembled! Try it out!" />
        <Step img="/gifs/drop_step4.gif" title="STEP 4" desc="Try a different style" />
      </div>
    </div>
  );
};
