import React from 'react';
import styles from './Ribbon.module.scss';

export const Ribbon = ({ text }) => {
  return <div className={styles.ribbon}>{text}</div>;
};
