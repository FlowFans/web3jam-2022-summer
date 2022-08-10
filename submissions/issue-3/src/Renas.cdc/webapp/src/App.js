import React from 'react';
import { BrowserRouter, Route, Switch } from 'react-router-dom'
import styled from 'styled-components'
import WalletSetup from './pages/WalletSetup'
import Redeem from './pages/Redeem'
import Wallet from './pages/Wallet'

const Wrapper = styled.div`
  font-size: 13px;
  min-height: 100vh;
  background-repeat: no-repeat;
  background-image: radial-gradient(circle at 55% 44%, rgb(217, 238, 253), rgb(255, 255, 255) 91%);
  display: flex;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
`;

function App() {
  return (
    <Wrapper>
      <BrowserRouter>
        <Switch>
          <Route exact path="/enable" component={WalletSetup} />
          <Route exact path="/redeem" component={Redeem} />
          <Route exact path="/wallet" component={Wallet} />
          <Route component={WalletSetup} />
        </Switch>
      </BrowserRouter>
    </Wrapper>
  );
}

export default App;
