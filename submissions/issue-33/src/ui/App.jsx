import React from 'react'
import { Outlet, useNavigate, Link } from 'react-router-dom';
import { Layout } from 'antd';
import Header from './components/Header';
import Footer from './components/Footer';

import './assets/base.css'

export default function App() {
    const { Content } = Layout;
    const navigate = useNavigate()
    
    const trans = () => {
        setTimeout(()=>{
            navigate('/home')
          }, 0)
    };
    window.onload = function(){
      document.getElementById('app_btn').click();
      document.getElementById('app_btn').style.display="none";
    }

  return (  
    <>
      <Layout id='app'>
          <Header/>
          <Content className='cont'>
            <Outlet/>
          </Content>
          <Footer/>
      </Layout>
    <button id='app_btn' onClick={() => trans()}></button>
    </>
  )
}
