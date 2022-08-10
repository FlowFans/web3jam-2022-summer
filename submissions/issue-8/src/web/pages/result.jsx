import Link from "next/link"
import {useRouter} from "next/router"
import PropTypes from "prop-types"
import { Button, Result } from 'antd';

export default function ResultPage() {
  const router = useRouter()
  const {status, type, title, msg, btn1Text, btn1Path, btn2Text, btn2Path} = router.query

  const onBtn1Click = () => {
    router.push({pathname: btn1Path})
  }

  const onBtn2Click = () => {
    router.push({pathname: btn2Path})
  }

  return (
    <Result
    status={status}
    title={title}
    subTitle={msg}
    extra={[
      <Button type="primary" key="console" onClick={onBtn1Click} shape="round">
        {btn1Text}
      </Button>,
      <Button key="buy" onClick={onBtn2Click} shape="round">{btn2Text}</Button>,
    ]}
    ></Result>
  )
}

