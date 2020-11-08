import React, {useRef} from 'react';
import styles from '../styles/alert.module.css';

type PaginationProps = {
    message: string,
    severity: string,
    onClick: () => void;
}

export const Alert = ({message, severity, onClick}: PaginationProps) => {
    const alertRef = useRef<HTMLDivElement>();
    const closeAlert = () => {
        onClick();
        if (alertRef.current === null) return;
        alertRef.current.classList.add(styles.closed);
    }

    return (
        <div>
            {/*{messages.map((message) => {*/}
            {/*    return (*/}
                    <div className={`${styles.alert} fixed bottom-0 right-0 m-8 w-3/6 md:w-full max-w-sm z-10`}>
                        <div className="close cursor-pointer flex items-start w-full p-3 bg-red-200 rounded shadow-lg text-red-800"
                             ref={alertRef}
                             title="close"
                             onClick={() => closeAlert()}
                        >
                            <div>
                                {message}
                            </div>
                            <div className="ml-auto">
                                <svg className="block fill-current text-teal-700 mt-1" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 18 18">
                                    <path d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                {/*)*/}
            {/*})}*/}
        </div>
    )
}
