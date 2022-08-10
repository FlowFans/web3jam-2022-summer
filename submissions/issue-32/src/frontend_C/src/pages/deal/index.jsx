import React from 'react'
import styles from "./index.less"

import {user} from "@/utils/user"

import Header from "@/components/Header"
import Brand from "@/components/Brand"
import Info from "@/components/Info"
import Button from "@/components/Button"
import User from "@/components/User"
export default function index(props) {
  console.log(user);
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
            <div className={styles.users}>
                {user.map((user,index)=>{
                    return (
                        <User key={index} {...user}></User>
                    )
                })}
            </div>
            <div className={styles.more}>{'More >'}</div>
          </div>
          <div className={styles.right}>
            <div className="mb-8">
              <Info></Info>
            </div>
            <div className="flex justify-center mt-14">
              <Button content="Sign Up Again" ></Button>
            </div>
            <div className="flex justify-center mt-14">
                <Button content="Rent"></Button>
                <Button content="Sell"></Button>
            </div>
          </div>
        </main>
      </div>
    </div>
  );
}
