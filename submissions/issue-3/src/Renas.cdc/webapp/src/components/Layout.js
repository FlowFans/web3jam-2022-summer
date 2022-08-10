import React from 'react';
import styled from "styled-components"

import Card from '../components/Card'
import bloctoFull from '../assets/logo-blocto-full.svg';

const Wrapper = styled.div`
  display: flex;
  flex-direction: column;
  height: 100vh;
  width: 100vw;
  overflow-y: auto;

  ul, ol {
    margin-block-start: 0.5em;
    margin-block-end: 0.5em;
    padding-inline-start: 20px;
  }
`;

const Header = styled.div`
  flex: 0 0 80px;
  height: 80px;
  width: 100%;
  box-shadow: 0 1px 0 0 rgba(127, 127, 127, 0.1);
  background-color: rgba(255, 255, 255, 0.3);
  display: flex;
  flex-direction: row;
  align-items: center;
  padding: 0 26px;
  font-size: 15px;
  font-weight: 600;
  box-sizing: border-box;
`;

const Body = styled.div`
  padding: 15px;
  flex: 1 0 auto;
  display: flex;
  justify-content: center;
  align-items: center;
`;

const Img = styled.img`
  width: 100px;
`;

const Seperator = styled.div`
  width: 1px;
  height: 12px;
  margin: 8px 18px;
  background-color: #c0c0c0;
`;

const Title = styled.div`
  height: 60px;
  width: 100%;
  font-size: 16px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  border-bottom: 1px solid #efefef;
`;

const CardBody = styled.div`
  padding: 30px;
  position: relative;
`;

const Layout = ({ children, withTitle, width = '400px' }) => (
  <Wrapper>
    <Header>
      <Img src={bloctoFull} alt="logo" />
      <Seperator />
      领取 THiNG.FUND 纪念 NFT 凭证
    </Header>
    <Body>
      <Card width={width}>
        {withTitle && <Title>NFT 钱包启用</Title>}

        <CardBody>{children}</CardBody>
      </Card>
    </Body>
  </Wrapper>
)

export default Layout
