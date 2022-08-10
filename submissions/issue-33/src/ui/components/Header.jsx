import React from 'react'
import {Link} from 'react-router-dom'
import { useState, useEffect } from 'react'
import * as fcl from "@onflow/fcl";
import * as t from "@onflow/types";
import logoImg from '../assets/logo.png'

import './header.css'

fcl.config()
  .put("accessNode.api", "https://rest-testnet.onflow.org")
  .put("discovery.wallet", "https://fcl-discovery.onflow.org/testnet/authn")

export default function Header() {
  const [user, setUser] = useState();

  useEffect(() => {
    // sets the 'user' variable to the person that is logged in through Blocto
    fcl.currentUser().subscribe(setUser);
  }, [])

  const logIn = () => {
    // log in through Blocto
    fcl.authenticate();
  }

  const logOut = () => {
    // log out
    fcl.unauthenticate();
  }

  return (
    <header>
      <div className='divone'>
        <img src={logoImg} className="logoimg" /> 
      </div>
      <div className='divtwo'>
        <ul className='headerUl'>
          <li className='headli'><Link to="/home" className='headlia'>Home</Link></li>
          <li className='headli'><Link to="/aboutus" className='headlia'>About&nbsp;Us</Link></li>
          <li className='headli'><div to="/donate" className='headlia'>Donate</div></li>
          <li className='headli'><div to="/market" className='headlia'>Market</div></li>
          <li className='headli'><div to="/organization" className='headlia'>Organization</div></li>
        </ul>
      </div>
      <div className='divthr'>
        {user ? 
          <button className='wallbtn' onClick={() => logOut()}>LOG OUT</button> :
          <button className='wallbtn' onClick={logIn}>CONNECT WALLET</button>
        }
         
      </div>      
    </header>
  )
}
