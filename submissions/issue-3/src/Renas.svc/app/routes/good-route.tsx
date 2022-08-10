import { Box, Heading } from '@chakra-ui/react';
import type { MetaFunction } from 'remix';

// https://remix.run/api/conventions#meta
export const meta: MetaFunction = () => {
  return {
    title: 'Good Route',
    description: 'Good route',
  };
};

export function CatchBoundary() {
  return (
    <Box bg="yellow.500">
      <Heading as="h2">I caught some condition</Heading>
    </Box>
  );
}

export function ErrorBoundary() {
  return (
    <Box bg="red.400" color="white">
      <Heading as="h2">Something is really wrong!</Heading>
    </Box>
  );
}

export default function GoodRoute() {
  return <Box bg="green.400">I'm a real route!</Box>;
}
