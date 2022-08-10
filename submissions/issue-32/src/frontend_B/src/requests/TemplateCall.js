import {useCurrentUser} from "."  //相关配置
import {getAllGames, getGameByGameId, getGameByOwnerAddr, getMintedNFTList, getUserNFTs} from "../../../flow/scripts"
import {createGame, createGameNFTTemplate, mintGameNFT, mintThemeNFT} from "../../../flow/transactions"
import { useEffect } from 'react';

export default function TemplateCall(){
    const [user,addr,logIn,logOut] = useCurrentUser()
    useEffect(async ()=>{
      try{
        await logIn()
        //await createGameNFTTemplate(29,"sss","aaaa","ssss","ssss")
        await getAllGames()
        //await getGameByOwnerAddr(user.addr)
        //await getGameByGameId(29)
        
        
        
      } catch(e){
        console.error(e)
      }
      
    },[])
  
    useEffect(async ()=>{
      console.log("user:",user)
      if(user.addr){
        try{
          // await createGame("test",100,11)
          //await getMintedNFTList(user.addr,29)
          // await mintGameNFT(user.addr,29,20)
          //await mintThemeNFT(user.addr, 29, "blue")
          //await getUserNFTs(user.addr)
        } catch(e){
          console.error(e)
        }

      }
    },[user])
    //////////////////////////////
    return <div style={{visibility: 'hidden'}}>
      Template Call 
      User addr: {user.addr}
      </div>
}
