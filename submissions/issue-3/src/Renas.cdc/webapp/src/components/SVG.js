// @flow
import React from 'react';
import PropTypes from 'prop-types';
import styled from 'styled-components';
import InlineSVG from 'react-inlinesvg';

export const StyledSVG = styled(InlineSVG)`
  display: flex;
  align-items: center;
`;

const SVG = ({ src, ...props }) => (src ? (
    <StyledSVG cacheGetRequests src={src} {...props} />
) : null);

SVG.propTypes = {
    src: PropTypes.string,
    onClick: PropTypes.func,
    className: PropTypes.string,
};

export default SVG;
