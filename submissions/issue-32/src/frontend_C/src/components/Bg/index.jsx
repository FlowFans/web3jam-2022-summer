import React from 'react'
import styles from './index.less'

export default function index(props) {
  const {img} = props
  return (
    <div className={styles.bg} style={img} onClick={()=>props.getBg(img)}></div>
  )
}
