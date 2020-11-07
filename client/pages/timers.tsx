import React, {useEffect, useState} from 'react';
import Layout from '../components/layout';
import useSWR from 'swr';
import {differenceInDays, differenceInSeconds, format} from 'date-fns';
import isToday from 'date-fns/isToday';
import cn from 'classnames';
import {useRouter} from 'next/router';
import {Pagination} from '../components/pagination';
import {fetcherFunc} from '../services/Fetcher.service';
import {useAuth} from "../services/Auth.context";
import TokenService from "../services/Token.service";

export const Timers = () => {
    const router = useRouter();
    const [auth, authDispatch] = useAuth();
    const [runningTimer, setRunningTimer] = useState(null);
    const [latestTimer, setLatestTimer] = useState(null);
    const currentPage = Number(typeof router.query.page !== 'undefined' ? router.query.page : 1);
    const url = `timers?order[dateStart]&page=${currentPage}`;
    const { data, error, isValidating, mutate: mutateTimers } = useSWR([url, auth.jwt, 'GET'], fetcherFunc)

    const getLatestTimer = () => {
        if (typeof data['hydra:member'] === 'undefined') return;
        const latestTimer = data["hydra:member"].reduce(function(prev, current) {
            return (prev.id > current.id) ? prev : current
        })
        setLatestTimer((prevVal) => latestTimer);
    }

    // const getRunningTimer = () => {
    //     if (typeof data['hydra:member'] === 'undefined') return;
    //     const timer = data["hydra:member"].filter((timer) => {
    //         return typeof timer.date_end === 'undefined' || timer.date_end === null
    //     })
    //     console.log(timer);
    //     setRunningTimer((prevVal) => timer);
    // }

    const prepareTimerData = () => {

        if (typeof data['hydra:member'] === 'undefined') return;

        {data['hydra:member'].map((timer, key) => {
            const dateStartStr = format(new Date(timer.date_start), 'u-MM-dd');

            const now = new Date();
            timer.diffInSeconds = differenceInSeconds(new Date(typeof timer.date_end !== 'undefined' ? timer.date_end : now), new Date(timer.date_start));

            if (totalSecondsPerDay.hasOwnProperty(dateStartStr)) {
                totalSecondsPerDay[dateStartStr] += timer.diffInSeconds
            } else {
                totalSecondsPerDay[dateStartStr] = timer.diffInSeconds;
            }

            if (typeof timersNormalized[dateStartStr] === 'undefined') {
                timersNormalized[dateStartStr] = [];
            }
            timersNormalized[dateStartStr].push(timer);
        })}
    }

    useEffect(() => {
        if (data) {
            if (typeof data.code !== 'undefined' && data.code === 401) {
                const tokenService = new TokenService();
                authDispatch({
                    type: 'removeAuthDetails'
                });

                tokenService.deleteToken();

                router.push('/');
            }

            getLatestTimer();
            // getRunningTimer();

            //@TODO this updates the seconds, however it throws an error when swr updates its cache again. Read this: https://reactjs.org/docs/hooks-effect.html
            // if (latestTimer !== null && (latestTimer.date_end === null || typeof latestTimer.date_end === 'undefined')) {
            //     return setInterval(() => {
            //         let diffInSeconds = differenceInSeconds(new Date(), new Date(latestTimer.date_start));
            //         setLatestTimer((prevVal) => {
            //             return {diffInSeconds, ...prevVal}
            //         })
            //     }, 1000);
            // }
        }
    }, [data]);


    const generateSubTimerHtml = (timer, formattedDiffInMinPerTimer: string) => {
        const dateStart = new Date(timer.date_start);
        const isRunning = typeof timer.date_end === 'undefined' || timer.date_end === null;

        let diffInDays = 0;
        let dateEndString = '';
        if (!isRunning) {
            diffInDays = differenceInDays(new Date(timer.date_end), dateStart);
            dateEndString = format(new Date(timer.date_end), 'HH:mm');
        }

        const timerStartString = format(dateStart, 'HH:mm');

        let timerEndString = '...';
        if (!isRunning) {
            timerEndString = dateEndString;
            if (diffInDays > 0) {
                timerEndString = dateEndString + ' +'+ diffInDays
            }
        }

        return (
            <div key={timer.id} className="flex flex-row items-center ml-2 mt-1">
                <div className="text-sm">{timerStartString}  -  {timerEndString}</div>
                <div className="text-sm ml-auto text-gray-600">{formattedDiffInMinPerTimer}</div>
            </div>
        )
    }

    if (error) {
        const content = <div>failed to load</div>;
        console.log('error in data', data);
        console.log('error in data', error);
        return content;
    }
    console.log(data);

    let timersNormalized = {};
    let totalSecondsPerDay = {};

    const stopTimer = async () => {
        console.group('STOP timer')
        console.log('latest timer in stop timer', latestTimer);
        let timer = {...latestTimer};
        timer.date_end = new Date();
        console.log('timer update with date end', timer);
        console.groupEnd()
        setLatestTimer((pervVal) => timer);
        await mutateTimers((data) => {
            let newData = {...data};
            console.log('hydra members', newData['hydra:member']);
            newData['hydra:member'][0].date_end = new Date();
            console.log('hydra members - after', newData['hydra:member']);
            return {...data, "hydra:member": [...newData['hydra:member']]};
        }, false);
        const res = await fetcherFunc(`/timers/${timer.id}`, auth.jwt, 'PATCH', timer, 'application/merge-patch+json');
    }

    const startTimer = async () => {
        console.group('start timer')
        console.log('latest timer in start timer', latestTimer);
        let id = latestTimer.id + 1 ?? 1;
        const timer = {
            date_start: new Date(),
            date_end: null,
            timer_type: 'work'
        }
        console.log('new timer to add', {id, timer});
        setLatestTimer(timer);
        console.log('latest timer in start timer - after update', latestTimer);
        console.groupEnd();
        await mutateTimers((data) => {
            const hydraMember = data['hydra:member'];
            return {...data, "hydra:member": [{id, ...timer}, ...hydraMember]};
        }, false);
        const res = await fetcherFunc('timers', auth.jwt, 'POST', timer);
    }

    if (data) prepareTimerData();

    return (
        <Layout>
            {error ? <div>Ups... there was an error fetching your timers</div> :
            !data ? <div>no data yet...</div> :
            typeof data['hydra:member'] === 'undefined' || data['hydra:member'].length === 0 ? <div>no data yet...</div> :
            <div className="mt-6">
                <div>
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
                                {timers.map((timer) => {
                                    const formattedDiffInMinPerDay = toHHMMSS(timer.diffInSeconds);
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
                {console.log(runningTimer)}
                {latestTimer !== null && (latestTimer.date_end === null || typeof latestTimer.date_end === 'undefined') ?
                // {runningTimer !== null && runningTimer.length !== 0 ?
                    <div
                        className="fixed bottom-0 notification bottom bg-red-500 rounded-full p-4 border-teal-400 border-2 shadow-md cursor-pointer"
                        onClick={() => stopTimer()}>
                        <img src="../images/icons/icons8-stop-48.png" width="30" height="30" alt="Stop Timer"/>
                    </div>
                    :
                    <div
                        className="fixed bottom-0 notification bottom bg-teal-500 rounded-full p-4 border-teal-700 border-2 shadow-md cursor-pointer"
                        onClick={() => startTimer()}>
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
 * running timer - update minutes and seconds
 * jump to first page when adding timer
 */

//DONE
/**
 * dynamic user id when generating a timer
 * add timer and refresh directly to show new timer
 * play button to start timer.
 * only allow user to see its own entries!
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





const toHHMMSS = (seconds: number) => {
    let h   = Math.floor(seconds / 3600)
    let m = Math.floor(seconds / 60) % 60
    let s = seconds % 60

    return [h,m,s]
        .map(v => v < 10 ? "0" + v : v)
        .join(":")
}

export default Timers;
