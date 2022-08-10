import Link from 'next/link';
import { Arrow } from '../Arrow/Arrow';
import styles from './BackButton.module.scss';

export const BackButton = ({ url, onClick }) => {
  return (
    <div className={styles.backButton} onClick={onClick}>
      {url ? (
        <Link passHref href={url}>
          <a>
            <Arrow direction="left" inactive={false} />
          </a>
        </Link>
      ) : (
        <Arrow direction="left" inactive={false} />
      )}
    </div>
  );
};
