import React from 'react';
import styled from 'styled-components';
import * as fcl from '@onflow/fcl';
import SVG from '../components/SVG';
import copy from '../images/ic-copy.svg';
import { clickableStyle } from '../utils/styles';

const Wrapper = styled.div`
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
`;

// const Title = styled.div`
//   font-size: 16px;
//   font-weight: 600;
//   margin-bottom: 24px;
// `;

const InnerWrapper = styled.div`
  margin-bottom: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
`;

const AddressWrapper = styled.div`
  width: 218px;
  height: 38px;
  margin-top: 23px;
  border-radius: 10px;
  font-size: 14px;
  padding: 11px 50px 11px 16px;
  display: flex;
  position: relative;
  align-items: center;
  border: solid 1px #EFEFEF;
  background: repeating-linear-gradient(45deg, #F5F5F5, #F5F5F5 5px, #EFEFEF 5px, #EFEFEF 6px);
  color: #141414;
  box-sizing: border-box;
`;

const Button = styled.div`
  ${clickableStyle};

  right: -1px;
  top: -1px;
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: #1336BF;
  display: flex;
  justify-content: center;
  align-items: center;
  position: absolute;
`;

const Message = styled.div`
  margin-top: 15px;
  text-align: center;
  font-size: 13px;
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
  color: rgb(42, 74, 197);
  font-size: 14px;
  margin-top: 20px;
  padding: 8px 10px;
  border-radius: 5px;
  flex: 0 0 auto;
  font-weight: 600;
  width: 100%;
  height: fit-content;
  box-sizing: border-box;

  &:hover {
    background-color: rgb(0, 0, 0, 0.05);
  }
`;

const EnabledScreen = ({ address }) => {
  const handleCopy = () => {
    const holder = document.createElement('textarea');
    holder.value = address;
    holder.setAttribute('readonly', '');
    holder.style.position = 'fixed';
    holder.style.left = '-9999px';
    document.body.appendChild(holder);
    holder.select();
    document.execCommand('copy');
    document.body.removeChild(holder);

    alert('地址复制成功')
  };

  return (
    <Wrapper>
      {/* <Title>NFT 钱包创建完成</Title> */}
      <InnerWrapper>
        <AddressWrapper>
          {address}

          <Button onClick={handleCopy}>
            <SVG src={copy} />
          </Button>
        </AddressWrapper>

        <Message>
          请复制上方地址<br />
          提交给 THiNG.FUND 的工作人员
        </Message>

      </InnerWrapper>

      <ActionButton onClick={fcl.unauthenticate}>更换帐户</ActionButton>
    </Wrapper>
  );
};

export default EnabledScreen;
