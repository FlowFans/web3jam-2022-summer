import React from 'react'

export default function index(props) {
  const {number,status} = props
  const getNumber = ()=>{
    if(status){
      props.getNumber({number})
    }else{
      alert("Please select other number")
    }
  }
  return (
    <div className='text-white text-center my-5 mr-4 cursor-default' onClick={getNumber}>
        {status? <div className=" bg-card-true px-7 py-3">{number}</div>
        :<div className=" bg-card-false px-7 py-3 cursor-not-allowed">{number}</div>
        }
    </div>    
  )
}
