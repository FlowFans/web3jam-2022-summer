import { LoadingButton } from '@mui/lab';
import { TextField } from '@mui/material';
import React from 'react';
import styles from './NftTransferField.module.scss';

export const NftTransferField = ({ onChange, onTransfer, loading }) => {
  return (
    <TextField
      name="nft_transfer_field"
      onChange={onChange}
      label="Address"
      className={styles.wrapper}
      InputProps={{
        classes: {
          root: styles.root,
        },
        endAdornment: (
          <LoadingButton
            loading={loading}
            onClick={onTransfer}
            style={{
              height: '100%',
              borderTopLeftRadius: 0,
              borderBottomLeftRadius: 0,
              padding: '13px 40px',
              fontSize: '20px',
              minWidth: '150px',
            }}
          >
            Transfer
          </LoadingButton>
        ),
      }}
    />
  );
};
