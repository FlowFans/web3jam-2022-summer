import { history } from 'umi'
import arrow from '../../assets/icons/arrow.svg'
import text from '../../assets/icons/text.svg'
import { SketchPicker } from 'react-color'
import { Select, Spin } from 'antd';
import { DownOutlined } from '@ant-design/icons'
import RectButton from '../../components/rect-button'
import { create, CID, IPFSHTTPClient } from "ipfs-http-client"
import domtoimage from 'dom-to-image';
import rLogo from '../../assets/icons/r-title.svg'
import addIcon from '../../assets/icons/add.svg'
import { getAllGames } from "../../../../flow/scripts"
import { createGameNFTTemplate } from "../../../../flow/transactions"
import { useCurrentUser } from '@/requests/index'

import './index.less';
import {Button, Popover} from "antd"
import { useEffect, useState } from 'react';

const { Option } = Select;



export default function ModifyPage(props: any) {

  const [color, setColor] = useState('#00209E')
  const [textColor, setTextColor] = useState('#ffffff')
  const [showColorPicker, setShowColorPicker] = useState(false)
  const [showTextColor, setShowTextPicker] = useState(false)
  const [slogan, setSlogan] = useState("Run, Run, Run")
  // const [image, setImage] = useState({cid: null as any, path: null as any})
  const [loading, setLoading] = useState(false)
  const [bg, setBg] = useState('linear-gradient(180deg, #4D7FFF 0%, #9D9BFF 99.99%)')
  const [currentGame, setCurrentGame] = useState(null) as any
  const handleChangeColor = (e: any) => {
    setColor(e.hex)
  }
  const handleTextColorChange = (e: any) => {
    setTextColor(e.hex)
  }

  const parseValue = (value: string) => {
      return parseInt(value, 10);
  }

  const draw = async () => {
    const dom = document.getElementById('draw') as any
    const box = window.getComputedStyle(dom);
    // DOM 节点计算后宽高
    const width = parseValue(box.width);
    const height = parseValue(box.height);

    domtoimage.toSvg(dom, {width: width, height: height, style: typeList[props.location.query.type]})
    .then(function (dataUrl: string) {
      addFile(dataUrl)
    })
    .catch(function (error: any) {
        console.error('oops, something went wrong!', error);
    });
  }

  const addFile = async (file: any) => {
    // const ipfs = create(new URL('https://ipfs.infura.io:5001'))
    setLoading(true)
    let ipfs: IPFSHTTPClient | undefined;

    const projectId = '2DAG3LKiD1yN2bXa5aKCnyj2POB';
    const projectSecret = '993041e67d7972bfa56772921303a510';
    const auth =
        'Basic ' + Buffer.from(projectId + ':' + projectSecret).toString('base64');
    try {
      // ipfs = create({
      //   // host: 'ipfs.infura.io',
      //   host: 'infura-ipfs.io',
      //   port: 5001,
      //   protocol: 'https',
      //   headers: {
      //     authorization: auth
      //   }
      // });
      ipfs = create({
        url: 'http://182.254.220.49:5001'
      })
    } catch (error) {
      console.error("IPFS error ", error);
      ipfs = undefined;
    }
    // const added = await ipfs?.add(file)
    let added = null as any
    try {
      added = await ipfs?.add(file)
    } catch (error) {
      added = {
        path: 'QmcVExofA8Yf5no8k04AGuqCuadirJAhvfqWHjEaJRBF2H'
      }
    }
    setLoading(false)
    createGameNFTTemplate(currentGame.uid, added?.path, props.location.query.type, props.location.query.gameType, slogan).then(() => {
      history.push({
        pathname: '/preview-page',
        query: {
          type: props.location.query.type
        }
      })
    })
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

  const content = (
    <div>
      <SketchPicker onChange={handleChangeColor} color={color} />
    </div>
  )

  const textColorPicker = (
    <div>
      <SketchPicker onChange={handleTextColorChange} color={textColor} />
    </div>
  )

  const typeList = {
    rectStyle: {},
    eightBorder: {clipPath: 'polygon(75px 0, calc(100% - 75px) 0, 100% 75px,  100% calc(100% - 75px), calc(100% - 75px) 100%, 75px 100%, 0 calc(100% - 75px), 0 75px)'},
    sixBorder: {clipPath: 'polygon(90px 0, calc(100% - 90px) 0, 100% 50%,  100% calc(100% - 50%), calc(100% - 90px) 100%, 90px 100%, 0 calc(100% - 50%), 0 50%)'},
    oval: {borderRadius: '202.5px'},
    circleCorner: {borderRadius: '60px'},
    rightCorner: {clipPath: 'polygon(0 0, calc(100% - 120px) 0, 100% 90px,  100% 100%, 100% 100%, 0 100%, 0 calc(100% - 50%), 0 50%)'},
    leftCorner: {clipPath: 'polygon(120px 0, 100% 0, 100% 90px,  100% 100%, 100% 100%, 0 100%, 0 calc(100% - 50%), 0 90px)'}
  } as any

  const bgList = [
    {
      key: 'blue',
      value: 'linear-gradient(180deg, #4D7FFF 0%, #9D9BFF 99.99%)'
    },
    {
      key: 'green',
      value: 'linear-gradient(180deg, #4DFF9F 0%, #C1FF9B 99.99%)'
    },
    {
      key: 'yellow',
      value: 'linear-gradient(180deg, #FFC34D 0%, #FDFF9B 99.99%)'
    }
  ] as any
  
  useEffect(() => {
    getAllGames().then((res: any) => {
      setCurrentGame(res[res.length - 1])
    })
  }, [props.location.query.type])
  const gameData = JSON.parse(sessionStorage.getItem('gameData'))
  useCurrentUser()
  return (
    <div className='modify-page'>
      {loading ? <div className='spin'>
        <Spin tip="imgae uploading..." size="large" />
      </div> : ''}
      <div className='modify-left'>
        <div className='top-bar'>
          <div className='text-center'><img className='cursor-pointer' src={arrow} alt="" /></div>
          <div className='text-center relative'>
              <Popover 
                content={content} 
                title="Color Picker"
                trigger="click"
                visible={showColorPicker}
                onVisibleChange={handleVisibleChange}
                placement="bottom">
                  <div className='flex cursor-pointer' onClick={() => handleColorPicker(true)} style={{justifyContent: 'center', alignItems: 'center'}}>
                      <div className='bg-preview mr-4' style={{backgroundColor: color}}></div>
                      <span className='text-lg'><DownOutlined /></span>
                  </div>
              </Popover>
          </div>
          <div className='cursor-pointer text-center'><img className='cursor-pointer' src={text} alt="" /></div>
        </div>
        <div className='flex mt-16 mb-32'>
          <RectButton btnText={'Set to user editable'} type={'freeStyle'} style={{marginRight: '120px'}} />
          <div style={{marginLeft: '120px'}} className='btn-wrap'><RectButton btnText={'Allow user to add material'} type={'freeStyle'} /></div>
        </div>
        <div className='outer-container'>
          <div id='draw' style={{padding: '20px'}}>
            <div 
              className='template-card-container' 
              id='card-dom'
              style={{
                ...typeList[props.location.query.type], 
                background: bg}}>
                  <div className='text-center pt-4 text-white ft-s-36'>{slogan}</div>
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
           <div className='slogan-input mb-6'>
              <input type="text" placeholder="slogan, 10 words limit" onChange={(e) => {
                if (e.target.value) {
                  setSlogan(e.target.value)
                } else {
                  setSlogan('Run Run Run')
                }
              }} />
          </div>
          <div className='panel-title text-left ft-s-36 '>Design</div>
          <div className='flex-1 select-bar mt-10'>
            {
              bgList.map((item: any) => {
                return <div className='select-box cursor-pointer mr-10' key={item.key} style={{background: item.value}} onClick={() => selectColor(item.key)}></div>
              })
            }
            <span className='ft-s-32 text-dark-gray'>more</span>
          </div>
          <div className='add-image-area flex-1 h-center mt-12 cursor-pointer'>
            <img className='mr-6' src={addIcon} alt="" />
            <div className='ft-s-28 text-dark-gray'>Add Image</div>
          </div>
          <div className='text-modifier mt-16'>
            <div className='ft-s-28'>Text</div>
            <div className='font-selector mt-2'>
              <Select defaultValue="Heiti" style={{ width: 550 }} onChange={handleTextChange}>
                <Option value="Heiti">Heiti</Option>
                <Option value="DARMA">DARMA</Option>
                <Option value="Test">Test</Option>
              </Select>
            </div>
            <div className='font-setting mt-4'>
              <Popover 
                content={textColorPicker} 
                title="Color Picker"
                trigger="click"
                visible={showTextColor}
                onVisibleChange={handleTextVisibleChange}
                placement="bottom">
                  <div className='cursor-pointer flex-1' onClick={() => handleTextColor(true)} style={{alignItems: 'center'}}>
                      <div className='text-preview mr-2' style={{backgroundColor: textColor}}></div>
                      <span className='text-lg ft-s-24 text-dark-gray'>{textColor.split('#')[1].toUpperCase()}</span>
                  </div>
              </Popover>
              <div className='text-center ft-s-24 text-dark-gray'>Bold</div>
              <div className='text-right'>
                <Select defaultValue="Heiti" style={{ width: 100, textAlign: 'left' }} onChange={handleTextChange}>
                  <Option value="Heiti">32</Option>
                  <Option value="DARMA">28</Option>
                  <Option value="Test">24</Option>
                </Select>
              </div>
            </div>
            <div className='add-image-area flex-1 h-center mt-24 cursor-pointer'>
              <img className='mr-6' src={addIcon} alt="" />
              <div className='ft-s-28 text-dark-gray'>Add material</div>
            </div>
            <div className='mt-20'>
              <div className='btn-wrap confirm-btn'>
                <RectButton onClick={draw} btnText={'Confirm'} type={'rect'} />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
