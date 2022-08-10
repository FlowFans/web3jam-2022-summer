import React from 'react';
import styles from './RoadmapChart.module.scss';

const Circle = ({ text, size = 'md' }) => {
  const className = `${styles.circle} ${styles[size]}`;
  return (
    <div className={styles.circleWrapper}>
      <div className={className}>{text}</div>
    </div>
  );
};

const Timeline = () => {
  return <div className={styles.line} />;
};

const Plot = ({ version, time, items = [], size }) => {
  return (
    <div className={styles.plot}>
      <Circle size={size} text={version} />
      <div className={styles.time}>{time}</div>
      <div className={styles.items}>
        {items.map((item, index) => (
          <div key={item}>{item}</div>
        ))}
      </div>
    </div>
  );
};

export const RoadmapChart = () => {
  return (
    <div className={styles.roadmapChart}>
      <Timeline />

      <div className={styles.plots}>
        <Plot version="V0.0" time="Q4 2021" size="sm" items={['- Kick off']} />
        <Plot
          version="V1.0"
          time="Q2 2022"
          items={['- 2D Avatar', '- Exclusive Artists', '- Smart Contracts Deployed']}
        />
        <Plot version="V2.0" time="Q4 2022" size="sm" items={['- Music', '- 3D', '- Video', '- UGC']} />
        <Plot version="V3.0" time="Q2 2023" items={['- Secret :)']} />
      </div>
    </div>
  );
};
