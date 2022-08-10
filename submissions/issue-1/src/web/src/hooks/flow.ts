import {useEffect, useMemo} from "react"
import * as fcl from "@onflow/fcl"
import {atom, useRecoilState} from "recoil"

interface FLOW_USER {
  addr?: string
  cid?: string
  f_vsn?: string
  f_type?: string
  loggedIn: boolean
  services?: any[]
}

const flowServicesAtom = atom<any[]>({
  key: "flow:services",
  default: [],
})

const activeUserAtom = atom<FLOW_USER>({
  key: "flow:active:user",
  default: {
    loggedIn: false,
  },
})

export const useActiveFlowReact = () => {
  const [user, setUser] = useRecoilState(activeUserAtom)
  const [flowServices, setFlowServices] = useRecoilState(flowServicesAtom)

  useEffect(() => fcl.currentUser.subscribe(setUser),
    [setUser])

  useEffect(() => fcl.discovery.authn.subscribe((res: any) => setFlowServices(res.results)),
    [setFlowServices]
  )

  const activeServiceName = useMemo(() => {
    if (user?.services && user?.services?.length > 0) {
      return user?.services[0].provider.name
    }
    return undefined
  }, [user])

  return {
    user,
    flowServices,
    activeServiceName,
  }
}
