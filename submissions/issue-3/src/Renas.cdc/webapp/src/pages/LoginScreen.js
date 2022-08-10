import React from "react"
import styled from "styled-components"
import * as fcl from "@onflow/fcl"

import Button from '../components/Button'

const Content = styled.div`
  font-size: 15px;
  margin-bottom: 30px;
  font-size: 14px;
  line-height: 1.57;

  p {
    margin-block-start: 0;
  }

  &:last-child {
    margin-bottom: 0;
  }
`;

const LoginScreen = () => {

  return (
    <>
      <Content>
        <p>
          请依照提示，创建 NFT 钱包，领取你的纪念品 NFT。
        </p>
      </Content>
      <Button onClick={fcl.authenticate}>开始创建</Button>
    </>
  );
};

export default LoginScreen;
