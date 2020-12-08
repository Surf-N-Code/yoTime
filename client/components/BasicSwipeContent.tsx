import React from 'react';
import PropTypes from 'prop-types';

import styles from './BasicSwipeContent.module.css';

const BasicSwipeContent = ({ label, position }) => (
  <div className={styles.left}>
    <span>{label}</span>
  </div>
);

BasicSwipeContent.propTypes = {
  label: PropTypes.string,
  position: PropTypes.oneOf(['left', 'right']).isRequired
};

export default BasicSwipeContent;
