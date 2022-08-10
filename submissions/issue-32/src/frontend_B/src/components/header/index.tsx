import styles from './index.less'
import logoR from '../../assets/icons/logoR.svg'
import logoAce from '../../assets/icons/logoAce.svg'
import React from 'react';


export default class Header extends React.Component{

    constructor(props: any) {
      super(props);
      this.state = {disableBtn: true}
    }
  
    render() {
      return (
        <div className={styles.header}>
            <img src={logoR} alt="" />
            <img src={logoAce} alt="" />
        </div>
      )
    }
  }