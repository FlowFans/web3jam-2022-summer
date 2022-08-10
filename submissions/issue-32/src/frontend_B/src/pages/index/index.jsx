import React from 'react';
import styles from './index.less';

import Message from "@/assets/images/Message.png";

import image_1 from "@/assets/images/image_1.jpg";
import image_2 from "@/assets/images/image_2.jpg";
import image_3 from "@/assets/images/image_3.jpg";
import image_4 from "@/assets/images/image_4.jpg";
import image_5 from "@/assets/images/image_5.jpg";
import image_6 from "@/assets/images/image_6.jpg";
import image_7 from "@/assets/images/image_7.jpg";
import image_8 from "@/assets/images/image_8.jpg";
import image_9 from "@/assets/images/image_9.jpg";
import image_10 from "@/assets/images/image_10.jpg";
import image_11 from "@/assets/images/image_11.jpg";
import createImg from "@/assets/icons/create.png";

import Header from '../../components/header'
import Number from "../../components/Number"
import { history } from 'umi'
export default function index() {
  const jump = () => {
    history.push({
        pathname: '/edit-page'
    })
  }
  return (
    <div>
      <div className={styles.main}>
        <header>
          <Header></Header>
          <div className={styles.user}>
            <div className={styles.image}>
                <img src="https://s3.bmp.ovh/imgs/2022/08/07/7a6d7942b2977fce.jpeg" alt="" />
            </div>
            <div className={styles.name}>       
                Mizuki NOGUCHI
            </div>
            <div className={styles.message}>
                <img src={Message} alt="" />
            </div>
          </div>
        </header>
        <main>
            <div className={styles.content}>
                <div className={styles.title}>Event management</div>
                <div className={styles.image1}>
                    <Number title="Run! Run! Run!" number="1213" topic="RaceNumber Marathon 2024" price={50} time={30}></Number>
                    <div className='box-border ml-6 mt-6 mb-4 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer flex flex-col items-center' style={{width: '254.07px'}} onClick={jump}>
                        <img className='mt-12' style={{width: '50%', height: 'auto'}} src={createImg} alt="create-game" />
                        <span className='mt-24 flex items-end text-2xl font-bold text-gray-400'>Create a game</span>
                    </div>
                </div>
            </div>
            <div className={styles.content}>
                <div className={styles.title}>Gallery</div>
                <div className={styles.image1}>
                    <img src={image_1} alt="" />
                    <img src={image_2} alt="" />
                    <img src={image_3} alt="" />
                </div>
            </div>
            <div className={styles.content}>
                <div className={styles.title}>Numbers on saling</div>
                <div className={styles.image1}>
                    <img src={image_4} alt="" />
                    <img src={image_5} alt="" />
                    <img src={image_6} alt="" />
                </div>
            </div>
            <div className={styles.content}>
                <div className={styles.title}>Number in the lease</div>
                <div className={styles.image1}>
                    <img src={image_7} alt="" />
                    <img src={image_8} alt="" />
                </div>
            </div>
            <div className={styles.content}>
                <div className={styles.title}>Unavailable number</div>
                <div className={styles.image2}>
                    <img src={image_9} alt="" />
                    <img src={image_10} alt="" />
                    <img src={image_11} alt="" />
                    <img src={image_10} alt="" />
                    <img src={image_11} alt="" />
                    <img src={image_10} alt="" />
                </div>
            </div>
        </main>
      </div>
    </div>
  );
}
