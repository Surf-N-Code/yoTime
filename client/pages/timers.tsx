import React, {useEffect, useState} from 'react';
import useSWR from 'swr';
import {useRouter} from 'next/router';
import {differenceInDays, differenceInSeconds, format} from 'date-fns';
import isToday from 'date-fns/isToday';
import cn from 'classnames';
import {v4 as uuidv4} from 'uuid';
import {ActionAnimations, SwipeableList, SwipeableListItem} from '@sandstreamdev/react-swipeable-list';
import '@sandstreamdev/react-swipeable-list/dist/styles.css';
import { CSSTransition, TransitionGroup } from 'react-transition-group';
import { Transition } from 'react-transition-group';

import {Alert, Layout, Pagination, ManualTimerview} from '../components';
import {FetcherFunc, TokenService, useAuth, useGlobalMessaging} from '../services';
import {ITimer, ITimerApiResult} from '../types';
import {toHHMMSS} from "../utilities";

import {GetServerSideProps} from "next";

export const Timers = ({validToken}) => {
    const router = useRouter();
    const [auth, authDispatch] = useAuth();
    const [manualTimerModalVisible, setManualTimerModalVisible] = useState(false);
    const [runningTimer, setRunningTimer] = useState<ITimer | null>(null);
    const [messageState, messageDispatch] = useGlobalMessaging();
    const [timerToEdit, setTimerToEdit] = useState<ITimer|null>(null);
    const currentPage = Number(typeof router.query.page !== 'undefined' ? router.query.page : 1);
    const url = `timers?order[dateStart]&page=${currentPage}`;
    const { data, error, mutate: mutateTimers } = useSWR<ITimerApiResult>([url, auth.jwt, 'GET'], FetcherFunc)

    useEffect(() => {
        if (!data || typeof data["hydra:member"] === 'undefined') return;
        const timer = data["hydra:member"].filter((timer) => {return typeof timer.date_end === 'undefined' || timer.date_end === null});
        setRunningTimer((prevTimer) => timer[0]);
        // return updateTimerDurationUi();
    }, [data])

    useEffect(() => {
        if (data && typeof data.code !== 'undefined' && data.code === 401) {
            const tokenService = new TokenService();
            authDispatch({
                type: 'removeAuthDetails'
            });
            tokenService.deleteToken();
            router.push('/timers');
        }
    }, [data]);

    useEffect(() => {
        console.log('use effect running timer', runningTimer);
        return updateTimerDurationUi(runningTimer);
    }, [runningTimer])

    const updateTimerDurationUi = (runTimer) => {
        if (typeof runTimer === 'undefined' || runTimer === null || typeof runTimer.timer_type === 'undefined' ) {
            return;
        }

        const timerSecondsUpdater = setInterval(() => {
            setRunningTimer((prevTimer) => {return {...runTimer}})
        }, 1000);

        if (typeof runningTimer.date_end !== 'undefined' && runningTimer.date_end !== null) {
            console.error('clear interval');
            clearInterval(timerSecondsUpdater);
        }
        return () => clearInterval(timerSecondsUpdater);
    }

    const generateSubTimerHtml = (timer: ITimer, formattedDiffInMinPerTimer: string) => {
        const dateStart = new Date(timer.date_start);
        const isRunning = typeof timer.date_end === 'undefined' || timer.date_end === null;
        const timerStartString = format(dateStart, 'HH:mm');

        let diffInDays = 0;
        let timerEndString = '...';
        if (!isRunning) {
            diffInDays = differenceInDays(new Date(timer.date_end), dateStart);
            timerEndString = format(new Date(timer.date_end), 'HH:mm');
            diffInDays > 0 ? timerEndString += ' +'+ diffInDays : timerEndString;
        }

        return (
            <CSSTransition
                classNames="my-node"
                key={timer.id}
                timeout={300}
            >
                <SwipeableListItem
                    key={timer.id}
                    swipeLeft={{
                        content: <div className={`text-white bg-green-500 text-right p-3 w-full`}>Edit</div>,
                        action: () => editTimer(timer),
                        actionAnimation: ActionAnimations.RETURN
                    }}
                    swipeRight={{
                        content: <div className={`text-white bg-red-500 p-3 w-full`}>Delete</div>,
                        action: () => deleteTimer(timer.id),
                        actionAnimation: ActionAnimations.REMOVE
                    }}
                    onSwipeProgress={progress => console.info(`Swipe progress: ${progress}%`)}
                >
                    <div className="flex flex-row items-center ml-3 mt-1 w-full" data-id={timer.id}>
                        <div className="text-sm">{timerStartString}  -  {timerEndString}{timer.timer_type === 'break' ? <span className="text-xs"> (b)</span>:''}</div>
                        <div className="text-sm text-gray-600 text-right ml-auto">{formattedDiffInMinPerTimer}</div>
                    </div>
                </SwipeableListItem>
            </CSSTransition>
        )
    }

    if (error) return <div>failed to load</div>;

    const editTimer = async (timer: ITimer) => {
        console.log('timer to edit', timer);
        setTimerToEdit(timer);
        toggleAddTimerView();
    }

    const deleteTimer = async (timerId) => {
        await mutateTimers((data) => {
            let newData = {...data};
            console.log()
            return {...data, "hydra:member": [...newData["hydra:member"].filter(timer => timer.id !== timerId)]};
        }, false);

        fetch(`https://localhost:8443/timers/${timerId}`, {
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
        console.log(timerId);
    }

    const stopTimer = async () => {
        if (!runningTimer) return;
        let timer = {...runningTimer};
        timer.date_end = new Date();
        let timersToStop = [];
        await mutateTimers((data) => {
            let newData = {...data};
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
        setRunningTimer((prevTimer) => null);
    }

    const startTimer = async (timerType: string) => {
        await stopTimer();
        // console.group('start timer')
        let tempId = uuidv4();
        const timer = {
            date_start: new Date(),
            date_end: null,
            timer_type: timerType
        }
        setRunningTimer((prevTimer) => {return {id: tempId, 'optimisticTimer': true, ...timer}});

        await mutateTimers((data) => {
            return {...data, "hydra:member": [{id: tempId, ...timer}, ...data['hydra:member']]};
        }, false);

        const result = await FetcherFunc('timers', auth.jwt, 'POST', timer);

        await mutateTimers((data) => {
            return {...data, "hydra:member": data['hydra:member'].map((timer) => timer.id === tempId ? result : timer)};
        }, false);

        console.log('result timer from fetch', result);
        setRunningTimer((prevTimer) => result);
        // console.groupEnd();
    }
    // console.log('latest timer', runningTimer);
    // console.log('---- DATA ---- ', data);

    if (data && data['@type'] === 'hydra:Error') {
        messageDispatch({
            type: 'setMessage',
            payload: {
                message: 'Ups... Something went wrong. Please try again.'
            }
        })
    }

    const toggleAddTimerView = () => {
        if (manualTimerModalVisible) {
            setTimerToEdit(null);
        }
        setManualTimerModalVisible((prevVal) => {return !prevVal});
    }

    const hasTimer = typeof data !== 'undefined' && typeof data['hydra:member'] !== 'undefined' && data['hydra:member'].length !== 0;

    let daysRendered = [];
    return (
        <Layout validToken={validToken}>
            <div className="mt-6 mb-24">
            {error || (data && data['@type'] === 'hydra:Error')? <div>Ups... there was an error fetching your timers</div> :
                <>
                    <div>
                        {!hasTimer ?
                            <div>
                                <button className="fixed bottom-0 left-0 w-full h-full bg-black opacity-50"/>
                                <div className="fixed bottom-52 text-white text-lg">Start or add your first timer!</div>
                                <img src="../images/icons/comic-arrow.svg" width="220" className="fixed bottom-32 left-12 animate-bounce-little"/>
                            </div>
                            :
                            data['hydra:member'].map((timer: ITimer) => {
                                const timerDate = format(new Date(timer.date_start), 'u-MM-dd');
                                const todayTimer = isToday(new Date(timer.date_start));

                                let totalWorkDuration = 0
                                let totalBreakDuration = 0
                                let subTimerHtml = data['hydra:member'].map((subTimer: ITimer) => {
                                    if (timerDate === format(new Date(subTimer.date_start), 'u-MM-dd') && !daysRendered.some((e) => { return e === timerDate })) {
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
                                        return generateSubTimerHtml(subTimer, toHHMMSS(subTimer.timer_type === 'work' ? diffInSWork : diffInSBreak));
                                    }
                                })

                                if (!daysRendered.some((e) => { return e === timerDate })) {
                                    daysRendered.push(timerDate);
                                    return (
                                        <div key={timerDate} className="bg-white p-3 mb-1 border-gray-300 border-l-4 hover:border-teal-600">
                                            <div>
                                                <div
                                                    className="text-xs text-gray-500">{format(new Date(timer.date_start), 'dd LLLL uuuu')}</div>
                                                <div className="flex flex-row items-center">
                                                    <div
                                                        className={`text-2xl${cn({' text-yellow-500 font-bold': todayTimer}, {' text-gray-900': !todayTimer})}`}>{todayTimer ? 'Today' : format(new Date(timer.date_start), 'iiii')}</div>
                                                    <div className="ml-auto"><span className="text-xs">({toHHMMSS(totalBreakDuration)})</span> {toHHMMSS(totalWorkDuration)}</div>
                                                </div>
                                            </div>
                                            <SwipeableList threshold={300}>
                                                {({
                                                      className,
                                                      scrollStartThreshold,
                                                      swipeStartThreshold,
                                                      threshold
                                                  }) => (
                                                    <TransitionGroup
                                                        className={className}
                                                    >
                                                        {subTimerHtml}
                                                    </TransitionGroup>
                                                )}
                                            </SwipeableList>
                                            {/*<SwipeableList*/}
                                            {/*    threshold={0.30}*/}
                                            {/*>*/}
                                            {/*    <TransitionGroup>*/}
                                            {/*        {subTimerHtml}*/}
                                            {/*    </TransitionGroup>*/}
                                            {/*</SwipeableList>*/}
                                        </div>
                                    )
                                }
                            })
                        }
                        {manualTimerModalVisible ?
                            <button className={`inset-0 fixed w-full h-full cursor-default bg-black opacity-50`} onClick={() => setManualTimerModalVisible(false)}/>
                            : ''
                        }
                        <ManualTimerview
                            mutateTimers={mutateTimers}
                            toggleAddTimerView={toggleAddTimerView}
                            isVisible={manualTimerModalVisible}
                            timerToEdit={timerToEdit}
                        />
                    </div>
                    <div>
                        <Pagination
                            currentPage={currentPage}
                            totalPages={Math.ceil(typeof data === 'undefined' || typeof data['hydra:member'] === 'undefined' || data['hydra:member'].length === 0 ? 30 / 30 : data['hydra:totalItems'] / 30)}
                        />
                    </div>
                    {runningTimer !== null && typeof runningTimer !== 'undefined' ?
                        <div className="fixed bottom-0 ml-3">
                            <div className="flex mb-3">
                                <button
                                    className={`bg-red-500 rounded-full p-4 border-white border-2 outline-none shadow-md cursor-pointer`}
                                    onClick={() => stopTimer()}>
                                    <img src={`../images/icons/icons8-stop-48.png`} width="30" height="30" alt="Stop Timer"/>
                                </button>
                                <button
                                    className={`${cn({'animate-spin-slow cursor-default bg-yellow-200 ': runningTimer.timer_type  === 'break'}, {'cursor-pointer bg-yellow-400 ': runningTimer.timer_type  === 'work'}, {})}ml-3 rounded-full p-4 border-white border-2 outline-none shadow-md`}
                                    onClick={() => startTimer('break')}
                                    disabled={cn({true: runningTimer.timer_type  === 'break'})}>
                                    <img src="../images/icons/icons8-pause-60.png" width="30" height="30"
                                         alt="Stop Timer"/>
                                </button>
                            </div>
                        </div>
                        :

                        <div className={`fixed bottom-0 flex flex-col mb-3 ml-3${cn({' border-2 border-gray-300 p-3': !hasTimer})}`}>
                            <div className="flex-row">
                                <button
                                    className="bg-teal-500 rounded-full p-4 border-white border-2 outline-none shadow-md cursor-pointer"
                                    onClick={() => startTimer('work')}>
                                    <img src="../images/icons/icons8-play-100.png" width="30" height="30" alt="Start Timer"/>
                                </button>
                                <button
                                    className="ml-3 bg-teal-500 rounded-full p-4 border-white border-2 outline-none shadow-md cursor-pointer"
                                    onClick={() => toggleAddTimerView()}>
                                    <img src="../images/icons/icons8-plus-math-30.png" width="30" height="30" alt="Start Timer"/>
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

export const getServerSideProps: GetServerSideProps = (async (context) => {
    const tokenService = new TokenService();
    const validToken = await tokenService.authenticateTokenSsr(context)
    if (!validToken) {
        return {
            redirect: {
                permanent: false,
                destination: '/login',
            },
        }
    }
    return { props: { validToken } };
});

//TO-DO
/**
 * jump to first page when adding timer
 */

//DONE
/**
 * running timer - update minutes and seconds
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

export default Timers;
