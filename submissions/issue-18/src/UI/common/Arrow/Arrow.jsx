import Image from 'next/image';
import React from 'react';
import styles from './Arrow.module.scss';

export const Arrow = React.forwardRef(({ direction = 'right', inactive = false }, ref) => {
  const arrowClass = direction === 'right' ? styles.right : styles.left;
  const arrowInactiveClass = inactive ? styles.inactive : '';

  return (
    <div className={`${styles.arrow} ${arrowClass} ${arrowInactiveClass}`} ref={ref}>
      <Image src={`/new-design/svgs/right-arrow-svgrepo-com.svg`} alt="arrow" layout="fill" />
    </div>
  );
});

Arrow.displayName = 'Arrow';
