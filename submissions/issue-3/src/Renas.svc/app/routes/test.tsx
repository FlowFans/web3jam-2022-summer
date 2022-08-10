import { Box, Heading, HStack, Link as ChakraLink } from '@chakra-ui/react';
import { useTranslation } from "react-i18next";


export default function Index() {
  let { t } = useTranslation();
  return (
    <Box fontFamily="system-ui, sans-serif" lineHeight={1.4}>
      <Heading as="h1" color="blue.400">
        Test page {t("greeting")}
      </Heading>
      <HStack spacing={4}>

      </HStack>
    </Box>
  );
}
