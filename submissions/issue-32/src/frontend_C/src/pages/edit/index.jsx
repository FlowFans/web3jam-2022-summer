import React,{useState,useEffect} from 'react';
import styles from './index.less';

import { typeList } from '@/utils/typeList.js';
import {bg} from "@/utils/bg.js"

import Rectangle from '@/assets/images/Rectangle.png';
import Add from "@/assets/images/Add.png"
import Header from '@/components/Header';
import Button from '@/components/Button';
import Brand from '@/components/Brand';
import Bg from '@/components/Bg';
export default function index(props) {
  console.log(props.location.query.number);
  const [img,setImg] = useState({})
  const [number,setNumber] = useState()
  useEffect(() => { 
    setImg(brandInfo.img);
  },[brandInfo])
  useEffect(()=>{
    setNumber(props.location.query.number)
  },[props.location.query.number])
  const brandInfo = {
    title: 'Run! Run! Run!',
    topic: 'RaceNumber Marathon 2024',
    type: 'eightBorder',
    img: { background: 'linear-gradient(180deg, #4D7FFF 0%, #9D9BFF 99.99%)' },
  };

  const brand = {
    title: brandInfo.title,
    topic: brandInfo.topic,
    bg: {
      ...img,
      ...typeList[brandInfo.type],
    },
  };
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
            <div className={styles.enter}>
              <div className={styles.text}>
                <input type="text" placeholder="10 words limit" />
              </div>
              <img src={Rectangle} alt="" />
            </div>
            <div className={styles.img}>
              {bg.map((item,index)=>{
                return (
                  <Bg key={index} img={item} getBg={(img)=>setImg(img)}></Bg>
                )
              })}
            </div>
            <div className={styles.add}>
              <img src={Add} alt="" />
              Add material
            </div>
            <div className={styles.btn}>
              <Button content="Confirm" url={`/buy?brand=${JSON.stringify(brand)}&number=${number}`}></Button>
            </div>
          </div>
        </main>
      </div>
    </div>
  );
}
