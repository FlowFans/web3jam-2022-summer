import React from 'react'
import styles from "./index.less"
import Vectoc from "@/assets/images/Vector.png"
import Price from "@/assets/images/Price.png"
export default function index({title,number,topic,price,time}) {
  return (
    <div>
        <div className={styles.container}>
            <div className={styles.top}>
                <div className={styles.title}>
                    {title}
                </div>
                <div className={styles.number}>
                    {number}
                </div>
                <div className={styles.topic}>
                    <img src={Vectoc} alt="" />
                    {topic}
                </div>
            </div>
            <div className={styles.bottom}>
                <div className={styles.price}>
                    <span>Price</span>
                    <img src={Price} alt="" />
                    <span>{price}</span>
                </div>
                <div className={styles.time}>
                    End in {time} days
                </div>
            </div>
        </div>
    </div>
  )
}
