import React from 'react';
import { CssBaseline, ThemeProvider } from '@mui/material';
import dynamic from 'next/dynamic';
import * as fcl from '@onflow/fcl';
import { send as httpSend } from '@onflow/transport-http';
import { appWithI18Next } from 'ni18n';
import { soulmadeTheme } from '../soulmadeTheme';
import 'nprogress/nprogress.css';
import Layout from '../components/Layout';
import { ni18nConfig } from '../ni18n.config';
import '../styles/globals.css';
import Head from 'next/head';

const LoadingBar = dynamic(() => import('../common/LoadingBar'), { ssr: false });

fcl
  .config({
    'discovery.wallet': process.env.NEXT_PUBLIC_FCL_DISCOVERY_WALLET,
  })
  .put('accessNode.api', process.env.NEXT_PUBLIC_FCL_ACCESS_NODE_API)
  .put('challenge.handshake', process.env.NEXT_PUBLIC_FCL_CHALLENGE_HANDSHAKE)
  .put('sdk.transport', httpSend)
  .put('app.detail.title', 'SoulMade')
  .put('app.detail.icon', 'https://raw.githubusercontent.com/SoulMadeNFT/wiki/main/favicon.ico');

function MyApp({ Component, pageProps }) {
  return (
    <>
      <Head>
        <meta name="viewport" content="width=device-width,initial-scale=1" />
        <title>SoulMade</title>
      </Head>
      <ThemeProvider theme={soulmadeTheme}>
        <CssBaseline />
        <LoadingBar />
        <Layout>
          <Component {...pageProps} />
        </Layout>
      </ThemeProvider>
    </>
  );
}

export default appWithI18Next(MyApp, ni18nConfig);
