import { useMemo, useState, useEffect } from 'react'

const useTimer = (refreshCycle = 10000) => {
    // Returns the current time
    // and queues re-renders every `refreshCycle` milliseconds (default: 100ms)

    const [nonce, setNonce] = useState(0)

    useEffect(() => {
        // Regularly set time in state
        // (this will cause your component to re-render frequently)
        const intervalId = setInterval(() => setNonce(nonce + 1), refreshCycle)

        // Cleanup interval
        return () => clearInterval(intervalId)

        // Specify dependencies for useEffect
    }, [nonce, refreshCycle])

    return useMemo(() => nonce, [nonce])
}

export default useTimer
