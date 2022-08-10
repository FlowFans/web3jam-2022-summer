import Link from 'next/link';
import { LazyImage } from '../../common/LazyImage';
import { Header } from '../Header/Header';
import styles from './Layout.module.scss';

export default function Layout({ children }) {
  return (
    <div className={styles.layout}>
      <Header />

      {children}
      <footer className={styles.footer}>
        <div className={styles.top}>
          <div className={styles.info}>
            <div className={styles.brand}>
              <div style={{ width: 68, height: 68, position: 'relative' }}>
                <LazyImage asset="/images/logo_bi@2x.png" />
              </div>
              <div className={styles.logoText}>SoulMade</div>
            </div>

            <div className={styles.contact}>
              <div>
                <Link passHref href="https://twitter.com/soulmade_nft">
                  <a target="_blank">contact us</a>
                </Link>
              </div>
              <div>
                <Link passHref href="https://5mf23nou4th.typeform.com/to/Je3tRjhg">
                  <a target="_blank">Artist Application</a>
                </Link>
              </div>
              <div>
                <Link passHref href="https://wiki.soulmade.art/">
                  <a target="_blank">FAQ</a>
                </Link>
              </div>
              <div>
                <Link passHref href="https://wiki.soulmade.art/legal/terms-of-service">
                  <a target="_blank">term & condition</a>
                </Link>
              </div>
              <div>
                <Link passHref href="https://twitter.com/soulmade_nft">
                  <a target="_blank">Twitter</a>
                </Link>
              </div>
            </div>
          </div>

          <div className={styles.links}>
            {/* <div className={styles.link}>
              <AiFillLinkedin size={32} />
            </div>
            <div className={styles.link}>
              <AiFillInstagram size={32} />
            </div> */}

            <div className={styles.power}>
              <div style={{ width: 43, height: 43, position: 'relative' }}>
                <LazyImage asset="/images/flow-demo.png" />
              </div>
              <div className={styles.powerText}>Built on Flow</div>
            </div>

            <div className={styles.power}>
              <div style={{ width: 43, height: 43, position: 'relative' }}>
                <LazyImage asset="/images/graffle.png" />
              </div>
              <div className={styles.powerText}>Powered by Graffle</div>
            </div>

            {/* <div className={styles.link}>
              <Link passHref href="https://twitter.com/soulmade_nft">
                <a target="_blank">
                  <AiFillTwitterSquare size={66} />
                  <div className={styles.logoText}>Twitter</div>
                </a>
              </Link>
            </div> */}
          </div>
        </div>

        <div className={styles.bottom}>©2022 Meta Soul AB. All Rights Reserved.</div>
      </footer>
    </div>
  );
}
