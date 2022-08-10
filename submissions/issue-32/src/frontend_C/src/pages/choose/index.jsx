import React,{useState} from 'react';
import styles from './index.less';

import Rectangle from '@/assets/images/Rectangle.png';
import Group from '@/assets/images/Group.png';
import Price from '@/assets/images/Price.png';

import Header from '@/components/Header';
import Card from '@/components/Card';
import Button from '@/components/Button';

export default function index() {
  const [RaceNumber,setRaceNumber] = useState("请选择")

  const cards = [];
  for (let i = 1000; i < 1050; i++) {
    cards.push({ number: i, status: true });
  }
  const getNumber = ({number})=>{
    setRaceNumber(number)
  }
  return (
    <div>
      <div className={styles.main}>
        <header>
          <Header></Header>
        </header>
        <main>
          <div className={styles.left}>
            <div className={styles.enter}>
              <div className={styles.text}>
                <input type="text" placeholder="Enter number within 9999" />
                <img src={Group} alt="" />
              </div>
              <img src={Rectangle} alt="" />
            </div>
            <div className={styles.cards}>
              <Card number={1213} status={false}></Card>
              {cards.map((card) => {
                return <Card key={card.number} number={card.number} status={card.status} getNumber={getNumber}></Card>;
              })}
            </div>
          </div>
          <div className={styles.right}>
            <div className={styles.gameName}>RaceNumber Marathon</div>
            <div className={styles.no}>NO.</div>
            <div className={styles.number}>{RaceNumber?RaceNumber:'0000'}</div>
            <div className={styles.mint}>
              <div className={styles.price}>
                Price
                <img src={Price} alt="" />
                <span>50</span>
              </div>
              <Button content="mint" url={`/edit?number=${RaceNumber}`}></Button>
            </div>
          </div>
        </main>
      </div>
    </div>
  );
}
