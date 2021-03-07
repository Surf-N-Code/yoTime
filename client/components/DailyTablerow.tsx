import {format} from "date-fns";
import React from "react";
import cn from 'classnames';
import {toHHMMSS} from "../utilities/lib";
import isToday from "date-fns/isToday";

export const DailyTablerow = ({daily, onClick}) => {
    const workSubBreak = daily.time_worked_in_s - daily.time_break_in_s;
    const formattedDiffInMinPerDayWork = toHHMMSS(workSubBreak > 0 ? workSubBreak : workSubBreak * -1);
    const formattedDiffInMinPerDayBreak = toHHMMSS(daily.time_break_in_s);
    const todayDaily = isToday(new Date(daily.start_time));
    return (
         <div key={daily.id} className="w-full bg-white p-3 mb-1 border-gray-300 border-l-4 hover:border-teal-600 rounded-md cursor-pointer" onClick={() => onClick(daily)}>
            <div className={"flex"}>
                <div className="flex flex-col">
                    <div className="text-xs text-gray-500">{format(new Date(daily.start_time), 'dd LLLL uuuu')}</div>
                    <div className={`text-2xl${cn({' text-yellow-500 font-bold': todayDaily})}`}>{todayDaily ? 'Today' : format(new Date(daily.start_time), 'iiii')}</div>
                    <div className="text-md mt-auto">{format(new Date(daily.start_time), 'HH:mm')} - {format(new Date(daily.end_time), 'HH:mm')}</div>
                </div>
                <div className="flex flex-col ml-auto items-end">
                    <div className="flex">
                        <div className={`${cn({'pill-green': daily.is_synced_to_personio}, {'pill-yellow': !daily.is_synced_to_personio})}`}>Personio</div>
                        <div className={`ml-2 ${cn({'pill-green': daily.is_email_sent}, {'pill-yellow': !daily.is_email_sent})}`}>Mail</div>
                    </div>
                    <div className="flex mt-7 items-center">
                        <div className="text-xs">({formattedDiffInMinPerDayBreak})</div>
                        <div className={`ml-2${cn({' text-red-500': workSubBreak < 0})}`}>{workSubBreak < 0 ? '-' : '' }{formattedDiffInMinPerDayWork}</div>
                    </div>
                </div>
            </div>
        </div>
    )
}
