import React,{useState,useEffect} from 'react'
import styles from "./index.less"

import Rectangle from "@/assets/images/Rectangle.png"

import Header from "@/components/Header"
import Number from "@/components/Number"
import Info from "@/components/Info"
import Button from "@/components/Button";

import {getAllGames, getGameByGameId, getGameByOwnerAddr, getMintedNFTList, getUserNFTs} from "../../../../flow/scripts"
import { useCurrentUser } from '../../requests'

export default function index(props) {
  const [game,setGame] = useState({})
  const [uid,setUid] = useState()
  useEffect(() => {
    const uid = props.location.query.uid;
    setUid(uid)
    getGameByGameId(uid).then(item=>{
      setGame(item)
    })
  },[])
  const {slogan,gameName,timestamp} = game
  return (
    <>
      <div className={styles.main}>
        <header>
          <Header></Header>
        </header>
        <main>
          <div className={styles.left}>
            <div className={styles.enter}>
              <p className={styles.text}>Enter number within 9999</p>
              <img src={Rectangle} alt="" />
            </div>
            <div className={styles.numbers}>
              <Number title={slogan} number="0001" topic={gameName} price={50} time={26}></Number>
              <Number title={slogan} number="0011" topic={gameName} price={50} time={26}></Number>
              <Number title={slogan} number="0111" topic={gameName} price={50} time={26}></Number>
              <Number title={slogan} number="0666" topic={gameName} price={50} time={26}></Number>
              <Number title={slogan} number="0888" topic={gameName} price={50} time={26}></Number>
              <Number title={slogan} number="0999" topic={gameName} price={50} time={26}></Number>
            </div>
          </div>
          <div className={styles.right}>
            <div className="mb-8">
              <Info topic={gameName} time={timestamp}></Info>
            </div>
            <div className="flex justify-center">
              <Button content="Sign up" url={`/choose?uid=${uid}`}></Button>
            </div>
          </div>
        </main>   
      </div>
    </>
  )
}
