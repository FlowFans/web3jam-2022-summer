import { useDevice } from './useDevice';

const COMPOSER_SIZE = {
  mobile: 300,
  tablet: 500,
  desktop: 300,
  largeDesktop: 400,
};

export const useComposerSize = () => {
  const device = useDevice();
  return COMPOSER_SIZE[device];
};
