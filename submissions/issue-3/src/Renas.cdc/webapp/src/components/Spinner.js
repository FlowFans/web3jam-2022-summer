import React from 'react';
import styled, { keyframes } from 'styled-components';

const bounce = keyframes`
  0%, 80%, 100% {
    transform: scale(0);
  }

  40% {
    transform: scale(1);
  }
`;

const Wrapper = styled.div`
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  text-align: center;
  align-items: center;
  justify-content: center;
`;

export const Ball = styled.div`
  width: 9px;
  height: 9px;
  margin: 2px;
  background-color: #FFFFFF;
  border-radius: 100%;
  display: inline-block;
  animation: ${bounce} 1.4s infinite ease-in-out both;
  animation-delay: ${props => props.index * 0.16}s;
`;

const Spinner = ({ className }: { className: string }) => (
  <Wrapper className={className}>
    <Ball index={0} />
    <Ball index={1} />
    <Ball index={2} />
  </Wrapper>
);

export default Spinner;
