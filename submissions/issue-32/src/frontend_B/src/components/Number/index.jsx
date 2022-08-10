import React from 'react'
import styles from "./index.less"
import Vectoc from "@/assets/images/Vector.png"
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
                    <span>{topic}</span>
                </div>
                <div className={styles.time}>
                    <div>
                        <div className='ft-s-24 text-dark-blue'>52</div>
                        <div>days</div>
                    </div>
                    <div className='text-center'>
                        <div className='ft-s-24 text-dark-blue'>23</div>
                        <div>hours</div>
                    </div>
                    <div className='text-right'>
                        <div className='ft-s-24 text-dark-blue'>41</div>
                        <div>mins</div>
                    </div>
                </div>
                <div className='ft-s-24 text-dark-blue'>
                    13506/19999
                </div>
            </div>
        </div>
    </div>
  )
}
