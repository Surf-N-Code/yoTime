import React, {useState} from 'react';
import Layout from '../components/layout';
import useSWR from 'swr';
import {differenceInDays, differenceInSeconds, format} from 'date-fns';
import isToday from 'date-fns/isToday';
import cn from 'classnames';
import {useRouter} from 'next/router';
import {Pagination} from '../components/pagination';
import {fetcherFunc} from '../services/Fetcher.service';
import {useAuth} from "../services/Auth.context";
import {v4} from 'uuid'; //Use for setting id of timer kacheln instead of datestart

export const Timers = () => {
    const forceUpdate = useForceUpdate();

    const router = useRouter();
    const [auth, setAuth] = useAuth();
    const currentPage = Number(typeof router.query.page !== 'undefined' ? router.query.page : 1);
    const url = `timers?order[dateStart]&page=${currentPage}`;
    const { data, error, isValidating, mutate: mutateTimers } = useSWR([url, auth.jwt, 'GET'], fetcherFunc)

    if (error) {
        const content = <div>failed to load</div>;
        console.log('error', error);
        console.log('error in data', data);
        return content;
    }

    let timersNormalized = {};
    let totalSecondsPerDay = {};
    let runningTimer = false;
    console.log('----DATA:----', data);


    const startTimer = async () => {
        let id = v4();
        const timer = {
            date_start: format(new Date(), 'u-MM-dd'),
            date_end: null,
            timer_type: 'work'
        }

        // console.group('before and after state update')
        // console.log(data);
        // const newHydraMembers = [{id, ...timer}, ...data['hydra:member']];
        // const newData = {...data, [...data['hydra:member'], timer]};
        // data['hydra:member'] = newHydraMembers;
        // const newData = {...data};
        // console.log(data);
        // console.log(newHydraMembers);
        // console.groupEnd()
        console.log('new data', {...data, "hydra:member": [data["hydra:member"], {id, ...timer}]});
        await mutateTimers((data) => {
            const hydraMember = data['hydra:member'];
            return {...data, "hydra:member": [{id, ...timer}, ...hydraMember]};

            // const newData = {...data, [...data['hydra:member'], timer]};
            // data['hydra:member'] = [{id, ...timer}, ...data['hydra:member']];
            // return {...data};
        }, false);

        // const res = await fetcherFunc('timers', auth.jwt, 'POST', timer);
        // forceUpdate();
    }

    // const startTimer = async () => {
    //     let id = v4();
    //     const timer = {
    //         date_start: new Date(),
    //         date_end: null,
    //         timer_type: 'work'
    //     }
    //
    //     console.log('adding timer to data array', [timer, ...data])
    //     await mutateTimers((data) => {
    //         return [{id, ...timer}, ...data];
    //     }, false);
    //     const res = await fetcherFunc('timers', auth.jwt, 'POST', timer);
    //
    //     // forceUpdate();
    // }

    return (
        <Layout>
            {error ? <div>Ups... there was an error fetching your timers</div> :
                !data ? <div>no data yet...</div> :
                    // isValidating ? <div className={"text-red-600 text-6xl"}>VALIDATING</div> :
                    // <div>
                    //     {data.map((e) => {
                    //         return (
                    //             <div key={e.id}>{e.id}</div>
                    //         )
                    //     })}
                    //     <div
                    //         className="red fixed bottom-0 right-0 notification bottom bg-teal-500 rounded-full p-4 border-teal-700 border-2 shadow-md cursor-pointer"
                    //         onClick={() => startTimer()}>
                    //         <img src="../images/icons/icons8-play-100.png" width="30" height="30" alt="Start Timer"/>
                    //     </div>
                    // </div>
            typeof data['hydra:member'] === 'undefined' || data['hydra:member'].length === 0 ? <div>no data yet...</div> :
            <div className="mt-6">
                <div>
                    {prepareTimerData(data, totalSecondsPerDay, timersNormalized)}

                    {Object.entries(timersNormalized).map(([dateStartString, timers]) => {
                        const dateStart = new Date(dateStartString);

                        const formattedDiffInMinPerDay = toHHMMSS(totalSecondsPerDay[dateStartString]);
                        return (
                            <div key={timers.id} className="bg-white p-3 mb-1 border-gray-300 border-l-4 hover:border-teal-600">
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
                        onClick={() => stopTimer(forceUpdate, runningTimer, auth.jwt)}>
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

const stopTimer = async (forceUpdate, timer, jwt) => {
    timer.date_end = new Date();
    const res = await fetcherFunc(`timers/${timer.id}`, jwt, 'PATCH', timer, 'application/merge-patch+json');
    // forceUpdate();
}
//
// const startTimer = async (forceUpdate, jwt, data, currentPage) => {
//     const timer = {
//         date_start: new Date(),
//         date_end: null,
//         timerType: 'work'
//     }
//
//     console.log(data);
//     const newData = {...data, timer: timer};
//     // data.TEST = 'HIER';
//     console.log(data);
//     await mutate(`https://localhost:8443/timers?order[dateStart]&page=${currentPage}`, {...data, timer: timer});
//
//     const res = await fetcherFunc('timers', jwt, 'POST', timer);
//     // forceUpdate();
// }

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
