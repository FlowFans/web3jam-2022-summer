import React from 'react'
import './footer.css'

import logo from '../assets/logotxt.jpg'
import comimg from '../assets/community.jpg'

export default function Footer() {
  return (
    <footer>
      <div className='foot_top'>
        <div className='foot_top_d1'>
          <img className='foot_top_img' src={logo} />
          <div className='foot_txt'>
          {/* &copy;&nbsp;Irisation */}
          Irisation | Copyright &copy; 2022 DaoMie
          </div>
        </div>
        <div className='foot_top_d2'>
          <h3 className='foot_c_txt'>Community</h3>
          <a>
            <img className='foot_com_img' src={comimg} />
          </a>
        </div>
      </div>
    </footer>
  )
}
