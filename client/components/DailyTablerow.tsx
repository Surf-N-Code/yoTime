import {format} from "date-fns";
import React from "react";
import cn from 'classnames';
import {toHHMMSS} from "../utilities/lib";

export const DailyTablerow = ({daily, onClick}) => {
    const dateString = format(new Date(daily.date), 'dd LLLL uuuu' );
    const formattedDiffInMinPerDay = toHHMMSS(daily.time_worked_in_s - daily.time_break_in_s);

    return (
        <div
            key={daily['@id']}
            className="bg-white p-3 mb-1 border-gray-300 border-l-4 hover:border-teal-600"
            onClick={() => onClick(daily)}
        >
            <>
                <div className="flex text-gray-900 mb-2">
                    <div>{dateString}</div>
                    <img src={`../images/icons/${cn({'mail-sent' : daily.is_email_sent}, {'mail-outstanding' : !daily.is_email_sent})}.png`} width="25" className="ml-auto" alt={`${cn({'Daily summary email sent' : daily.is_email_sent}, {'Daily summary email not sent yet' : !daily.is_email_sent})}`}/>
                    <img src={`../images/icons/${cn({'personio-fail' : daily.is_synced_to_personio}, {'personio-success' : !daily.is_synced_to_personio})}.png`} width="25" className="ml-3" alt={`${cn({'Successfully synced your daily summary info to Personio' : daily.is_email_sent}, {'Time is not synced to Personio yet.' : !daily.is_email_sent})}`}/>
                </div>
                <div className="flex flex-row items-center text-gray-900">
                    <div>{format(new Date(daily.start_time), 'HH:mm')} - {format(new Date(daily.end_time), 'HH:mm')}</div>
                    <div className="ml-auto">{formattedDiffInMinPerDay}</div>
                </div>
            </>
        </div>
    )
}
