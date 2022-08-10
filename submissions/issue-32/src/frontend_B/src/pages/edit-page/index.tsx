import Header from '../../components/header/index'
import CustomTitle from '@/components/custom-title';
import { history } from 'umi'
import './index.less';
import { Button, Input} from "antd"
import RectButton from '../../components/rect-button'
import { useEffect, useState } from 'react';
import { createGame } from "../../../../flow/transactions"
import { useCurrentUser } from '@/requests/index'
import TemplateCall from '../../requests/TemplateCall'
import dayjs from 'dayjs';
export default function EditPage() {
  const [editData, setEditData] = useState({ // 初始化表单数据
    contractAddress: '',
    name: '',
    year: '',
    month: '',
    day: '',
    time: '',
    issues: null as any
  })


  const confirm = async () => {
    sessionStorage.setItem('gameData', JSON.stringify(editData))
    const date = dayjs(`${editData.year}-${editData.month}-${editData.day} ${editData.time}:00`).toDate().valueOf()
    await createGame(editData.name, editData.issues, date / 1000)
    history.push({
      pathname: '/template-select'
    })
  }
  const inputLongStyle = {
    borderRadius: '10px',
    padding: '1px 10px',
    height: '66px',
    fontSize: '28px'
  }
  const btnStyle = {
    fontSize: '24px',
    fontWeight: '700',
    color: 'white',
    padding: '0 23px',
    background: 'linear-gradient(180deg, #0048BD 0%, #0027A4 100%)',
    borderRadius: '10px',
    height: '70px'
  }
  useCurrentUser()
  return (
    <div className='edit-page'>
      <TemplateCall></TemplateCall>
      <Header />
      <CustomTitle title={"Let's create information for the game"} />
      <div className='edit-form pb-16'>
        {/* <div className='mt-60 mb-1 deploy-label'>Add your game contract address</div> */}
        {/* <div className='flex deploy-address mb-12'>
          <div className='input-warp'><Input value={editData.contractAddress} style={inputLongStyle} onChange={
            (e) => setEditData(editData => { return {...editData, contractAddress: e.target.value}})
            } /></div>
          <div className='btn-wrap'><RectButton onClick={deployContract} btnText={'Quick deploy'} type={'corner'} /></div>
        </div> */}
        <div className='mb-12 mt-60'>
          <div className='mb-1 deploy-label'>Game name</div>
          <div className='input-warp'><Input value={editData.name} style={inputLongStyle} onChange={
            (e) => setEditData(editData => { return {...editData, name: e.target.value}})
          } /></div>
        </div>
        <div className=''>
          <div className='mb-1 deploy-label'>Time</div>
          <div className='flex flex-nowrap'>
            <div className='input-warp-short'><Input value={editData.year} style={inputLongStyle} onChange={
              (e) => setEditData(editData => { return {...editData, year: e.target.value}})
            } /></div>
            <div className='input-warp-short'><Input value={editData.month} style={inputLongStyle} onChange={
              (e) => setEditData(editData => { return {...editData, month: e.target.value}})
            } /></div>
            <div className='input-warp-short'><Input value={editData.day} style={inputLongStyle} onChange={
              (e) => setEditData(editData => { return {...editData, day: e.target.value}})
            } /></div>
            <div className='input-warp-short'><Input value={editData.time} style={inputLongStyle} onChange={
              (e) => setEditData(editData => { return {...editData, time: e.target.value}})
            } /></div>
          </div>
          <div className='flex flex-nowrap'>
            <div className='text-center time-hint'>Year</div>
            <div className='text-center time-hint'>Month</div>
            <div className='text-center time-hint'>Day</div>
            <div className='text-center time-hint'>Time</div>
          </div>
        </div>
        <div className='mt-12'>
          <div className='mb-1 deploy-label'>Number of issues</div>
          <div className='input-warp'><Input value={editData.issues} style={inputLongStyle} onChange={
            (e) => setEditData(editData => { return {...editData, issues: e.target.value}})
          } /></div>
        </div>
        <div className='btn-wrap confirm-btn mt-20'>
          <RectButton onClick={confirm} btnText={'Confirm'} type={'rect'} />
        </div>
      </div>
    </div>
  );
}
