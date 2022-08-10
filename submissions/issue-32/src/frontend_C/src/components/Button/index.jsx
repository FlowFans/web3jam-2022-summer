import React from 'react';
import styles from './index.less';
import { history } from 'umi';
export default function index(props) {
  const { content ,url} = props;
  return (
    <div onClick={()=>{
      url?
      history.push(url):
      ''
    }}>
      <div className={styles.button}>
        <div className={styles.buttonLeft}></div>
        <div className={styles.buttonMiddle}>
          <div>{content}</div>
        </div>
        <div className={styles.buttonRight}></div>
      </div>  
    </div>
  );
}
