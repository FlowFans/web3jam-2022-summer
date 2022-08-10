import React from 'react';
import { MdClose } from 'react-icons/md';
import styles from './CloseIcon.module.scss';

export const CloseIcon = ({ onClick }) => {
  return (
    <div
      className={styles.closeIcon}
      onClick={e => {
        e.stopPropagation();
        onClick();
      }}
    >
      <MdClose />
    </div>
  );
};
