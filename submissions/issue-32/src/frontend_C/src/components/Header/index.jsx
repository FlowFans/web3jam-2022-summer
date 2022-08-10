import React from 'react'

import styles from "./index.less"

import logo from "@/assets/images/logo.png"


export default function index() {
  return (
    <div>
        <div className={styles.header}>
          <img src={logo} alt="" />
        </div>
    </div>
  )
}
