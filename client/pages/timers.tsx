import React, {useEffect, useState} from 'react';
import useSWR, { mutate } from 'swr';
import {useRouter} from 'next/router';
import {differenceInDays, differenceInSeconds, format} from 'date-fns';
import isToday from 'date-fns/isToday';
import cn from 'classnames';
import {v4 as uuidv4} from 'uuid';
import { CSSTransition, TransitionGroup } from 'react-transition-group';
import { Transition } from 'react-transition-group';
import fetch from 'isomorphic-unfetch';
import {IsoFetcher} from '../services';

import {Alert, Layout, Pagination, ManualTimerview} from '../components';
import {FetcherFunc, TokenService, useAuth, useGlobalMessaging} from '../services';
import {ITimer, ITimerApiResult} from '../types';
import {toHHMMSS} from "../utilities";

import {GetServerSideProps} from "next";
import Cookies from "universal-cookie";
import {log} from "util";
import {sleep, utcDate} from "../utilities/lib";

const TimerData = (pageIndex, initialData) => {
    const [auth, authDispatch] = useAuth();
    const url = `/timers?order[dateStart]&page=${pageIndex}`;
    return useSWR<ITimerApiResult>([url, auth.jwt, 'GET'], FetcherFunc, {initialData})
}

export const Timers = ({validToken, initialData}) => {
    const router = useRouter();
    const [auth, authDispatch] = useAuth();
    const [manualTimerModalVisible, setManualTimerModalVisible] = useState(false);
    const [runningTimer, setRunningTimer] = useState<ITimer | null>(null);
    const [pageIndex, setPageIndex] = useState(1);
    const [messageState, messageDispatch] = useGlobalMessaging();
    const [timerToEdit, setTimerToEdit] = useState<ITimer|null>(null);
    const { data, error, mutate: mutateTimers } = TimerData(pageIndex, initialData);
    TimerData(pageIndex+1, initialData);

    useEffect(() => {
        if (data?.code === 401) {
            const tokenService = new TokenService();
            authDispatch({
                type: 'removeAuthDetails'
            });
            tokenService.deleteToken();
            router.push('/timers');
            return;
        }

        if (data?.["hydra:member"]) {
            const timer = data["hydra:member"].filter((timer) => {return typeof timer.date_end === 'undefined' || timer.date_end === null});
            setRunningTimer((prevTimer) => timer[0]);
        }
    }, [data])

    useEffect(() => {
        return updateTimerDurationUi(runningTimer);
    }, [runningTimer])

    const updateTimerDurationUi = (runTimer) => {
        if (typeof runTimer === 'undefined' || runTimer === null || typeof runTimer.timer_type === 'undefined' ) {
            return;
        }

        const timerSecondsUpdater = setInterval(() => {
            setRunningTimer((prevTimer) => {return {...runTimer}})
        }, 1000);

        if (runningTimer?.date_end && runningTimer?.date_end) {
            clearInterval(timerSecondsUpdater);
        }
        return () => clearInterval(timerSecondsUpdater);
    }

    const generateSubTimerHtml = (timer: ITimer, formattedDiffInMinPerTimer: string) => {
        const dateStart = new Date(timer.date_start);
        const isRunning = typeof timer.date_end === 'undefined' || timer.date_end === null;
        const timerStartString = format(dateStart, 'HH:mm');

        console.log(dateStart, timer.date_start);
        let diffInDays = 0;
        let timerEndString = '...';
        if (!isRunning) {
            diffInDays = differenceInDays(new Date(timer.date_end), dateStart);
            timerEndString = format(new Date(timer.date_end), 'HH:mm');
            diffInDays > 0 ? timerEndString += ' +'+ diffInDays : timerEndString;
        }

        return (
            <div
                className="flex flex-row items-center mt-1 py-1 cursor-pointer hover:bg-gray-50 rounded-md"
                key={timer.id}
                onClick={() => editTimer(timer)}
                data-id={timer.id}
            >
                <div className="pl-3">{timerStartString}  -  {timerEndString}{timer.timer_type === 'break' ? <span className="text-md"> 💤</span>:''}</div>
                <div className="text-right ml-auto pr-3">{formattedDiffInMinPerTimer}</div>
            </div>
        )
    }

    const editTimer = async (timer: ITimer) => {
        console.log(timer);
        setTimerToEdit(timer);
        toggleAddTimerView(true);
    }

    const deleteTimer = async (timerToDelete) => {
        await mutateTimers((data) => {
            let newData = {...data};
            return {...data, "hydra:member": [...newData["hydra:member"].filter(timer => timer.id !== timerToDelete.id)]};
        }, false);

        fetch(`https://localhost:8443/timers/${timerToDelete.id}`, {
            method: "DELETE",
            headers: {
                "content-type": "application/json",
                Authorization: 'Bearer ' + auth.jwt
            }
        })
        .then(res => {
            return true;
        })
        .catch(error => {
           return false;
        });
    }

    const stopTimer = async () => {
        if (!runningTimer) return;
        setRunningTimer((prevTimer) => null);

        let timer = {...runningTimer};
        timer.date_end = new Date();
        let timersToStop = [];
        let newData = {...data};
        await mutateTimers((data) => {
            newData["hydra:member"].map((timer) => {
                if (typeof timer.date_end === 'undefined' || timer.date_end === null) {
                    timer.date_end = new Date();
                    timersToStop.push(timer);
                }
            })
            return {...data, "hydra:member": [...newData['hydra:member']]};
        }, false);
        if (timersToStop.length > 1) {
            timersToStop.map((timer) => {
                FetcherFunc(`/timers/${timer.id}`, auth.jwt, 'PATCH', timer, 'application/merge-patch+json');
            })
        }
        await FetcherFunc(`/timers/${timer.id}`, auth.jwt, 'PATCH', timer, 'application/merge-patch+json');
    }

    const startTimer = async (timerType: string) => {
        stopTimer();
        let tempId = uuidv4();
        const newTimer = {
            date_start: new Date(),
            date_end: null,
            timer_type: timerType
        }
        setRunningTimer((prevTimer) => {return {id: tempId, 'optimisticTimer': true, ...newTimer}});

        await mutateTimers((data) => {
            let newHydra = [{id: tempId, ...newTimer}, ...data['hydra:member']].sort((a, b) => new Date(b.date_start).getTime() - new Date(a.date_start).getTime());
            return {
                ...data,
                'hydra:member': [ ... newHydra ]
            }
        }, false);

        const fetchedTimer = await FetcherFunc('timers', auth.jwt, 'POST', newTimer);
        setRunningTimer(fetchedTimer);

        await mutateTimers((data) => {
            return {
                ...data,
                'hydra:member': data['hydra:member'].map((timer) =>
                    timer.id === tempId ? fetchedTimer : timer
                )

            }
        }, false);
    }

    const toggleAddTimerView = (timerToEdit) => {
        if (manualTimerModalVisible || !timerToEdit) {
            setTimerToEdit(null);
        }
        setManualTimerModalVisible((prevVal) => {return !prevVal});
    }

    const totalPages = Math.ceil(data?.['hydra:member']?.length === 0 ? 1 : data['hydra:totalItems'] / 30);

    const hasTimer = typeof data !== 'undefined' && typeof data['hydra:member'] !== 'undefined' && data['hydra:member'].length !== 0;

    let daysRendered = [];
    return (
        <Layout validToken={validToken}>
            <div className="mt-6 mb-24">
            {error || (data?.['@type'] === 'hydra:Error')? <div>Ups... there was an error fetching your timers</div> :
                <>
                    <div>
                        {data?.['hydra:member']?.length > 0 ?
                            data['hydra:member']
                                .sort((a, b) => new Date(b.date_start).getTime() - new Date(a.date_start).getTime())
                                .map((timer: ITimer) => {
                                    const timerDate = format(new Date(timer.date_start), 'u-MM-dd');
                                    const todayTimer = isToday(new Date(timer.date_start));

                                    let totalWorkDuration = 0
                                    let totalBreakDuration = 0
                                    let subTimerHtml = data['hydra:member'].map((subTimer: ITimer) => {
                                        if (timerDate === format(new Date(subTimer.date_start), 'u-MM-dd') && !daysRendered.some((e) => {
                                            return e === timerDate
                                        })) {
                                            let isRunningTimer = typeof subTimer.date_end === 'undefined' || subTimer.date_end === null;
                                            let diffInSWork = 0;
                                            let diffInSBreak = 0;
                                            if (subTimer.timer_type === 'work') {
                                                diffInSWork = differenceInSeconds(isRunningTimer ? new Date() : new Date(subTimer.date_end), new Date(subTimer.date_start));
                                            } else {
                                                diffInSBreak = differenceInSeconds(isRunningTimer ? new Date() : new Date(subTimer.date_end), new Date(subTimer.date_start));
                                            }
                                            totalWorkDuration += diffInSWork;
                                            totalBreakDuration += diffInSBreak;
                                            console.log(diffInSBreak, diffInSWork);
                                            return generateSubTimerHtml(subTimer, toHHMMSS(subTimer.timer_type === 'work' ? diffInSWork : diffInSBreak));
                                        }
                                    })

                                    if (!daysRendered.some((e) => {
                                        return e === timerDate
                                    })) {
                                        daysRendered.push(timerDate);
                                        return (
                                            <div key={timerDate}
                                                 className="bg-white p-3 mb-1 border-gray-300 border-l-4 hover:border-teal-600 rounded-md">
                                                <div>
                                                    <div className="flex">
                                                        <div className="flex flex-col">
                                                            <div
                                                                className="text-xs text-gray-500">{format(new Date(timer.date_start), 'dd LLLL uuuu')}</div>
                                                            <div
                                                                className={`text-2xl${cn({' text-yellow-500 font-bold': todayTimer}, {' text-gray-900': !todayTimer})}`}>{todayTimer ? 'Today' : format(new Date(timer.date_start), 'iiii')}</div>
                                                        </div>

                                                        <div className="ml-auto text-xs text-gray-500">
                                                            {totalBreakDuration > 0 ? <span
                                                                className="text-xs mr-2">({toHHMMSS(totalBreakDuration)})</span> : ''}
                                                            {toHHMMSS(totalWorkDuration)}
                                                        </div>
                                                    </div>
                                                </div>
                                                {subTimerHtml}
                                            </div>
                                        )
                                    }
                                })
                            :
                            <div>
                                <button className="fixed top-20 bottom-0 left-0 w-full h-full bg-black opacity-50"/>
                                <div className="fixed bottom-44 text-white text-lg right-6">Start or add your first timer!</div>
                                <img src="../images/icons/comic-arrow.svg" width="200" className="fixed bottom-24 right-14 animate-bounce-little"/>
                            </div>
                        }

                        {manualTimerModalVisible ?
                            <button className={`inset-0 fixed w-full h-full cursor-default bg-black opacity-50`} onClick={() => setManualTimerModalVisible(false)}/>
                            : ''
                        }
                        <ManualTimerview
                            data={data}
                            mutateTimers={mutateTimers}
                            toggleAddTimerView={toggleAddTimerView}
                            isVisible={manualTimerModalVisible}
                            timerToEdit={timerToEdit}
                            removeTimer={deleteTimer}
                            setRunningTimer={setRunningTimer}
                        />
                    </div>
                    {totalPages > 1 ?
                        <div>
                            <Pagination
                                currentPage={pageIndex}
                                setPageIndex={setPageIndex}
                                // totalPages={Math.ceil(typeof data === 'undefined' || typeof data['hydra:member'] === 'undefined' || data['hydra:member'].length === 0 ? 30 / 30 : data['hydra:totalItems'] / 30)}
                                totalPages={totalPages}
                                path="timers"
                            />
                        </div>
                        :
                        ''
                    }
                    {runningTimer ?
                        <div className="fixed bottom-0 right-6 ml-3">
                            <div className="flex mb-3">
                                <button
                                    className={`bg-red-500 rounded-full p-4 border-white border-2 outline-none shadow-md cursor-pointer`}
                                    onClick={() => stopTimer()}>
                                    <img src={`../images/icons/icons8-stop-48.png`} width="30" height="30" alt="Stop Timer"/>
                                </button>
                                <button
                                    className={`${cn({'animate-spin-slow cursor-default bg-yellow-200 ': runningTimer.timer_type  === 'break'}, {'cursor-pointer bg-yellow-400 ': runningTimer.timer_type  === 'work'}, {})}ml-3 rounded-full p-4 border-white border-2 outline-none shadow-md`}
                                    onClick={() => startTimer('break')}
                                    disabled={cn({'disabled': runningTimer.timer_type  === 'break'})}>
                                    <img src="../images/icons/icons8-pause-60.png" width="30" height="30"
                                         alt="Stop Timer"/>
                                </button>
                            </div>
                        </div>
                        :

                        <div className={`fixed bottom-0 right-6 flex flex-col mb-3 ml-3`}>
                            <div className="flex-row">
                                <button
                                    className="bg-teal-500 rounded-full p-4 border-white border-2 outline-none shadow-md cursor-pointer"
                                    onClick={() => startTimer('work')}>
                                    <img src="../images/icons/icons8-play-100.png" width="30" height="30" alt="Start Timer"/>
                                </button>
                                <button
                                    className="ml-3 bg-teal-500 rounded-full p-4 border-white border-2 outline-none shadow-md cursor-pointer"
                                    onClick={() => toggleAddTimerView(false)}>
                                    <img src="../images/icons/icons8-plus-math-60.png" width="30" height="22" alt="Start Timer"/>
                                </button>
                            </div>
                        </div>
                    }

                    {messageState.message !== '' ? <Alert message={messageState.message} severity={'error'}/> : ''}
                </>
            }
            </div>
        </Layout>
    );
}

export const getServerSideProps: GetServerSideProps = async (context) => {
    const cookies = new Cookies(context.req.headers.cookie);
    const token = cookies.get('token');
    let pageQueryParam = null;
    let url = new URL(`${process.env.API_BASE_URL}/timers?order[dateStart]`);
    const timers = await IsoFetcher.isofetchAuthed(url.href, 'GET', token);
    const tokenService = new TokenService();
    const validToken = await tokenService.authenticateTokenSsr(context)
    if (!validToken) {
        tokenService.deleteToken();
        return {
            redirect: {
                permanent: false,
                destination: '/login',
            },
        }
    }
    return { props: { validToken, initialData: timers } };
};

//TO-DO
/**
 * jump to first page when adding timer
 */

export default Timers;
