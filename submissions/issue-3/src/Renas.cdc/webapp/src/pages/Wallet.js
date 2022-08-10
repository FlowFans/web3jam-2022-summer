import React, { useState, useEffect, useMemo } from "react"
import styled from "styled-components"
import * as fcl from "@onflow/fcl"

import Layout from '../components/Layout'
import ArtItem from '../components/ArtItem'

import logoFlow from '../assets/flow-logo.svg'
import useCheckItems from '../hooks/useCheckItems'
import LoginScreen from './LoginScreen'

const Content = styled.div`
  font-size: 14px;
  line-height: 1.57;

  p {
    margin-block-start: 0;
  }

  a {
    color: #333333;

    &:hover {
      text-decoration: none;
    }
  }

  &:last-child {
    margin-bottom: 0;
  }
`;

const InfoCard = styled.div`
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 12px;
  margin-bottom: 24px;
`;

const AccountControl = styled.div`
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
`;

const FlowLogo = styled.img`
  width: 40px;
  height: 40px;
`;

const WalletName = styled.div`
  width: initial;
  font-size: 12px;
  color: #333333;
  text-transform: uppercase;
`;

const WalletAddress = styled.a`
  color: #131313;
  font-weight: 500;
  font-size: 1rem;
`;

const ActionButton = styled.div`
  text-transform: uppercase;
  display: flex;
  justify-content: center;
  flex-wrap: nowrap;
  align-items: center;
  cursor: pointer;
  position: relative;
  z-index: 1;
  background-color: rgb(42, 74, 197);
  color: white;
  font-size: 14px;
  padding: 7px 12px;
  border-radius: 20px;
  flex: 0 0 auto;
  font-weight: 600;
  width: fit-content;
  height: fit-content;
  pointer-events: ${props => props.isDisabled ? 'none' : 'inherit'};
  opacity: ${props => props.isDisabled || props.isProcessing ? 0.5 : 1};

  &:hover {
    background-color: rgb(38, 66, 176);
  }
`;

const ContentQualified = styled(Content)`
  border-radius: 15px;
  background-color: #f6f6f6;
  border: 1px solid #eaeaea;
  padding: 18px;
  margin-bottom: 20px;
  padding-top: 20px;
  height: 400px;
  overflow: auto;
  display: flex;
  align-items: center;

  a {
    color: #333333;
  }

  li {
    margin-bottom: 4px;

    &:last-child {
      margin-bottom: 0;
    }
  }
`;

const Wallet = () => {
  const [user, setUser] = useState(null)
  const [arts, pass] = useCheckItems(user && user.addr)

  const isLoggedIn = useMemo(
    () => Boolean(user && user.addr),
    [user]
  )

  const handleChangeWallet = () => {
    fcl.unauthenticate()
  }

  useEffect(() => {
    fcl
      .currentUser()
      .subscribe(user => {
        setUser({ ...user });
      })
  }, [])

  if (!isLoggedIn) {
    return (
      <Layout withTitle>
        <LoginScreen />
      </Layout>
    )
  }

  return (
    <Layout>
      <InfoCard>
        <FlowLogo src={logoFlow} alt={'flow logo'} />
        <AccountControl>
          <WalletName>
            Flow 钱包地址
          </WalletName>

          <WalletAddress>
            {user.addr}
          </WalletAddress>
        </AccountControl>
        <ActionButton onClick={handleChangeWallet}>更换</ActionButton>
      </InfoCard>

      <Content>
        <ContentQualified>
          {arts.map(props => <ArtItem {...props} isArt />)}
          {pass.map(props => <ArtItem {...props} />)}
        </ContentQualified>
      </Content>
    </Layout>
  )
}

export default Wallet;
