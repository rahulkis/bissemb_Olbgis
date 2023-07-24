import styled from 'styled-components';
import { HEFFALUMP, LORAX, OLAF } from './colors';

export default styled.button`
  ${props =>
    props.use === 'tertiary'
      ? `
  background-color: ${HEFFALUMP};
  border-color: ${HEFFALUMP};
  color: ${OLAF};`
      : `
  background-color: ${LORAX};
  border: 3px solid ${LORAX};
  color: ${OLAF};
  `}
  border-radius: 3px;
  font-size: 14px;
  line-height: 14px;
  padding: 12px 24px;
  font-family: Avenir Next W02, Helvetica, Arial, sans-serif;
  font-weight: 500;
  white-space: nowrap;
`;
