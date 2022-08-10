import React from 'react';
import styles from './index.less';

export default function ({ topic, time }) {
  const timestamp_now = new Date().getTime();
  const countDown = parseInt((time * 1000 - timestamp_now) / 1000);
  const oDay = parseInt(countDown / (24 * 60 * 60));
  const oHours = parseInt((countDown / (60 * 60)) % 24);
  const oMinutes = parseInt((countDown / 60) % 60);
  return (
    <div>
      <div className={styles.container}>
        <div className={styles.title}>{topic}</div>
        <div className={styles.time}>
          <div className={styles.days}>
            <p>{oDay.toString()}</p>
            <span>days</span>
          </div>
          <div className={styles.hours}>
            <p>{oHours.toString()}</p>
            <span>hours</span>
          </div>
          <div className={styles.mins}>
            <p>{oMinutes.toString()}</p>
            <span>mins</span>
          </div>
        </div>
        <div className={styles.text}>
          {oDay > 30 ?`${parseInt(oDay/30)} month left before deadline of registration`:`${oDay} day left before deadline of registration`}
        </div>
      </div>
    </div>
  );
}
