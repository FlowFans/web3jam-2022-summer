import { AbstractConnector } from "@web3-react/abstract-connector"
import { SUPPORTED_WALLETS } from "../../constants/wallet"
import { injected } from "../../connectors"
import { Button, Spacer, Stack, Text, VStack } from "@chakra-ui/react"
import { RepeatIcon } from "@chakra-ui/icons"

export default function PendingView({
  connector,
  error = false,
  setPendingError,
  tryActivation,
}: {
  connector?: AbstractConnector
  error?: boolean
  setPendingError: (error: boolean) => void
  tryActivation: (connector: AbstractConnector) => void
}) {
  const isMetamask = window?.ethereum?.isMetaMask

  return (
    <Stack spacing={8} pb={4}>
      {error ? <Text>Error Connecting</Text> : <Text>Initializing...</Text>}
      {Object.keys(SUPPORTED_WALLETS).map(key => {
        const option = SUPPORTED_WALLETS[key]
        if (option.connector === connector) {
          if (option.connector === injected) {
            if (isMetamask && option.name !== "MetaMask") {
              return null
            }
            if (!isMetamask && option.name === "MetaMask") {
              return null
            }
          }
          return (
            <Button
              isFullWidth={true}
              id={`connect-${key}`}
              key={key}
              icon={option.iconURL}
              disabled={!error}
              onClick={() => {
                setPendingError(false)
                connector && tryActivation(connector)
              }}
            >
              <Stack direction={"row"} w={"100%"} alignItems={"center"}>
                {error && <RepeatIcon color={option.color} />}
                <Text color={option.color}>{option.name}</Text>
                <Spacer />
                <VStack size={16} alignItems={"center"} justifyContent={"center"} mr={"8px"}>
                  <img src={option.iconURL} alt={"Icon"} width={24} height={24} />
                </VStack>
              </Stack>
            </Button>
          )
        }
        return null
      })}
    </Stack>
  )
}
