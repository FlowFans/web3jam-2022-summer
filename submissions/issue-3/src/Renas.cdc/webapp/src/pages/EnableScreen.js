import React from "react"
import styled from "styled-components"
import Spinner from '../components/SpinnerCircular';

const Wrapper = styled.div`
  margin: 16px auto;
  display: flex;
  flex-direction: column;
  align-items: center;
`;

const StyledSpinner = styled(Spinner)`
  width: 100px;
  height: 100px;
  margin-bottom: 16px;
`;

const Message = styled.div`
  text-align: center;
  font-size: 14px;
`;

const EnableScreen = () => {

  return (
    <Wrapper>
      <StyledSpinner />

      <Message>
        正在创建 NFT 钱包
      </Message>
    </Wrapper>
  );
};

export default EnableScreen;
