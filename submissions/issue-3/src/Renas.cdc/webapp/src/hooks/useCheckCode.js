import { useState, useEffect } from 'react'

export default function useCheckCode(code, address, isProcessing) {
  const [error, setError] = useState('')

  if (!code) {
    setError('invalid_code')
  }

  useEffect(() => {
    fetch(`https://flow-wallet-testnet.blocto.app/api/flow/caa-check?code=${code}&address=${address}`)
      .then(response => response.json())
      .then(response => setError(response.error))
      .catch(() => setError('invalid_code'))
  }, [code, address, isProcessing])

  return error
}
