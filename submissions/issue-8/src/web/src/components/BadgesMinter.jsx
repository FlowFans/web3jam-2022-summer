import useAppContext from "src/hooks/useAppContext"
import useMintAndList from "src/hooks/useMintAndList"
import TransactionLoading from "./TransactionLoading"
import { Button, Form, Input, InputNumber, Upload, Modal } from 'antd';
import useNFTStorage from "src/hooks/useNFTStorage"
import useLogin from "src/hooks/useLogin";
import 'antd/dist/antd.css';
import { PlusCircleOutlined } from '@ant-design/icons';
import React, { useState, useEffect } from "react"
import {useRouter} from "next/router"
import {paths, STATUS_SUCCESS, STATUS_FAILED, TYPE} from "src/global/constants"

const layout = {
  labelCol: { span: 8 },
  wrapperCol: { span: 8 },
};

const getBase64 = (file) =>
  new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);

    reader.onload = () => resolve(reader.result);

    reader.onerror = (error) => reject(error);
  });


/* eslint-disable no-template-curly-in-string */
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

const beforeUpload = (file) => {
  const isJpgOrPng = file.type === 'image/jpeg' || file.type === 'image/png';

  if (!isJpgOrPng) {
    message.error('You can only upload JPG/PNG file!');
  }

  const isLt2M = file.size / 1024 / 1024 < 2;

  if (!isLt2M) {
    message.error('Image must smaller than 2MB!');
  }

  return isJpgOrPng && isLt2M;
};

