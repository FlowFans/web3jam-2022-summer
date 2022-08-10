import { css } from 'styled-components';

// size: http://getbootstrap.com/docs/4.0/layout/grid/#grid-options
const sizes = {
  xl: 1140,
  lg: 1139,
  md: 991,
  sm: 767,
  xs: 575,
  xxs: 350,
};

const media = Object.keys(sizes).reduce((acc, label) => {
  const attr = (label === 'xl') ? 'min-width' : 'max-width';
  acc[label] = (...args) => css`
    @media (${attr}: ${sizes[label]}px) {
      ${css(...args)}
    }
  `;

  return acc;
}, {});

export default media;
