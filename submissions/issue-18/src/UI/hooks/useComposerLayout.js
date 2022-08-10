import { useDevice } from './useDevice';

const COMPOSER_LAYOUT = {
  mobile: 'vertical',
  tablet: 'horizontal',
  desktop: 'horizontal',
  largeDesktop: 'horizontal',
};

export const useComposerLayout = () => {
  const device = useDevice();
  return COMPOSER_LAYOUT[device];
};
