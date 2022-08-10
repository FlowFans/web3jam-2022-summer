import { createTheme } from '@mui/material';

export const soulmadeTheme = createTheme({
  breakpoints: {
    values: {
      xs: 0,
      sm: 640,
      md: 768,
      lg: 1024,
      xl: 1280,
    },
  },
  palette: {
    primary: {
      main: '#2A252C',
    },
  },
  typography: {
    fontFamily: 'Fredoka',
  },
  components: {
    MuiButton: {
      defaultProps: {
        variant: 'contained',
      },
      // styleOverrides: {
      //   root: {
      //     borderRadius: '50px',
      //   },
      // },
    },
    MuiLoadingButton: {
      defaultProps: {
        variant: 'contained',
      },
      // styleOverrides: {
      //   root: {
      //     borderRadius: '6px',
      //     backgroundColor: '#2a252c',
      //     padding: 0,
      //     color: '#2a252c',
      //     fontFamily: 'Fredoka',
      //     fontSize: '20px',
      //     fontWeight: 'bold',
      //     borderRadius: '6px',
      //     padding: '13px 40px',
      //     letterSpacing: '2.6px',
      //     lineHeight: '30px',
      //     textAlign: 'center',
      //     color: '#ffffff',
      //     border: '2px solid #2a252c',
      //   },
      // },
    },
    MuiTabs: {
      defaultProps: {
        allowScrollButtonsMobile: true,
      },
      styleOverrides: {
        scrollButtons: {
          '&.Mui-disabled': { opacity: 0.3 },
        },
      },
    },
  },
});
