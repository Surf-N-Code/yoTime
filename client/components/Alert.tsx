import React, {useRef} from 'react';
import styles from '../styles/alert.module.css';
import {useGlobalMessaging} from "../services/GlobalMessaging.context";

type PaginationProps = {
    message: string,
    severity: string,
}

export const Alert = ({message, severity}: PaginationProps) => {
    const alertRef = useRef<HTMLDivElement>();
    const [messageState, messageDispatch] = useGlobalMessaging();
    const closeAlert = () => {
        messageDispatch({
            type: 'removeMessage',
        })
    }

    return (
        <div>
            <div className={`${styles.alert} fixed bottom-0 right-0 m-8 w-4/6 md:w-full max-w-sm z-50`}>
                <div className="close cursor-pointer flex items-start w-full p-4 bg-red-100 rounded shadow-lg text-red-600 text-m"
                     ref={alertRef}
                     title="close"
                     onClick={() => closeAlert()}
                >
                    <div>
                        {message}
                    </div>
                    <div className="pl-3 ml-auto">
                        <svg className="block fill-current text-teal-700 mt-1" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 18 18">
                            <path d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    )
}
