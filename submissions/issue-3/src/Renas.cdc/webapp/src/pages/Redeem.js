import React, { useState, useEffect, useMemo } from "react"
import styled from "styled-components"
import { parse } from 'query-string';
import * as fcl from "@onflow/fcl"

import Layout from '../components/Layout'
import Button from '../components/Button'

import { getAuth } from '../service/authorization'
import useCheckCode from '../hooks/useCheckCode'
import logoFlow from '../assets/flow-logo.svg'

import LoginScreen from './LoginScreen'

const setupTransacion = `\
import NonFungibleToken from 0x1d7e57aa55817448
import CaaPass from 0x98c9c2e548b84d31

transaction {
  prepare(signer: AuthAccount, admin: AuthAccount) {
    if signer.borrow<&CaaPass.Collection>(from: CaaPass.CollectionStoragePath) == nil {

      let collection <- CaaPass.createEmptyCollection() as! @CaaPass.Collection

      signer.save(<-collection, to: CaaPass.CollectionStoragePath)

      signer.link<&{NonFungibleToken.CollectionPublic, CaaPass.CollectionPublic}>(
        CaaPass.CollectionPublicPath,
        target: CaaPass.CollectionStoragePath)
    }

    let minter = admin
      .borrow<&CaaPass.Admin>(from: CaaPass.AdminStoragePath)
      ?? panic("Signer is not the admin")

    let nftCollectionRef = signer.getCapability(CaaPass.CollectionPublicPath)
      .borrow<&{NonFungibleToken.CollectionPublic}>()
      ?? panic("Could not borrow CAA Pass collection public reference")

    minter.mintNFT(recipient: nftCollectionRef, typeID: 10)
  }
}
`

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

const SubmitButton = styled(Button)`
  margin-top: 10px;
  padding: 14px;
`;

const ContentQualified = styled(Content)`
  border-radius: 15px;
  background-color: #f6f6f6;
  padding: 18px;
  margin-bottom: 20px;
  padding-top: 20px;

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

const Redeem = () => {
  const { code } = parse(window.location.search);

  const [user, setUser] = useState(null)
  const [isProcessing, setProcessing] = useState(false);
  const codeError = useCheckCode(code, user && user.addr, isProcessing);

  const isLoggedIn = useMemo(
    () => Boolean(user && user.addr),
    [user]
  )

  const auth = useMemo(
    () => getAuth(code, user && user.addr),
    [code, user]
  )

  const handleChangeWallet = () => {
    fcl.unauthenticate()
  }

  const handleClaim = async () => {
    setProcessing(true)

    const blockResponse = await fcl.send([
      fcl.getLatestBlock(),
    ])

    const block = await fcl.decode(blockResponse)

    try {
      const { transactionId } = await fcl.send([
        fcl.transaction(setupTransacion),
        fcl.proposer(fcl.currentUser().authorization),
        fcl.authorizations([
          fcl.currentUser().authorization,
          auth,
        ]),
        fcl.payer(fcl.currentUser().authorization),
        fcl.ref(block.id),
        fcl.limit(1000),
      ])

      const unsub = fcl
        .tx({ transactionId })
        .subscribe(transaction => {
          if (fcl.tx.isSealed(transaction)) {
            setProcessing(false)
            unsub()
          }
        })
    } catch (error) {
      console.error('signature failed', error);
      setProcessing(false)
    }
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

  let buttonMessage = '领取'
  if (codeError === 'full') {
    buttonMessage = '已被领取完毕'
  }

  if (codeError === 'invalid_code') {
    buttonMessage = '代码无效'
  }

  if (codeError === 'already_claimed') {
    buttonMessage = '已领取'
  }

  return (
    <Layout>
      <>
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
            请注意以下规则，领取 THiNG.FUND 纪念 NFT 凭证：
            <ul>
              <li>每位用户限领取 1 次。 </li>
              <li>每个领取代码限领取 100 枚纪念 NFT 凭证，领完为止。 </li>
              <li>你的代码是 {code}</li>
            </ul>
          </ContentQualified>

          <SubmitButton
            onClick={handleClaim}
            isDisabled={isProcessing || codeError}
            isProcessing={isProcessing}
          >
            {buttonMessage}
          </SubmitButton>
        </Content>
      </>
    </Layout>
  )
}

export default Redeem
