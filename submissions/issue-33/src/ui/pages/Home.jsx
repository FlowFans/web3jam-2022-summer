import React from 'react'
import { Link, Outlet } from 'react-router-dom';
import { Layout, Carousel } from 'antd';
import Header from '../components/Header';
import Footer from '../components/Footer';

import '../assets/base.css'
import './home.css'
import bgimg from '../assets/bg.png'
import img1 from '../assets/1.png'
import A1 from '../assets/A01.gif'
import A2 from '../assets/A02.gif'
import A3 from '../assets/A03.gif'
import A4 from '../assets/A04.gif'
import slide1 from '../assets/slide1.jpg'
import slide2 from '../assets/slide2.jpg'
import slide3 from '../assets/slide3.jpg'
import slide4 from '../assets/slide4.jpg'


export default function Home() {
  return (
    <div className='homediv'>
      <div className='slideimg'>
        <img className='bgimg' alt="slideimg" src={bgimg} />
      </div>
      
      <div className='bgcolor'>
        <div className='bg-top'>
          <div className='pcdiv'>
              <div className='home_pc_large'>
                <img className='home_pc_pc' src={img1} />
              </div>
              <div className='home_article'>
                <h1 className='home_subTitle'>IRIS&nbsp;NFT</h1>
                <h1 className='home_subP'>People often perceive the world directly through their eyes.
                  There is a group of people who see the world differently from many others.
                  This is a unique gift given to them by God and should not be a barrier to life. 
                  Perhaps their lives are not all black but they already have a line drawing and they need our help to fill it with colour.<br/><br/>
                  We intend to use the visually impaired as our first NFT project, 
                  using NFT's potential for transparency and variety of play to make the public aware of the urgency of eye disease and get involved in the charity to help visually impaired children.</h1>
                <div className='home_sub_btn'>
                  <div className='home_btn_d1'>
                    <Link to='/wannadonate'>
                    <button className='sub_btn_left'>WANNA DONATE</button>
                    </Link>
                  </div>
                  <div className='home_btn_d1'>
                    <button className='sub_btn_left'>BUY NOW</button>
                  </div>
                </div>
              </div>
          </div>

          <div className='pcdiv_4'>
            <Link className='pcdiv_4_1' to="/nftdetails/1">
              <img className='pcdiv_4_img' src={A1} />
            </Link>
            <Link className='pcdiv_4_1' to="/nftdetails/2">
              <img className='pcdiv_4_img' src={A2} />
            </Link>
            <Link className='pcdiv_4_1' to="/nftdetails/3">
              <img className='pcdiv_4_img' src={A3} />  
            </Link>
            <Link className='pcdiv_4_1' to="/nftdetails/4">
              <img className='pcdiv_4_img' src={A4} />
            </Link>
          </div>

          <div className='home_nft_matter'>
            <div className='home_matter_left'>
              <h1 className='home_matter_h1'>CHARITIES NFTs MATTER</h1>
              <h1 className='home_matter_p'>We build a relationship of trust between the donor and the recipient, providing a channel for interactive philanthropy between the two, thus making the act of charity visual and transparent.<br/>
                  We want to bring donations and attention to the recipient, second creation possibilities to the artist and interactive NFT play and mechanisms to all users.</h1>
            </div>
            <div className='home_matter_right'>
              <h1 className='home_matter_r_h1'>
                  <div className='home_matter_h1_d1'>
                    <div className='matter_r_d1'>01</div>
                    <div className='matter_r_d11'>No Intermediaries</div>
                  </div>
                  <div className='home_matter_h1_d1'>
                    <div className='matter_r_d1'>02</div>
                    <div className='matter_r_d11'>Decentralised Platform</div>
                  </div>
                  <div className='home_matter_h1_d1'>
                    <div className='matter_r_d1'>03</div>
                    <div className='matter_r_d11'>Transparent Donation</div>
                  </div>
                  <div className='home_matter_h1_d1'>
                    <div className='matter_r_d1'>04</div>
                    <div className='matter_r_d11'>Interactive Transaction</div>
                  </div>
                  <div className='home_matter_h1_d1'>
                    <div className='matter_r_d1'>05</div>
                    <div className='matter_r_d11'>Low Gas Fees</div>
                  </div>
                  </h1>
            </div>
          </div>
        </div>
        <div className='home_slideShow'>
          <Carousel autoplay>
            <div className='home_slideShow_div'>
              <img className='home_slideShow_img' src={slide1} />
            </div>
            <div className='home_slideShow_div'>
              <img className='home_slideShow_img' src={slide2} />
            </div>
            <div className='home_slideShow_div'>
              <img className='home_slideShow_img' src={slide3} />
            </div>
            <div className='home_slideShow_div'>
              <img className='home_slideShow_img' src={slide4} />
            </div>
          </Carousel>
        </div>

        <div className='home_drop'>
          <div className='home_drop_text'>
            <h4 className='home_drop_h3'>We hope NFT to be used as a brush to paint rainbows and hope for every group in the world.</h4>
            <h4 className='home_drop_h4'>You are free to browse our website, learn about ot join us ! </h4>
            <button className='home_drop_sub'>subscribe</button>
          </div>
        </div>
        
      </div>
    </div>
  )
}
