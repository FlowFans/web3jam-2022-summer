import { extendTheme } from "@chakra-ui/react"
import { styles } from "./styles"
import { config } from "./config"
import { borders } from "./foundations/borders"
import { Button } from "./components/button"
import { Text } from "./components/text"
import { Heading } from "./components/heading"
import { colors } from "./foundations/colors"

const theme = extendTheme({
  config,
  styles,
  colors,
  borders,
  components: {
    Button,
    Text,
    Heading,
  },
})

export default theme
