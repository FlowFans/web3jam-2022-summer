import React from 'react';
import cx from 'classnames';
import styles from './Button.module.scss';
import LoadingButton from '@mui/lab/LoadingButton';

export const Button = ({ loading, children, size = 'lg', variant = 'contained', onClick, className }) => {
  const btnStyle = {
    lg: styles.btnLarge,
    sm: styles.btnSmall,
  };

  const btnTypeStyle = {
    outlined: styles.outlined,
  };

  return (
    <LoadingButton
      loading={loading}
      variant={variant}
      className={cx(btnStyle[size], btnTypeStyle[variant], className)}
      onClick={onClick}
    >
      {children}
    </LoadingButton>
  );
};
