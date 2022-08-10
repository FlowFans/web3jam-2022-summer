import './index.less'
import React from 'react';

export default class CustomTitle extends React.Component<any>{
  constructor(props: any) {
    super(props);
  }

  render() {
    return (
      <div className='title'>
        <div
          className='first fontProperty'
          style={{transform: this.props.skew === true ? 'skew(-30deg)' : ''}}
        >{this.props.title}</div>
      </div>
    )
  }
}