import { LinearProgress, Snackbar } from '@mui/material';
import Link from 'next/link';
import React, { useEffect, useState } from 'react';
import styles from './StatusProgress.module.scss';

const StatusMap = {
  0: { title: 'Unkown' },
  1: { title: 'Pending', desc: 'Transaction Pending - Awaiting Finalization' },
  2: { title: 'Finalized', desc: 'Transaction Finalized - Awaiting Execution' },
  3: { title: 'Executed', desc: 'Transaction Executed - Awaiting Sealing' },
  4: { title: 'Sealed', desc: 'Transaction Sealed - Transaction Complete' },
  5: { title: 'Expired', desc: 'Transaction Expired' },
  6: { title: 'Failed', desc: 'Transaction Failed' },
};

export const StatusProgress = ({ transaction }) => {
  const { errorMessage, status, events } = transaction;
  const [transactionId, setTransactionId] = useState();

  useEffect(() => {
    setTransactionId(events[0]?.transactionId);
  }, [events]);

  console.log(errorMessage);

  return (
    <Snackbar style={{ zIndex: 99999 }} anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }} open={true}>
      {errorMessage == '' ? (
        <div className={styles.statusProgress}>
          <div className={styles.status}>
            <div className={styles.text}>{status < 3 ? StatusMap[1].title : StatusMap[status].title}</div>
          </div>
          <div className={styles.desc}>{status < 3 ? StatusMap[1]?.desc : StatusMap[status]?.desc}</div>
          {status < 3 ? (
            <div className={styles.addr}>Pending tx ...</div>
          ) : (
            <div className={styles.addr}>
              {/*<Link href={`https://testnet.flowscan.org/transaction/${transactionId}`}>*/}
              <Link href={`https://flowscan.org/transaction/${transactionId}`}>
                <a target="_blank">Check transaction on Flowscan</a>
              </Link>
            </div>
          )}
          <div className={styles.progress}>
            <LinearProgress />
          </div>
        </div>
      ) : (
        <div className={styles.statusProgress}>
          <div className={styles.status}>
            <div className={styles.text}>{StatusMap[6].title}</div>
          </div>
          <div className={styles.desc}>{StatusMap[6]?.desc}</div>
          <div className={styles.addr}>
            The tx is failed, please check if you enough tokens or refresh this page and try again
          </div>
          <div className={styles.progress}>
            <LinearProgress variant="determinate" value={0} />
          </div>
        </div>
      )}

      {/* <div className={styles.statusProgress}>
        <div className={styles.status}>
          <div className={styles.text}>{status < 3 ? StatusMap[1].title : StatusMap[status].title}</div>
        </div>
        <div className={styles.desc}>{status < 3 ? StatusMap[1]?.desc : StatusMap[status]?.desc}</div>

        {status < 3 ? (
          <div className={styles.addr}>Pending tx ...</div>
        ) : errorMessage == '' ? (
          <div className={styles.addr}>
            Check transaction on{' '}
            <Link href={`https://testnet.flowscan.org/transaction/${transactionId}`}>
              <a target="_blank">Flowscan</a>
            </Link>
          </div>
        ) : (
          <div className={styles.addr}>The tx is failed, please refresh page to try again.</div>
        )}
        <div className={styles.progress}>
          <LinearProgress />
        </div>
      </div> */}
    </Snackbar>
  );
};
