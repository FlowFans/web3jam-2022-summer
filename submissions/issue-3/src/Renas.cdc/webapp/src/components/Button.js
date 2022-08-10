import React from 'react'
import styled from 'styled-components'
import Spinner from './Spinner';

const StyledButton = styled.button`
  padding: 18px;
  width: 100%;
  text-align: center;
  border-radius: 8px;
  outline: none;
  border: 1px solid transparent;
  font-weight: 600;
  text-decoration: none;
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
  letter-spacing: 1px;
  pointer-events: ${props => props.isDisabled ? 'none' : 'inherit'};
  opacity: ${props => props.isDisabled || props.isProcessing ? 0.5 : 1};

  &:hover {
    background-color: rgb(38, 66, 176);
  }
`

const ButtonText = styled.div`
  transition: .2s opacity;
  opacity: ${props => (props.isProcessing ? 0 : 1)};
`;

const StyledSpinner = styled(Spinner)`
  transition: .2s opacity;
  opacity: ${props => (props.isProcessing ? 1 : 0)};
`;

type ButtonProps = {
  className: String,
  onClick: Function,
  children: any,
  isDisabled: Boolean,
  isProcessing: Boolean,
};

const Button = ({ className, onClick, children, isDisabled, isProcessing }: ButtonProps) => (
  <StyledButton
    isDisabled={isDisabled}
    isProcessing={isProcessing}
    className={className}
    onClick={onClick}
  >
    <ButtonText isProcessing={isProcessing} >
      {children}
    </ButtonText>

    <StyledSpinner isProcessing={isProcessing} />
  </StyledButton>
);

export default Button
