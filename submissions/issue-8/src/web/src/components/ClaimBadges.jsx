import useAppContext from "src/hooks/useAppContext"
import useClaim from "src/hooks/useClaim"
import MinterLoader from "./MinterLoader"
import RarityScale from "./RarityScale"
import TransactionLoading from "./TransactionLoading"
import { Button, Form, Input, InputNumber, Upload } from 'antd';
import {useRouter} from "next/router"
import {paths, STATUS_SUCCESS, STATUS_FAILED, TYPE} from "src/global/constants"
import {useEffect} from "react"
// import 'antd-button-color/dist/css/style.css'




export default function ClaimBadges() {
  const [{isLoading, transactionStatus}, mint] = useClaim()
  const {currentUser} = useAppContext()
  const router = useRouter()
  const address = currentUser?.addr

  const onFinish = (values) => {
    console.log(values);
    mint(values)
  };

  useEffect(() => {
    if(transactionStatus == null) return
    let status = STATUS_FAILED
    let title, msg, btn1Text, btn1Path, btn2Text, btn2Path = ""

    if(!transactionStatus.errorMessage) {
      status = STATUS_SUCCESS
      title = "Claimed Successfully"
      msg = "Congras! Just claimed a Badge!"
      btn1Text = "Go profile"
      btn1Path = paths.profile(currentUser?.addr)
      btn2Text = "Claim again"
      btn2Path = paths.claimBadges
    }
    else {
      title = "Claimed Failed"
      msg = transactionStatus.errorMessage
      btn1Text = "Home"
      btn1Path = "/"
      btn2Text = "Claim again"
      btn2Path = paths.claimBadges
    }
    const content = {title: title, msg: msg}
    console.log(JSON.stringify(content))
    router.push({pathname: paths.result, query: {status: status, type: TYPE.CLAIMED, title: title, msg: msg, btn1Text, btn1Path, btn2Text, btn2Path}})
  }, [transactionStatus])

  const validateMessages = {
    required: '${label} is required!',
    types: {
      email: '${label} is not a valid email!',
      number: '${label} is not a valid number!',
    },
    number: {
      range: '${label} must be between ${min} and ${max}',
    },
  };

  if (!currentUser) return null
  const curUserAddress = currentUser.addr





  return (
    
    <div className="flex flex-col flex-auto text-center">
      <h1 className="mb-20 text-7xl text-pink-600 font-extrabold text-center">Claim a badge</h1>
        <div className="flex-auto -ml-8">
          {isLoading ? (
            <TransactionLoading status={transactionStatus} />
          ) : (
            <>
                <Form requiredMark={false} colon={false} labelCol={{span: 10}} wrapperCol={{span: 5}} name="nest-messages" onFinish={onFinish} validateMessages={validateMessages} initialValues={{recipient: curUserAddress}} >
                  <Form.Item color="#394048" name={['claimCode']} label="Code" rules={[{ required: true }]} >
                    <Input style={{borderRadius: '30px', background: '#f9f9f9',border:'white'}} size="large"  />
                  </Form.Item>
                  <Form.Item  name={['recipient']} label="Recipient" rules={[{ required: true }]}>
                    <Input style={{paddingLeft:'20px',borderRadius: '30px', background: '#f9f9f9',border:'white'}} size="large"  />
                  </Form.Item>
                    <Button  
                    size="large" 
                    sizetype="primary" 
                    
                    // style={{marginLeft:'40px',color:'#f0f0f0',paddingLeft:'100px',paddingRight:'100px',background:'#cd6091',border:'white'}}
                     htmlType="submit" disabled={isLoading} 
                     loading={isLoading} 
                     shape="round">
                      Claim
                    </Button>
                </Form>
              
            </>
          )}
        </div>
      
    
    </div>
      
    
  )
}
