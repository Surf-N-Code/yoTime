import React, {useEffect, useState} from 'react';
import useSWR from 'swr';
import {useRouter} from 'next/router';
import {differenceInDays, differenceInSeconds, format} from 'date-fns';
import isToday from 'date-fns/isToday';
import cn from 'classnames';
import {v4 as uuidv4} from 'uuid';
import Layout from '../components/layout';
import {Pagination} from '../components/pagination';
import {Alert} from '../components/alert';
import {fetcherFunc} from '../services/Fetcher.service';
import {useAuth} from "../services/Auth.context";
import TokenService from "../services/Token.service";
import {sleep, toHHMMSS} from "../utilities/lib";
import {ITimer} from '../types/timer.types';
import {IApiResult} from '../types/apiResult.types';

export const Timers = () => {
    const router = useRouter();
    const [auth, authDispatch] = useAuth();
    const [appError, setAppError] = useState('');
    const [stopTimerCount, setStopTimerCount] = useState(0);
    const [runningTimer, setRunningTimer] = useState<ITimer | null>(null);
    const currentPage = Number(typeof router.query.page !== 'undefined' ? router.query.page : 1);
    const url = `timers?order[dateStart]&page=${currentPage}`;
    const { data, error, mutate: mutateTimers } = useSWR<IApiResult>([url, auth.jwt, 'GET'], fetcherFunc)
    const STOP_TIMER_RETRIES = 0;

    useEffect(() => {
        if (!data || typeof data["hydra:member"] === 'undefined') return;
        const timer = data["hydra:member"].filter((timer) => {return typeof timer.date_end === 'undefined' || timer.date_end === null});
        if (timer[0]) setRunningTimer(timer[0]);
        return updateTimerDurationUi();
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
        return updateTimerDurationUi();
    }, [runningTimer])

    useEffect(() => {
        if (typeof runningTimer === 'undefined' || runningTimer === null) return;
        async function sleep() {
            await new Promise(resolve => setTimeout(resolve, 1000));
            await stopTimer();
        }

        if (stopTimerCount >= STOP_TIMER_RETRIES && typeof runningTimer.lastSyncAttempt === 'undefined') {
            setStopTimerCount(0);
            setRunningTimer({'lastSyncAttempt': new Date(), ...runningTimer});
            setAppError('Ups, I couldn\'t stop your timer. Have a coffee, come back and please try again :)');
            return;
        }
        sleep();
    }, [stopTimerCount])

    const updateTimerDurationUi = () => {
        if (typeof runningTimer === 'undefined' || runningTimer === null || runningTimer["@type"] !== 'Timer' ) return;
        const timerSecondsUpdater = setInterval(() => {
            let diffInSeconds = differenceInSeconds(new Date(), new Date(runningTimer.date_start));
            setRunningTimer((prevVal) => {return {diffInSeconds, ...prevVal}})
        }, 1000);
        return () => clearInterval(timerSecondsUpdater);
    }

    const removeLatestErrorMessage = async () => {
        await sleep(1000); //wait for the css transition to finish
        setAppError('');
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
            <div key={timer.id} className="flex flex-row items-center ml-2 mt-1" data-id={timer.id}>
                <div className="text-sm">{timerStartString}  -  {timerEndString}{timer.timer_type === 'break' ? <span className="text-xs"> (b)</span>:''}</div>
                <div className="text-sm ml-auto text-gray-600">{formattedDiffInMinPerTimer}</div>
            </div>
        )
    }

    if (error) return <div>failed to load</div>;

    const stopTimer = async () => {
        if (!runningTimer) return;
        if (runningTimer.optimisticTimer === true && typeof runningTimer.lastSyncAttempt === 'undefined') {
            setStopTimerCount(stopTimerCount+1);
            return;
        }

        let timer = {...runningTimer};
        timer.date_end = new Date();
        //@TODO hier muss ich den timer iwie verwenden, nicht die hydra members
        await mutateTimers((data) => {
            let newData = {...data};
            newData['hydra:member'][0].date_end = new Date();
            return {...data, "hydra:member": [...newData['hydra:member']]};
        }, false);
        await fetcherFunc(`/timers/${timer.id}`, auth.jwt, 'PATCH', timer, 'application/merge-patch+json');
        console.log('setrunningtimer to null');
        setRunningTimer(null);
    }

    const startTimer = async (timerType: string) => {
        await stopTimer();
        console.group('start timer')
        let tempId = uuidv4();
        const timer = {
            date_start: new Date(),
            date_end: null,
            timer_type: timerType
        }
        setRunningTimer({id: tempId, 'optimisticTimer': true, ...timer});

        await mutateTimers((data) => {
            return {...data, "hydra:member": [{id: tempId, ...timer}, ...data['hydra:member']]};
        }, false);

        const result = await fetcherFunc('timers', auth.jwt, 'POST', timer);

        await mutateTimers((data) => {
            return {...data, "hydra:member": data['hydra:member'].map((timer) => timer.id === tempId ? result : timer)};
        }, false);
        setRunningTimer(result);
        console.groupEnd();
    }
    // console.log('latest timer', runningTimer);
    // console.log('---- DATA ---- ', data);

    if (data && data['@type'] === 'hydra:Error') {
        console.error(data['hydra:description']);
    }

    let daysRendered = [];
    return (
        <Layout>
            <div className="mt-6">
            {error || (data && data['@type'] === 'hydra:Error')? <div>Ups... there was an error fetching your timers</div> :
                <>
                    <div>
                        {typeof data === 'undefined' || typeof data['hydra:member'] === 'undefined' || data['hydra:member'].length === 0 ? <div>You have no timers yet</div> :
                            data['hydra:member'].map((timer: ITimer) => {
                                const timerDate = format(new Date(timer.date_start), 'u-MM-dd');
                                const todayTimer = isToday(new Date(timer.date_start));

                                let totalWorkDuration = 0
                                let totalBreakDuration = 0
                                let subTimerHtml = data['hydra:member'].map((subTimer: ITimer) => {
                                    if (timerDate === format(new Date(subTimer.date_start), 'u-MM-dd')) {
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
                                        return generateSubTimerHtml(subTimer, toHHMMSS(subTimer.timer_type === 'work' ? totalWorkDuration : totalBreakDuration));
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
                                                        className={`text-xl${cn({' text-yt_orange font-bold': todayTimer}, {' text-gray-900': !todayTimer})}`}>{todayTimer ? 'Today' : format(new Date(timer.date_start), 'iiii')}</div>
                                                    <div className="ml-auto"><span className="text-xs">({toHHMMSS(totalBreakDuration)})</span> {toHHMMSS(totalWorkDuration)}</div>
                                                </div>
                                            </div>
                                            {subTimerHtml}
                                        </div>
                                    )
                                }
                            })
                        }
                    </div>
                    <div>
                        <Pagination
                            currentPage={currentPage}
                            totalPages={Math.ceil(typeof data === 'undefined' || typeof data['hydra:member'] === 'undefined' || data['hydra:member'].length === 0 ? 30 / 30 : data['hydra:totalItems'] / 30)}
                        />
                    </div>
                    {runningTimer !== null && typeof runningTimer !== 'undefined' ?
                        <div className={`fixed bottom-0`}>
                            <div className="flex mb-3">
                                <button
                                    className={`bg-red-500 rounded-full p-4 border-white border-2 shadow-md cursor-pointer${cn({' bg-red-100':stopTimerCount > 0 && stopTimerCount <= STOP_TIMER_RETRIES})}`}
                                    onClick={() => stopTimer()}>
                                    <img src={`../images/icons/${cn({'loading.svg': stopTimerCount > 0 && stopTimerCount <= STOP_TIMER_RETRIES}, {'icons8-stop-48.png': stopTimerCount === 0})}`} width="30" height="30" alt="Stop Timer"/>
                                </button>
                                <button
                                    className={`${cn({'spin-360 opacity-50 cursor-default ': runningTimer.timer_type  === 'break'}, {'cursor-pointer ': runningTimer.timer_type  === 'work'}, {})}ml-3 bg-yellow-500 rounded-full p-4 border-white border-2 shadow-md`}
                                    onClick={() => startTimer('break')}
                                    disabled={cn({true: runningTimer.timer_type  === 'break'})}>
                                    <img src="../images/icons/icons8-pause-60.png" width="30" height="30"
                                         alt="Stop Timer"/>
                                </button>
                            </div>
                        </div>
                        :
                        <div className={`fixed bottom-0`}>
                            <div className="flex mb-3">
                                <button
                                    className="bg-teal-500 rounded-full p-4 border-white border-2 shadow-md cursor-pointer"
                                    onClick={() => startTimer('work')}>
                                    <img src="../images/icons/icons8-play-100.png" width="30" height="30" alt="Start Timer"/>
                                </button>
                            </div>
                        </div>
                    }

                    {appError && appError.length > 0 ? <Alert message={appError} severity={'error'} onClick={() => removeLatestErrorMessage()}/> : ''}
                </>
            }
            </div>
        </Layout>
    );
}

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
