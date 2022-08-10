import { Alert, Snackbar } from '@mui/material';

const statusText = status => {
  switch (status) {
    case 0:
      return 'unknown';
    case 1:
      return 'pending';
    case 2:
      return 'finalized';
    case 3:
      return 'executed';
    case 4:
      return 'sealed';
    case 5:
      return 'expired';
  }
};

const TransactionAlert = ({ status, open, onClose }) => {
  return (
    <Snackbar
      open={open}
      autoHideDuration={status === 4 ? 5000 : null}
      onClose={onClose}
      anchorOrigin={{ vertical: 'top', horizontal: 'center' }}
    >
      <Alert onClose={onClose} severity="success" sx={{ width: '100%' }}>
        The transaction is {statusText(status)}
      </Alert>
    </Snackbar>
  );
};

export default TransactionAlert;
