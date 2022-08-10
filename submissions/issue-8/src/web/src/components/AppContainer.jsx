import Head from "next/head"
import PropTypes from "prop-types"
import Header from "src/components/Header"
import {BASE_HTML_TITLE} from "src/global/constants"
import "src/global/fclConfig"
import FlashMessage from "./FlashMessage"

export default function AppContainer({children}) {
  return (
    <div>
      <Head>
        <title>{BASE_HTML_TITLE}</title>
        <link
          rel="apple-touch-icon"
          sizes="180x180"
          href="/"
        />
        <link
          rel="icon"
          type="image/png"
          sizes="32x32"
          href="/"
        />
        <link
          rel="icon"
          type="image/png"
          sizes="16x16"
          href="/"
        />
        <link rel="manifest" href="/site.webmanifest" />
        <link rel="mask-icon" href="" color="#5bbad5" />
        <meta name="msapplication-TileColor" content="#da532c" />
        <meta name="theme-color" content="#ffffff" />
        <meta
          name="description"
          content=""
        />
      </Head>
      <Header />
      <FlashMessage />
      <main>{children}</main>
      <footer></footer>
    </div>
  )
}

AppContainer.propTypes = {
  children: PropTypes.node,
}
