import Header from '../../components/header/index'
import CustomTitle from '@/components/custom-title';
import run from '../../assets/icons/run.svg'
import runActived from '../../assets/icons/runActived.png'
import ride from '../../assets/icons/ride.png'
import rideActived from '../../assets/icons/rideActived.svg'
import { history } from 'umi';
import './index.less';
import { useState } from 'react';
export default function TemplateSelect() {

  const [gameType, setGameType] = useState('run')

  const switchType = (type: string) => {
    setGameType(type)
  }
  const goToConfirm = (type: string) => {
    history.push({
      pathname: '/modify-page',
      query: {
        type: type,
        gameType: gameType
      }
    })
  }
  const typeList = [
    'rectStyle',
    'eightBorder',
    'sixBorder',
    'oval',
    'circleCorner',
    'rightCorner',
    'leftCorner'
  ]
  return (
    <div className='template-select pb-20'>
      <Header />
      <CustomTitle title={"Select a RaceNumber template"} />
      <div className='type-selector flex flex-nowrap mt-16 mb-12'>
        <div className='select-icon icon-margin cursor-pointer' onClick={() => switchType('run')}>
          {gameType === 'run' ? <img src={runActived} alt="" /> : <img src={run} alt="" />}
        </div>
        <div className='select-icon cursor-pointer icon-margin' onClick={() => switchType('ride')}>
        {gameType === 'ride' ? <img src={rideActived} alt="" /> : <img src={ride} alt="" />}
        </div>
      </div>
      <div className='template-container overflow-auto gap-4'>
        {
          typeList.map((item: any, index: number) => {
            return <div className='type-container text-center cursor-pointer' key={index} onClick={() => goToConfirm(item)}>
              <img src={require(`../../assets/images/${item}.png`)} alt="" />
            </div>
          })
        }
      </div>
    </div>
  );
}
