import { Avatar } from '@mui/material';
import styles from './FilterButton.module.scss';

export const FilterButton = ({ series, imgSrc, selected, onClick }) => {
  const handleClick = series => () => {
    onClick(series);
  };

  return (
    <div
      className={styles.filterItem}
      style={{
        transform: selected ? 'scale(1.3)' : 'none',
      }}
    >
      <Avatar
        onClick={handleClick(series)}
        alt={series}
        src={imgSrc}
        sx={{
          width: 100,
          height: 100,
          border: '2px solid',

          margin: '0 8px',
          '&:hover': { cursor: 'pointer' },
        }}
      />
      <div>{series}</div>
    </div>
  );
};
