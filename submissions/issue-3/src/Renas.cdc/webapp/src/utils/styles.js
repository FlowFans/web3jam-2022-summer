import { css } from 'styled-components';

export const condensedStyle = css`
  font-family: ${props => props.theme.FONT_CONDENSED};
  font-stretch: condensed;
`;

export const clickableStyle = css`
  cursor: pointer;
  user-select: none;
  position: relative;
  z-index: 1;

  &:after {
    content: '';
    position: absolute;
    border-radius: inherit;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    background-color: ${props => props.theme.COLOR_LIGHT_GRAY};
    pointer-events: none;
    opacity: 0;
    transition: .1s opacity;
    z-index: -1;
  }

  &:hover {
    &:after {
      opacity: 0.15;
    }
  }

  &:active, &:focus {
    &:after {
      opacity: 0.3;
    }
  }
`;

export const unclickableStyle = css`
  cursor: not-allowed;
  pointer-events: none;
  opacity: .6;

  a {
    pointer-events: none;
  }
`;
