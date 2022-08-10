import React from 'react';
import PropTypes from 'prop-types';
import styled from 'styled-components';
import arrayOf from '../utils/arrayOf';

const BAR_COUNT = 12;

const Wrapper = styled.div`
  svg {
    width: 100%;
    height: 100%;
  }

  rect {
    fill: #1336bf;
  }
`;

const Spinner = ({ className }) => (
  <Wrapper className={className}>
    <svg xmlns="http://www.w3.org/2000/svg" xmlnsXlink="http://www.w3.org/1999/xlink" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
      {arrayOf(BAR_COUNT)
        .map(index => (
          <g key={index} transform={`rotate(${index * (360 / BAR_COUNT)} 50 50)`}>
            <rect x={47} y={12} rx={4} ry={4} width={6} height={18}>
              <animate attributeName="opacity" values="1;0" dur="1s" begin={`${(index * (1 / BAR_COUNT)) - 1}s`} repeatCount="indefinite" />
            </rect>
          </g>
        ))
      }
    </svg>
  </Wrapper>
);

Spinner.propTypes = {
  className: PropTypes.string,
};

export default Spinner;
