import { history } from 'umi'
import { DownOutlined } from '@ant-design/icons'
import RectButton from '../../components/rect-button'
import Header from '../../components/header/index'
import rLogo from '../../assets/icons/r-title.svg'

import './index.less';
import {Button, Popover} from "antd"
import { useEffect, useState } from 'react';



export default function PreviewPage(props: any) {

  const [color, setColor] = useState('#00209E')
  const [textColor, setTextColor] = useState('#ffffff')
  const [showColorPicker, setShowColorPicker] = useState(false)
  const [showTextColor, setShowTextPicker] = useState(false)
  const [image, setImage] = useState({cid: null as any, path: null as any})
  const [bg, setBg] = useState('linear-gradient(180deg, #4D7FFF 0%, #9D9BFF 99.99%)')
  const handleChangeColor = (e: any) => {
    setColor(e.hex)
  }
  const handleTextColorChange = (e: any) => {
    setTextColor(e.hex)
  }

  const parseValue = (value: string) => {
      return parseInt(value, 10);
  }

  const handleColorPicker = (anchor: boolean) => {
    setShowColorPicker(anchor)
  }
  const handleTextColor = (anchor: boolean) => {
    setShowTextPicker(anchor)
  }
  const handleVisibleChange = (newVisible: boolean) => {
    setShowColorPicker(newVisible)
  }
  const handleTextVisibleChange = (newVisible: boolean) => {
    setShowTextPicker(newVisible)
  }

  const handleTextChange = (value: string) => {
    console.log(`selected ${value}`);
  };

  const selectColor = (key: string) => {
    const selectBg = bgList.filter((item: any) => {
      return item.key === key
    })[0].value
    setBg(selectBg)
  }

  const typeList = {
    rectStyle: {},
    eightBorder: {clipPath: 'polygon(75px 0, calc(100% - 75px) 0, 100% 75px,  100% calc(100% - 75px), calc(100% - 75px) 100%, 75px 100%, 0 calc(100% - 75px), 0 75px)'},
    sixBorder: {clipPath: 'polygon(90px 0, calc(100% - 90px) 0, 100% 50%,  100% calc(100% - 50%), calc(100% - 90px) 100%, 90px 100%, 0 calc(100% - 50%), 0 50%)'},
    oval: {borderRadius: '202.5px'},
    circleCorner: {borderRadius: '60px'},
    rightCorner: {clipPath: 'polygon(0 0, calc(100% - 120px) 0, 100% 90px,  100% 100%, 100% 100%, 0 100%, 0 calc(100% - 50%), 0 50%)'},
    leftCorner: {clipPath: 'polygon(120px 0, 100% 0, 100% 90px,  100% 100%, 100% 100%, 0 100%, 0 calc(100% - 50%), 0 90px)'}
  } as any
  
  useEffect(() => {
    console.log(props.location.query.type)
  }, [props.location.query.type])
  const gameData = JSON.parse(sessionStorage.getItem('gameData'))
  return (
    <div>
      <Header />
      <div className='modify-page-p'>
      <div className='modify-left'>
        <div className='outer-container'>
          <div id='draw' style={{padding: '20px'}}>
            <div 
              className='template-card-container' 
              id='card-dom'
              style={{
                ...typeList[props.location.query.type], 
                background: bg}}>
                  <div className='text-center pt-4 text-white ft-s-36'>Run Run Run</div>
                  <div className='text-center ft-s-145 text-white'>1213</div>
                  <div className='flex'>
                    <img src={rLogo} alt="" />
                    <div className='text-white ft-s-32 w-353 ml-2'>{gameData?.name}</div>
                  </div>
            </div>
          </div>
        </div>
      </div>
      <div className='modify-right'>
        <div className='right-panel p-16'>
          <div className='panel-title text-left ft-s-70'>{gameData?.name}</div>
          <div className='flex-1 select-bar mt-10'>
            <span className='ft-s-40 text-dark-gray mr-12'><strong className='text-dark-blue ft-s-64'>{gameData?.year}</strong> Y</span>
            <span className='ft-s-40 text-dark-gray mr-2'><strong className='text-dark-blue ft-s-64'>{gameData?.month}</strong> M</span>
            <span className='ft-s-40 text-dark-gray'><strong className='text-dark-blue ft-s-64'>{gameData?.day}</strong> D</span>
          </div>
          <div className='text-modifier mt-12'>
            <div className='font-setting mt-4 ft-s-64 text-dark-blue'>
              <strong>{gameData?.time}</strong>
            </div>
            <div className='mt-20 ft-s-64 text-dark-blue'>
              <strong className='mr-8'>{gameData?.issues}</strong>
              <span className='ft-s-40 text-dark-gray'>Numbers issued</span>
            </div>
            <div className='mt-32 flex' style={{justifyContent: 'left'}}>
              <div className='btn-wrap confirm-btn mr-10'>
                <RectButton btnText={'Submit'} type={'rect'} />
              </div>
              <div className='btn-wrap confirm-btn ml-10'>
                <RectButton btnText={'Modify'} type={'rect'} />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>
  );
}
