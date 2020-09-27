import React from 'react';
import styles from './loading.module.css';

export const Loader = () => {
    return (
        <div className={styles.laSquareJellyBox}>
            {/*"la-square-jelly-box la-2x"*/}
            <div></div>
            <div></div>
        </div>
    )
}