export default function BadgesMinter() {
  const [{ isLoading, transactionStatus }, mint] = useMintAndList()
  const [ isUploading, uploadNftStorage ] = useNFTStorage()
  const {currentUser} = useAppContext()
  const router = useRouter()
  const [fileList, setFileList] = useState([]);
  const [previewVisible, setPreviewVisible] = useState(false);
  const [previewImage, setPreviewImage] = useState("");
  const [previewTitle, setPreviewTitle] = useState("");
  const handleCancel = () => setPreviewVisible(false);

  const handlePreview = async (file) => {
    if (!file.url && !file.preview) {
      file.preview = await getBase64(file.originFileObj);
    }

    setPreviewImage(file.url || file.preview);
    setPreviewVisible(true);
    setPreviewTitle(file.name || file.url.substring(file.url.lastIndexOf('/') + 1));
  };

  const handleChange = ({ fileList: newFileList }) => {
    console.log(JSON.stringify(fileList));
    setFileList(newFileList);
  };

  useEffect(() => {
    if(transactionStatus == null) return
    let status = STATUS_FAILED
    let title, msg, btn1Text, btn1Path, btn2Text, btn2Path = ""

    if(!transactionStatus.errorMessage) {
      status = STATUS_SUCCESS
      title = "Create badges Successfully"
      msg = "Congras! Just created a Badge!"
      btn1Text = "Home"
      btn1Path = "/"
      btn2Text = "Create Badge"
      btn2Path = paths.mintBadges
    }
    else {
      title = "Claimed Failed"
      msg = transactionStatus.errorMessage
      btn1Text = "Home"
      btn1Path = "/"
      btn2Text = "Create again"
      btn2Path = paths.mintBadges
    }
    const content = {title: title, msg: msg}
    console.log(JSON.stringify(content))
    router.push({pathname: paths.result, query: {status: status, type: TYPE.CLAIMED, title: title, msg: msg, btn1Text, btn1Path, btn2Text, btn2Path}})
  }, [transactionStatus])

  if (!currentUser) return null
  const address = currentUser.addr

  const onFinish = (values) => {
    console.log(values);
    mint(values)
  };

  const handleUpload = async (option) => {
    const file = option.file
    console.log("upload file")
    console.log(file)
    let fileName = file.name;
    let fileType = fileName.split('.').pop();
    let renameFile = new File( [file],"nft_img"  +  '.' + fileType, option)
    uploadNftStorage(renameFile, "OnlyBadge", "Merchants Logo", 
      (responseUrl, data, ipnft) => {
        console.log("upload success:" + ipnft)
        console.log("upload success metadata:" + JSON.stringify(data))
        option.onSuccess(ipnft)
      }, 
      ()=> {
        console.log("upload error:" + error)
        option.onError(error)
      })
  }

  const uploadButton = (
    <div>
      <PlusCircleOutlined />
      <div
        style={{
          marginTop: 8
        }}
      >
        Upload
      </div>
    </div>
  );

  return (
    <div className="">
      {/* <MinterLoader isLoading={isLoading} /> */}

      <div className="flex flex-col">
        <h1 className="mb-8 text-7xl text-pink-600 font-extrabold text-center">Create a New Badge</h1>
        {/* <RarityScale /> */}

        <div className="ml-20">
        {isLoading ? (
          <TransactionLoading status={transactionStatus} />
        ) : (
          // <Button onClick={mint} disabled={isLoading} roundedFull={true}>
          <Form {...layout} requiredMark={false} colon={false} name="nest-messages" onFinish={onFinish} validateMessages={validateMessages} initialValues={{recipient: address, name:"Test Badge", max:1, description: "", royalty_cut: 0, royalty_receiver: address}}>
            <Form.Item  wrapperCol={{span: 5}} name={['recipient']} label="Creator" rules={[{ required: true }]} >
              <Input disabled={true} style={{paddingLeft:'30px',borderRadius: '30px', background: '#f9f9f9',border:'white'}} size="large"/>
            </Form.Item>
            <Form.Item wrapperCol={{span: 5}} name={['name']} label="Badge name" rules={[{ required: true }]}>
              <Input style={{paddingLeft:'30px',borderRadius: '30px', background: '#f9f9f9',border:'white'}} size="large"/>
            </Form.Item>
            <Form.Item  name={['max']} label="Total supply" rules={[{ type: 'number', min: 0, max: 9999, default: 9999, required: true }]}>
              <InputNumber style={{paddingLeft:'10px',borderRadius: '30px', background: '#f9f9f9',border:'white'}} size="large"/>
            </Form.Item>
            <Form.Item wrapperCol={{span: 5}} name={['claim_code']} label="Claim code" rules={[{ required: true }]}>
              <Input style={{paddingLeft:'20px',borderRadius: '30px', background: '#f9f9f9',border:'white'}}size="large"/>
            </Form.Item>
            <Form.Item name={['externalURL']} label="Website">
              <Input style={{paddingLeft:'20px',borderRadius: '30px', background: '#f9f9f9',border:'white'}} size="large"/>
            </Form.Item>
            <Form.Item name={['badge_image']}  label="Badge Picture" rules={[{ required: true }]}>
              <Upload listType="picture-card" beforeUpload={beforeUpload} customRequest={handleUpload} onPreview={handlePreview} onChange={handleChange}>
                {fileList.length >= 1 ? null : uploadButton}
              </Upload>
            </Form.Item>
            <Modal
                visible={previewVisible}
                title={previewTitle}
                footer={null}
                onCancel={handleCancel}
              >
                <img
                  alt="example"
                  style={{
                    width: "100%"
                  }}
                  src={previewImage}
                />
              </Modal>
            <Form.Item name={['description']} label="Description">
              <Input.TextArea rows={6} style={{paddingLeft:'10px',borderRadius: '10px', background: '#f9f9f9',border:'white'}} size="large" />
            </Form.Item>
            <Form.Item name={['royalty_cut']} label="Creator Fee(%)" rules={[{ type: 'number', min: 0, max: 50, default: 0, required: true}]}>
              <InputNumber style={{paddingLeft:'10px',borderRadius: '30px', background: '#f9f9f9',border:'white'}} size="large"/>
            </Form.Item>
            <Form.Item name={['royalty_description']} label="Fee Description ">
              <Input style={{paddingLeft:'30px',borderRadius: '30px', background: '#f9f9f9',border:'white'}} size="large" />
            </Form.Item>
            <Form.Item wrapperCol={{span: 5}} name={['royalty_receiver']} label="Fee Receiver">
              <Input style={{paddingLeft:'30px',borderRadius: '30px', background: '#f9f9f9',border:'white'}} size="large"/>
            </Form.Item>
            <Form.Item wrapperCol={{ ...layout.wrapperCol, offset: 8 }}>
              <Button 
              size="large"
              // style={{marginLeft:'40px',color:'#f0f0f0',paddingLeft:'150px',paddingRight:'150px',background:'#cd6091',border:'2px solid #f3f3f3'}}
              type="primary" 
              htmlType="submit" 
              disabled={isLoading && isUploading} loading={isLoading || isUploading}
              shape="round">
                Submit
              </Button>
            </Form.Item>
          </Form>
        )}


        </div>
      
        
      </div>
    </div>
  )
}
