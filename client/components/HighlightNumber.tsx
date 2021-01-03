import React, {useEffect, useState} from "react";
import 'react-date-picker/dist/DatePicker.css';
import 'react-calendar/dist/Calendar.css';

export const HighlightNumberCard = ({title, number, numberPostFix, href}) => {

    return (
        <div className="p-6 mt-3 rounded-md shadow-md bg-white flex w-full md:w-5/12 flex-col items-center">
            <div className="text-4xl font-bold">{number} {numberPostFix}</div>
            <div className="flex items-center mt-2">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" className="w-6 h-6 mr-1">
                    <path fill={"#A5B3BB"}
                          d="M5 4h14a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6c0-1.1.9-2 2-2zm0 5v10h14V9H5z"/>
                    <path fill={"#0D2B3E"}
                          d="M13 13h3v3h-3v-3zM7 2a1 1 0 0 1 1 1v3a1 1 0 1 1-2 0V3a1 1 0 0 1 1-1zm10 0a1 1 0 0 1 1 1v3a1 1 0 0 1-2 0V3a1 1 0 0 1 1-1z"/>
                </svg>
                <div className="text-lg text-gray-400 font-bold">{title}</div>
            </div>
        </div>
    )
}
