import React from 'react'
import styles from "./index.less"

import Header from "@/components/Header"
import Brand from "@/components/Brand"
import Info from "@/components/Info"
import Button from "@/components/Button"
export default function index(props) {
  const brand = JSON.parse(props.location.query.brand)
  const number = props.location.query.number
  return (
    <div>
      <div className={styles.main}>
        <header>
          <Header></Header>
        </header>
        <main>
          <div className={styles.left}>
          <Brand {...brand} number={number}></Brand>
          </div>
          <div className={styles.right}>
            <div className="mb-8">
              <Info></Info>
            </div>
            <div className="flex justify-center">
              <Button content="mint" url={`/deal?brand=${JSON.stringify(brand)}&number=${number}`}></Button>
            </div>
          </div>
        </main>
      </div>
    </div>
  );
}
