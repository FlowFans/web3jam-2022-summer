import useAppContext from "src/hooks/useAppContext"
import TransactionLoading from "./TransactionLoading"
import { Button, Form, Input, InputNumber, Upload, Modal } from 'antd';
import { PlusCircleOutlined } from '@ant-design/icons';
import 'antd/dist/antd.css';
import useNFTStorage from "src/hooks/useNFTStorage"
import useLogin from "src/hooks/useLogin";
import React, { useState, useEffect } from "react"
import {paths, STATUS_SUCCESS, STATUS_FAILED, TYPE} from "src/global/constants"
import {useRouter} from "next/router"
import useMintNFTMinter from "src/hooks/useMintNFTMinter"
import Footer from "./Footer";



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

export default function NFTMinter() {
  const [{ isLoading, transactionStatus }, mint] = useMintNFTMinter()
  const [previewVisible, setPreviewVisible] = useState(false);
  const [previewTitle, setPreviewTitle] = useState("");
  const [ isUploading, uploadNftStorage ] = useNFTStorage()
  const {currentUser} = useAppContext()
  const [fileList, setFileList] = useState([])
  const handleCancel = () => setPreviewVisible(false);
  const [previewImage, setPreviewImage] = useState("");

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



  const router = useRouter()

  useEffect(() => {
    if(transactionStatus == null) return
    let status = STATUS_FAILED
    let title, msg, btn1Text, btn1Path, btn2Text, btn2Path = ""

    if(!transactionStatus.errorMessage) {
      status = STATUS_SUCCESS
      title = "Create Profile Successfully"
      msg = "Congras! Just created a Profile!"
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
      btn2Path = paths.mintNFTMinter
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

      <div className="mb-32">
        <h1 className="mb-16 text-7xl text-pink-600 font-extrabold text-center">Setup Profile</h1>

        <div className="ml-20">
          {isLoading ? (
            <TransactionLoading status={transactionStatus} />
          ) : (
            // <Button onClick={mint} disabled={isLoading} roundedFull={true}>
            <Form requiredMark={false} colon={false} {...layout} name="nest-messages" onFinish={onFinish} validateMessages={validateMessages}>
              <Form.Item labelCol={{span: 10}} wrapperCol={{span: 5}} name={['name']} label="Profile Name" rules={[{ required: true }]} >
                <Input size="large" style={{paddingLeft:'20px',borderRadius: '30px', background: '#f9f9f9',border:'white'}} placeholder="Input Your Profile Name"/>
              </Form.Item>
              {/* <Form.Item name={['imagePath']} label="商户logo" rules={[{ required: true }]}>
                <Input />
              </Form.Item> */}
              <Form.Item labelCol={{span: 10}} wrapperCol={{span: 5}} name={['image']} label="Profile logo" rules={[{ required: true }]} >
                <Upload listType="picture-card" beforeUpload={beforeUpload}  customRequest={handleUpload} onPreview={handlePreview} onChange={handleChange}>
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
              <Form.Item wrapperCol={{ ...layout.wrapperCol, offset: 8 }}>
                <Button 
                size="large" 
                type="primary" 
                htmlType="submit" 
                style={{marginTop:'10px',marginLeft:'100px',color:'#f0f0f0',paddingLeft:'70px',paddingRight:'70px',background:'#cd6091',border:'white'}}
                disabled={isLoading || isUploading} loading={isLoading || isUploading}
                shape="round">
                  Submit
                </Button>
              </Form.Item>
            </Form>
        )}

        </div>
       
      </div>
      <Footer/>
      

    </div>

     
  )
}


