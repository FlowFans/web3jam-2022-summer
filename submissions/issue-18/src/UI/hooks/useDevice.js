import { useMediaQuery, useTheme } from '@mui/material';

export const useDevice = () => {
  const theme = useTheme();
  const mobile = useMediaQuery(theme.breakpoints.down('sm'));
  const tablet = useMediaQuery(theme.breakpoints.between('sm', 'md'));
  const desktop = useMediaQuery(theme.breakpoints.between('md', 'lg'));
  const largeDesktop = useMediaQuery(theme.breakpoints.up('lg'));

  if (mobile) {
    console.log('mobile');
    return 'mobile';
  } else if (tablet) {
    console.log('tablet');
    return 'tablet';
  } else if (desktop) {
    console.log('desktop');
    return 'desktop';
  } else if (largeDesktop) {
    console.log('largeDesktop');
    return 'largeDesktop';
  } else {
    return 'unknown';
  }
};
