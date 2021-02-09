import React, {useEffect, useState} from 'react';
import Layout from "../components/Layout";
import useSWR from "swr";
import {ITimerApiResult} from "../types/apiResult.types";
import {useAuth, FetcherFunc, IsoFetcher} from "../services";
import {differenceInSeconds, format, isToday, startOfWeek} from "date-fns";
import {GetServerSideProps} from "next";
import Cookies from "universal-cookie";
import TokenService from "../services/Token.service";
import {ITimer} from "../types/timer.types";
import {HighlightNumberCard} from "../components/HighlightNumber";
import {toHHMM, toHHMMSS} from "../utilities/lib";
import {useRouter} from "next/router";
import {getUniqueValuesForProperty} from '../utilities';

export const Dashboard = (props) => {
    const [auth, authDispatch] = useAuth();
    const router = useRouter();
    const [dayWorkedInS, setDayWorkedInS] = useState(0);
    const [weekWorkedInS, setWeekWorkedInS] = useState(0);
    const [runningTimer, setRunningTimer] = useState<ITimer | null>(null);
    const url = `/timers?order[dateStart]&page=1&dateStart[after]=${format(startOfWeek(new Date(), {'weekStartsOn':1}), 'yyyy-MM-dd')}&itemsPerPage=1000`;
    const { data, error } = useSWR<ITimerApiResult>([url, auth.jwt, 'GET'], FetcherFunc);

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
        if (typeof data === 'undefined') return;
        setDayWorkedInS(getDailyHoursWorked(data));
        if (typeof data['hydra:member'][0] !== 'undefined' && (typeof data['hydra:member'][0].date_end === 'undefined' || data['hydra:member'][0].date_end === null)) {
            setRunningTimer(data['hydra:member'][0]);
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
            setRunningTimer((prevTimer) => {return {...runTimer}});
            setDayWorkedInS(getDailyHoursWorked(data));
            setWeekWorkedInS(getWeeklyHoursWorked(data));
        }, 1000);

        if (typeof runningTimer.date_end !== 'undefined' && runningTimer.date_end !== null) {
            clearInterval(timerSecondsUpdater);
        }
        return () => clearInterval(timerSecondsUpdater);
    }

    return (
        <Layout validToken={props.validToken}>
            <div className="mt-6">
                <div className="flex flex-col md:flex-row">
                    <HighlightNumberCard
                        title={"Today"}
                        number={toHHMMSS(dayWorkedInS == 0 && props.dayWorkedInS !== 0 ? props.dayWorkedInS : dayWorkedInS)}
                        numberPostFix=""
                        href={"/timers"}
                    />

                    <HighlightNumberCard
                        title={"This week"}
                        number={toHHMMSS(weekWorkedInS == 0 && props.weekWorkedInS !== 0 ? props.weekWorkedInS : weekWorkedInS)}
                        numberPostFix=""
                        href={"/timers"}
                    />
                </div>
            </div>
        </Layout>
    )
}

/**
 * 1) Show list of missing daily summaries
 *  Get all dates from timers
 *  Get all dates form dailies
 *
 *
 * 2) Show list of missing personio entries
 */
export default Dashboard;

const getWeeklyHoursWorked = (timers: ITimerApiResult) => {
    return timers['hydra:member'].reduce((total, timer, idx) => {
        if (timer.timer_type === 'work') {
            let isRunningTimer = typeof timer.date_end === 'undefined' || timer.date_end === null;
            let diffInSWork = differenceInSeconds(isRunningTimer ? new Date() : new Date(timer.date_end), new Date(timer.date_start));
            return total + diffInSWork;
        }
    }, 0) || 0;
}

const getDailyHoursWorked = (timers: ITimerApiResult) => {
    return timers['hydra:member']
        .filter((timer) => timer.timer_type === 'work' && isToday(new Date(timer.date_start)))
        .reduce((total, timer: ITimer, idx) => {
            let isRunningTimer = typeof timer.date_end === 'undefined' || timer.date_end === null;
            let diffInSWork = differenceInSeconds(isRunningTimer ? new Date() : new Date(timer.date_end), new Date(timer.date_start));
            return total + diffInSWork;
        }, 0) || 0;
}

export const getServerSideProps: GetServerSideProps = async (context) => {
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

    const cookies = new Cookies(context.req.headers.cookie);
    const token = cookies.get('token');

    const timersUrl = `${process.env.API_BASE_URL}/timers?order[dateStart]&page=1&dateStart[after]=${format(startOfWeek(new Date(), {'weekStartsOn':1}), 'yyyy-MM-dd')}&itemsPerPage=1000`;
    const timers = await IsoFetcher.isofetchAuthed(timersUrl, 'GET', token);

    const dayWorkedInS = getDailyHoursWorked(timers);

    const weekWorkedInS = getWeeklyHoursWorked(timers);
    console.log('dayWork', dayWorkedInS, 'week work', weekWorkedInS);

    return { props: { validToken, ApiBaseUrl: process.env.API_BASE_URL, dayWorkedInS: dayWorkedInS, weekWorkedInS: weekWorkedInS }};
};
