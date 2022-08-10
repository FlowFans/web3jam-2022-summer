import React from 'react'
import styles from './index.less'
export default function index({grade,id,name,time,userImg}){
  return (
    <div className={styles.user}>
       <span>{id}</span>
       <img src={userImg} alt="" />
       <div className={styles.text}>
            <div className={styles.name}>
                {name}
            </div>
            <div className={styles.grade}>
                {grade}
                <span>{time}</span>
            </div>
       </div>
    </div>
  )
}
