import React from 'react';
import Layout from '../components/layout';
import useSWR from "swr";
import { format } from 'date-fns';
import isToday from 'date-fns/isToday';
import differenceInMinutes from 'date-fns/differenceInMinutes';
import differenceInSeconds from 'date-fns/differenceInSeconds';
import cn from 'classnames';
import {log} from "util";

export const Timers = () => {
    const url = 'https://localhost:8443/timers?order[dateEnd]=desc';
    const fetcher = (...args) => fetch(...args).then(res => res.json());

    const { data, error } = useSWR(url, fetcher)

    if (error) {
        const content = <div>failed to load</div>;
    }

    if (!data) {
        const content = <div>loading...</div>;
    }


    let timers = {};
    let multipleTimersOnDay = {};
    let timersTest = [];
    let totalMinsPerDay = {};
    return (
        <Layout>
            {error ? <div>failed to load</div> :
                !data ? <div>loading...</div> :
            <div className="mt-6">

                {data['hydra:member'].map((value, key) => {
                    const dateEnd = new Date(value.date_end);
                    const dateStart = new Date(value.date_start);
                    const formattedDateEnd = format(dateEnd, 'u-MM-dd');

                    const diffInMin = differenceInMinutes(dateEnd, dateStart);

                    let sameday = false;
                    if (totalMinsPerDay.hasOwnProperty(formattedDateEnd)) {
                        sameday = true;
                        totalMinsPerDay[formattedDateEnd] += diffInMin
                        multipleTimersOnDay[formattedDateEnd] = true;
                    } else {
                        totalMinsPerDay[formattedDateEnd] = diffInMin;
                        multipleTimersOnDay[formattedDateEnd] = false;
                    }
                    if (typeof timersTest[formattedDateEnd] === 'undefined') {
                        timersTest[formattedDateEnd] =[];
                    }
                    timersTest[formattedDateEnd].push(value);
                    timers[key] = {'data' : value, 'sameDayTimer' : sameday};
                })}
                {/*{console.log(totalMinsPerDay)}*/}
                {/*{console.log(data)}*/}
                {/*{console.log(multipleTimersOnDay)}*/}
                {console.log(timersTest)}
                {data['hydra:member'].map((value, key) => {
                    const dateStart = new Date(value.date_start);
                    const dateEnd = new Date(value.date_end);
                    const formattedDateEnd = format(dateEnd, 'u-MM-dd');
                    let date = new Date(0);
                    date.setSeconds(totalMinsPerDay[formattedDateEnd]*60);
                    const formattedDiffInMinPerDay = date.toISOString().substr(11,5);

                    date = new Date(0);
                    date.setSeconds(differenceInMinutes(dateEnd, dateStart)*60);
                    const formattedDiffInMinPerTimer = date.toISOString().substr(11,5);
                    // console.log(multipleTimersOnDay);
                    // console.log(multipleTimersOnDay[formattedDateEnd]);

                    return (
                        <div key={value['@id']} className={`bg-white p-2 border-gray-300${cn({' border-t rounded-tl-lg rounded-tr-lg rounded-bl-lg rounded-br-lg': !multipleTimersOnDay[formattedDateEnd]})}`}>
                            {!timers[key]['sameDayTimer'] ?
                                <>
                                    <div className="text-xs text-gray-500">{format(dateStart, 'dd LLLL uuuu' )}</div>
                                    <div className="flex flex-row items-center">
                                        <div className={`text-xl${cn({' text-yt_orange font-bold': isToday(dateStart)}, {' text-gray-900': !isToday(dateStart)})}`}>{format(dateStart, 'iiii' )}</div>

                                        <div className="ml-auto">{formattedDiffInMinPerDay}</div>
                                    </div>
                                </> : null
                            }
                            <div className="flex flex-row items-center ml-2">
                                <div className="text-sm">{format(dateStart, 'HH:mm')} to {format(dateEnd, 'HH:mm')}</div>
                                <div className="text-sm ml-auto text-gray-600">{formattedDiffInMinPerTimer}</div>
                            </div>
                        </div>
                    )
                })}
            </div>}
        </Layout>
    );
}

export default Timers;
