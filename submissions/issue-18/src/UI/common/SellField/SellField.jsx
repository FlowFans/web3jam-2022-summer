import { LoadingButton } from '@mui/lab';
import { InputAdornment, TextField } from '@mui/material';
import React from 'react';
import NumberFormat from 'react-number-format';
import styles from './SellField.module.scss';

const NumberFormatCustom = React.forwardRef((props, ref) => {
  const { onChange, ...other } = props;

  return (
    <NumberFormat
      {...other}
      getInputRef={ref}
      onValueChange={values => {
        onChange({
          target: {
            name: props.name,
            value: values.value,
          },
        });
      }}
      thousandSeparator
      isNumericString
      allowNegative={false}
      decimalScale={2}
    />
  );
});

NumberFormatCustom.displayName = 'NumberFormatCustom';

export const SellField = ({ onChange, onSell, loading }) => {
  return (
    <TextField
      name="sellfield"
      onChange={onChange}
      label="Amount"
      className={styles.wrapper}
      InputProps={{
        classes: {
          root: styles.root,
        },
        inputComponent: NumberFormatCustom,
        startAdornment: <InputAdornment position="start">FLOW</InputAdornment>,
        endAdornment: (
          <LoadingButton
            loading={loading}
            onClick={onSell}
            style={{
              height: '100%',
              borderTopLeftRadius: 0,
              borderBottomLeftRadius: 0,
              padding: '13px 40px',
              fontSize: '20px',
              minWidth: '150px',
            }}
          >
            Sell
          </LoadingButton>
        ),
      }}
    />
  );
};
