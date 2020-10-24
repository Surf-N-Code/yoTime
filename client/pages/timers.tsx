import React, { useState } from 'react';
import Layout from '../components/layout';
import useSWR from "swr";
import {differenceInDays, differenceInSeconds, format} from 'date-fns';
import isToday from 'date-fns/isToday';
import cn from 'classnames';
import { useRouter } from 'next/router';
import { Pagination } from '../components/pagination';

export const Timers = () => {
    const forceUpdate = useForceUpdate();

    const router = useRouter();
    const currentPage = Number(typeof router.query.page !== 'undefined' ? router.query.page : 1);
    const url = 'https://localhost:8443/users/1/timers?order[dateStart]&page='+currentPage;
    const fetcher = (...args) => fetch(...args).then(res => res.json());

    const { data, error } = useSWR(url, fetcher)

    if (error) {
        const content = <div>failed to load</div>;
    }

    if (!data) {
        const content = <div>loading...</div>;
    }


    let timersNormalized = {};
    let totalSecondsPerDay = {};
    let runningTimer = false;
    console.log(data);
    return (
        <Layout>
            {error ? <div>failed to load</div> :
            !data ? <div>loading...</div> :
            typeof data['hydra:member'] === 'undefined' || data['hydra:member'].length === 0 ? <div>no data yet...</div> :
            <div className="mt-6">
                <div>
                    {prepareTimerData(data, totalSecondsPerDay, timersNormalized)}

                    {Object.entries(timersNormalized).map(([dateStartString, timers]) => {
                        const dateStart = new Date(dateStartString);

                        const formattedDiffInMinPerDay = toHHMMSS(totalSecondsPerDay[dateStartString]);
                        return (
                            <div key={dateStartString} className="bg-white p-3 mb-1 border-gray-300 border-l-4 hover:border-teal-600">
                                <>
                                    <div className="text-xs text-gray-500">{format(dateStart, 'dd LLLL uuuu' )}</div>
                                    <div className="flex flex-row items-center">
                                        <div className={`text-xl${cn({' text-yt_orange font-bold': isToday(dateStart)}, {' text-gray-900': !isToday(dateStart)})}`}>{isToday(dateStart) ? 'Today' : format(dateStart, 'iiii' )}</div>

                                        <div className="ml-auto">{formattedDiffInMinPerDay}</div>
                                    </div>
                                </>
                                {timers.map((timer, index) => {
                                    const formattedDiffInMinPerDay = toHHMMSS(timer.diffInSeconds);
                                    timer.isRunning ? runningTimer = timer : null;
                                    return generateSubTimerHtml(timer, formattedDiffInMinPerDay);
                                })}
                            </div>
                        )
                    })}
                </div>
                <div>
                    <Pagination
                        currentPage={currentPage}
                        totalPages={Math.ceil(data['hydra:totalItems'] / 30)}
                    />
                </div>
                {/*<Notification/>*/}
                {runningTimer ?
                    <div
                        className="fixed bottom-0 notification bottom bg-red-500 rounded-full p-4 border-teal-400 border-2 shadow-md cursor-pointer"
                        onClick={() => stopTimer(forceUpdate, runningTimer)}>
                        <img src="../images/icons/icons8-stop-48.png" width="30" height="30" alt="Stop Timer"/>
                    </div>
                    :
                    <div
                        className="fixed bottom-0 notification bottom bg-teal-500 rounded-full p-4 border-teal-700 border-2 shadow-md cursor-pointer"
                        onClick={() => startTimer(forceUpdate)}>
                        <img src="../images/icons/icons8-play-100.png" width="30" height="30" alt="Start Timer"/>
                    </div>
                }
            </div>
            }
        </Layout>
    );
}

//TO-DO
/**
 * only allow user to see its own entries!
 * play button to start timer.
 * running timer - update minutes and seconds
 * jump to first page when adding timer
 * add timer and refresh directly to show new timer
 * dynamic user id when generating a timer
 */

//DONE
/**
 * no timers at all handling
 * wrong multi day timer
 * show seconds
 * add timer fixtures
 * paging of timers
 *
 * cleanup paging
 * add hover effect on timer rows
 * menu - highlight current selected menu element
 * format start and endtime on timer nicely
 * bottom right play icon to start work timer. Show stop icon if timer is running on all pages
 * stop timer on icon click and refresh view
 */

const stopTimer = async (forceUpdate, timer) => {
    timer.date_end = new Date();
    const res = await fetch('https://localhost:8443/timers/'+timer.id, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/merge-patch+json',
            'Accept': 'application/json',
        },
        body: JSON.stringify(timer),
    })

    const json = await res.json()
    if (json.errors) {
        throw new Error('Failed to fetch API')
    }
    forceUpdate();
}

const startTimer = async (forceUpdate) => {
    const timer = {
        user: '/users/1',
        date_start: new Date(),
        date_end: null,
        timerType: 'punch'
    }
    const res = await fetch('https://localhost:8443/timers', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/ld+json',
            'Accept': 'application/json',
        },
        body: JSON.stringify(timer),
    })

    const json = await res.json()
    if (json.errors) {
        console.error(json.errors)
        throw new Error('Failed to fetch API')
    }
    forceUpdate();
}

function useForceUpdate(){
    const [value, setValue] = useState(0); // integer state
    return () => setValue(value => ++value); // update the state to force render
}

const generateSubTimerHtml = (timer, formattedDiffInMinPerTimer: string) => {
    const dateStart = new Date(timer.date_start);

    let diffInDays = 0;
    let dateEndString = '';
    if (!timer.isRunning) {
        diffInDays = differenceInDays(new Date(timer.date_end), dateStart);
        dateEndString = format(new Date(timer.date_end), 'HH:mm');
    }

    return (
        <div className="flex flex-row items-center ml-2 mt-1">
            <div className="text-sm">{format(dateStart, 'HH:mm')}  -  {!timer.isRunning ? diffInDays > 0 ? dateEndString + ' +'+ diffInDays : dateEndString : '...'}</div>
            <div className="text-sm ml-auto text-gray-600">{formattedDiffInMinPerTimer}</div>
        </div>
    )
}

const prepareTimerData = (data, totalSecondsPerDay, timersNormalized) => {
    {data['hydra:member'].map((value, key) => {
        typeof value.date_end !== 'undefined' ? value.isRunning = false : value.isRunning = true;
        const dateStartStr = format(new Date(value.date_start), 'u-MM-dd');

        let diffInSeconds = 0;
        if (!value.isRunning) {
            diffInSeconds = differenceInSeconds(new Date(value.date_end), new Date(value.date_start));
        } else {
            diffInSeconds = differenceInSeconds(new Date(), new Date(value.date_start));
        }
        value.diffInSeconds = diffInSeconds;

        if (totalSecondsPerDay.hasOwnProperty(dateStartStr)) {
            totalSecondsPerDay[dateStartStr] += diffInSeconds
        } else {
            totalSecondsPerDay[dateStartStr] = diffInSeconds;
        }

        if (typeof timersNormalized[dateStartStr] === 'undefined') {
            timersNormalized[dateStartStr] = [];
        }
        timersNormalized[dateStartStr].push(value);
    })}

}

const toHHMMSS = (seconds: number) => {
    let h   = Math.floor(seconds / 3600)
    let m = Math.floor(seconds / 60) % 60
    let s = seconds % 60

    return [h,m,s]
        .map(v => v < 10 ? "0" + v : v)
        .join(":")
}

export default Timers;
