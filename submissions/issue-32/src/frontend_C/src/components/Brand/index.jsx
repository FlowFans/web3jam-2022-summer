import React from 'react';
import styles from './index.less';
import Vectoc from "@/assets/images/Vector.png"
export default function index({ title, number, topic, bg }) {
  return (
    <div className={styles.brand} style={bg}>
      <div className={styles.title}>{title}</div>
      <div className={styles.number}>{number}</div>
      <div className={styles.topic}>
        <img src={Vectoc} alt="" />
        {topic}
      </div>
    </div>
  );
}
