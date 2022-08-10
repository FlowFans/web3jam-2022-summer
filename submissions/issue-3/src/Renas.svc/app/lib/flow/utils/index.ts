import { createStandaloneToast } from '@chakra-ui/react'
import moment from 'moment'
import Big from 'big.js'
import axios from 'axios'
import * as fcl from '@onflow/fcl'
// import { send as httpSend } from '@onflow/transport-http'
import { send as grpcSend } from '@onflow/transport-grpc'

import {
  nodeUrl,
  flowTokenAddr,
  flowFungibleAddr,
  fusdTokenAddr,
  flowNonFungibleAddr,
  discoveryUrl,
  network,
  emeraldIdAddress,
  discoveryEndpointUrl,
} from '../constants'

export const fclinit = () => {
  fcl
    .config()
    .put('discovery.wallet', discoveryUrl)
    // .put('discovery.authn.endpoint', discoveryEndpointUrl)
    .put('sdk.transport', grpcSend)
    .put('env', network)
    .put('flow.network', network)
    .put('accessNode.api', nodeUrl)
    .put('0xNonFungibleToken', flowNonFungibleAddr)
    .put('0xFungibleToken', flowFungibleAddr)
    .put('0xFlowToken', flowTokenAddr)
    .put('0xFUSD', fusdTokenAddr)
    .put('0xEmeraldIdentity', emeraldIdAddress)
    .put('0xEmeraldID', emeraldIdAddress)
    // .put('grpc.metadata', { api_key: alchemyKey })
    .put('app.detail.title', 'Flowns')
    .put(
      'app.detail.icon',
      'https://trello.com/1/cards/624713879fd8c23f0395c63d/attachments/6247139af2071076c7d74c93/previews/6247139bf2071076c7d74cb3/download/logo192.png',
    )
}

export const getNowTimestamp = () => {
  return new Date().getTime()
}

export const timeformater = (timestamp: number, formater: string) => {
  const time = Number(timestamp)
  if (time === 0) return ''
  return moment(time * 1000).format(formater || 'YYYY-MM-DD hh:mm:ss')
}

export const formatNumber = (num: number) => {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

export const formatBalance = (amount = '0', decimal = 18) => {
  const num = Big(amount).div(10 ** decimal)
  return num.toFixed(2)
}

export const getQuery = async (url: string, params = {}, headers = {}) => {
  try {
    const data = await axios.get(url, { params, headers })
    return data.data
  } catch (error) {
    console.log(error)
    return {}
  }
}

export const putReq = async (url: string, params = {}, headers = {}) => {
  try {
    const data = await axios.put(url, params, { headers })
    return data.data
  } catch (error) {
    console.log(error)
    return {}
  }
}

export const postReq = async (url: string, params = {}, headers = {}) => {
  try {
    const data = await axios.post(url, params, { headers })
    return data.data
  } catch (error) {
    console.log(error)
    return {}
  }
}

// const toastStandalone = createStandaloneToast()
// export const toast = ({
//   title = '',
//   desc = '',
//   status = 'success',
//   duration = 3000,
//   isClosable = true,
//   position = 'top',
// }) => {
//   toastStandalone({
//     position: position,
//     title,
//     description: desc,
//     status,
//     duration,
//     isClosable,
//   })
// }

export function getFlowScanLink(chainType: string, data: string, type: string) {
  const host = chainType === 'mainnet' ? `https://flowscan.io` : ``

  switch (type) {
    case 'transaction': {
      return `${host}/tx/${data}`
    }
    case 'token': {
      return `${host}/token/${data}`
    }
    case 'block': {
      return `${host}/block/${data}`
    }
    case 'address':
    default: {
      return `${host}/address/${data}`
    }
  }
}

// export const preloadFonts = (id: number) => {
//   return new Promise((resolve) => {
//     WebFont.load({
//       typekit: {
//         id: id,
//       },
//       active: resolve,
//     })
//   })
// }

export const randomNumber = (min: number, max: number) =>
  Math.floor(Math.random() * (max - min + 1) + min)

// todo add server sign and client sign
export const sendTrx = async (CODE: string, args = [], opt: any = {}) => {
  const authz = opt.authz || fcl.authz

  const txId = await fcl
    .send([
      fcl.transaction(CODE),
      fcl.args(args),
      fcl.proposer(authz),
      fcl.payer(authz),
      fcl.authorizations([authz]),
      fcl.limit(opt.limit || 9999),
    ])
    .then(fcl.decode)

  return txId
}

// export const throttle = (fn: any, delay: number) => {
//   let previous = 0
//   // 使用闭包返回一个函数并且用到闭包函数外面的变量previous
//   return function () {
//     var _this: any = this
//     var args = arguments
//     var now: Date = new Date()
//     if (now - previous > delay) {
//       fn.apply(_this, args)
//       previous = now
//     }
//   }
// }

// export const debounce = (func, wait) => {
//   let timer
//   return function () {
//     var context = this // 注意 this 指向
//     var args = arguments // arguments中存着event

//     if (timer) clearTimeout(timer)

//     timer = setTimeout(function () {
//       func.apply(context, args)
//     }, wait)
//   }
// }

export const execScript = async (script: string, args = []) => {
  return await fcl.send([fcl.script`${script}`, fcl.args(args)]).then(fcl.decode)
}

export const camelize = (str: string) => {
  return str
    .replace(/(?:^\w|[A-Z]|\b\w)/g, function (word, index) {
      return index === 0 ? word.toUpperCase() : word.toUpperCase()
    })
    .replace(/\s+/g, '')
}

export const ellipseAddress = (address = '', width = 3) => {
  return `${address.slice(0, width)}...${address.slice(-width)}`
}

export const ellipseStr = (str = '', start = 8, end = 8) => {
  return `${str.slice(0, start)}...${str.slice(-end)}`
}

export const isFlowAddr = (str = '') => {
  return /^0x[0-9a-f]{16}$/.test(str)
}

export const isDiscordId = (str = '') => {
  return /^\d{18}$/.test(str)
}

export const firstUpperCase = (value: string) => {
  return value.replace(/\b(\w)(\w*)/g, function ($0, $1, $2) {
    return $1.toUpperCase() + $2.toLowerCase()
  })
}

export const validateKey = (key: string) => {
  const reg = /^[_a-zA-Z][_a-zA-Z0-9]*$/
  return reg.test(key)
}

export const validateAddress = (key: number, address = '') => {
  if (key == undefined) return false
  address = address.toLowerCase()
  const flowReg = /^0x[0-9a-f]{16}$/
  const ethReg = /^0x[0-9a-f]{40}$/
  let reg = null
  key = Number(key)
  switch (key) {
    case 0:
      reg = flowReg
      break
    case 1:
      reg = ethReg
      break
    default:
      reg = null
  }
  return reg?.test(address)
}
