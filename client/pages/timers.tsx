import React from 'react';
import Layout from '../components/layout';
import useSWR from "swr";
import {differenceInDays, differenceInSeconds, format} from 'date-fns';
import isToday from 'date-fns/isToday';
import cn from 'classnames';
import Link from 'next/link';
import { useRouter } from 'next/router';
import {log} from "util";

export const Timers = () => {
    const router = useRouter();
    console.log(router.query);
    const url = 'https://localhost:8443/users/1/timers?order[dateStart]&page='+router.query.page;
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
    console.log(data);
    return (
        <Layout>
            {error ? <div>failed to load</div> :
            !data ? <div>loading...</div> :
            typeof data['hydra:member'] === 'undefined' || data['hydra:member'].length === 0 ? <div>no data yet...</div> :
            <div className="mt-6">
                <div>
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

                    {Object.entries(timersNormalized).map(([dateStartString, timers]) => {
                        const dateStart = new Date(dateStartString);

                        const formattedDiffInMinPerDay = toHHMMSS(totalSecondsPerDay[dateStartString]);
                        return (
                            <div key={dateStartString} className="bg-white p-2 border-gray-300 border-t">
                                <>
                                    <div className="text-xs text-gray-500">{format(dateStart, 'dd LLLL uuuu' )}</div>
                                    <div className="flex flex-row items-center">
                                        <div className={`text-xl${cn({' text-yt_orange font-bold': isToday(dateStart)}, {' text-gray-900': !isToday(dateStart)})}`}>{isToday(dateStart) ? 'Today' : format(dateStart, 'iiii' )}</div>

                                        <div className="ml-auto">{formattedDiffInMinPerDay}</div>
                                    </div>
                                </>
                                {timers.map((timer, index) => {
                                    const formattedDiffInMinPerDay = toHHMMSS(timer.diffInSeconds);
                                    return generateSubTimerHtml(timer, formattedDiffInMinPerDay);
                                })}
                            </div>
                        )
                    })}
                </div>
                <div>
                    <div className="flex flex-col items-center my-12">
                        <div className="flex text-gray-700">
                            {Number(router.query.page) > 1 ?
                                <Link href={`http://localhost:3000/timers?page=${Number(router.query.page)-1}`}>
                                    <a className="h-12 w-12 mr-1 flex justify-center items-center rounded-full cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                             stroke-linejoin="round" className="feather feather-chevron-left w-6 h-6">
                                            <polyline points="15 18 9 12 15 6"></polyline>
                                        </svg>
                                    </a>
                                </Link>
                                :
                                <div className="h-12 w-12 mr-1 flex justify-center items-center rounded-full cursor-pointer opacity-25">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round" className="feather feather-chevron-left w-6 h-6">
                                        <polyline points="15 18 9 12 15 6"></polyline>
                                    </svg>
                                </div>
                            }
                            <div className="flex h-12 font-medium rounded-full bg-gray-200">
                                {generatePagination(data, router.query.page)}
                                <div className="w-12 h-12 md:hidden flex justify-center items-center cursor-pointer leading-5 transition duration-150 ease-in rounded-full bg-teal-600 text-white">{router.query.page}</div>
                            </div>
                            {Number(router.query.page) <= Math.ceil(data['hydra:totalItems'] / 30) - 1 ?
                                <Link href={`http://localhost:3000/timers?page=${Number(router.query.page)+1}`}>
                                    <a className="h-12 w-12 mr-1 flex justify-center items-center rounded-full cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                             stroke-linejoin="round" className="feather feather-chevron-right w-6 h-6">
                                            <polyline points="9 18 15 12 9 6"></polyline>
                                        </svg>
                                    </a>
                                </Link>
                                :
                                <div className="h-12 w-12 mr-1 flex justify-center items-center rounded-full cursor-pointer opacity-25">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round" className="feather feather-chevron-right w-6 h-6">
                                        <polyline points="9 18 15 12 9 6"></polyline>
                                    </svg>
                                </div>
                            }
                        </div>
                    </div>
                </div>
            </div>
            }
        </Layout>
    );
}

//TO-DO
//running timer icon
//running timer - show update minutes and seconds
//bottom right play icon to start work timer. Show stop icon if timer is running on all pages
/**
 * only allow user to see its own entries!
 * menu - highlight current selected menu element
 * cleanup paging
 */

//DONE
/**
 * no timers at all handling
 * wrong multi day timer
 * show seconds
 * add timer fixtures
 * paging of timers
 */
const generateSubTimerHtml = (timer, formattedDiffInMinPerTimer: string) => {
    const dateStart = new Date(timer.date_start);

    let diffInDays = 0;
    let dateEndString = '';
    if (!timer.isRunning) {
        diffInDays = differenceInDays(new Date(timer.date_end), dateStart);
        dateEndString = format(new Date(timer.date_end), 'HH:mm:ss');
    }

    return (
        <div className="flex flex-row items-center ml-2">
            <div className="text-sm">{format(dateStart, 'HH:mm:ss')} - {!timer.isRunning ? diffInDays > 0 ? dateEndString + ' +'+ diffInDays : dateEndString : '...'}</div>
            <div className="text-sm ml-auto text-gray-600">{formattedDiffInMinPerTimer}</div>
        </div>
    )
}

const generatePagination = (data, curPage) => {
    let pageIndicatorLow = 0;
    let pageIndicatorHigh = 0;
    let maxPages = Math.ceil(data['hydra:totalItems'] / 30);
    let currentPageNumber = Number(curPage);
    return (
        Array.from({length: maxPages}, (_, i) => i + 1).map((page, value) => {
            console.log(value, page, currentPageNumber);
            if (page > 1 && page < currentPageNumber -1) {
                pageIndicatorLow++;
            }
            if (page > 1 && page > currentPageNumber +1) {
                pageIndicatorHigh++;
            }

            return (
                page === 1 || page === maxPages || page === currentPageNumber || page === currentPageNumber-1 || page === currentPageNumber+1 ?
                    <Link href={`http://localhost:3000/timers?page=${page}`}>
                        <a className={`w-12 md:flex justify-center items-center hidden cursor-pointer leading-5 transition duration-150 ease-in hover:bg-white rounded-full${cn({' bg-teal-600 text-white hover:text-gray-700': currentPageNumber === page})}`}>{page}</a>
                    </Link>
                :
                    (pageIndicatorLow === 1 && pageIndicatorHigh === 0) || pageIndicatorHigh === 1?
                    <div className={`w-12 md:flex justify-center items-center hidden cursor-default leading-5 transition duration-150 ease-in rounded-full`}>...</div>
                :
                    null
            )
        })
    )
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
