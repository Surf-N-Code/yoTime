import React, { useState } from 'react';
import Layout from '../components/layout';
import useSWR from "swr";
import { format } from 'date-fns';
import { toHHMMSS } from '../utilities/lib';
import cn from 'classnames';
import { useRouter } from 'next/router';
import { Pagination } from '../components/pagination';

export const Dailies = () => {
    const [isEditViewVisible, setIsEditViewVisible] = useState(0);
    const forceUpdate = useForceUpdate();

    const router = useRouter();
    const currentPage = Number(typeof router.query.page !== 'undefined' ? router.query.page : 1);
    const url = 'https://localhost:8443/users/1/daily_summaries?order[date]&page='+currentPage;
    const fetcher = (...args) => fetch(...args).then(res => res.json());

    const { data, error } = useSWR(url, fetcher)

    if (error) {
        const content = <div>failed to load</div>;
    }

    if (!data) {
        const content = <div>loading...</div>;
    }

    let dailiesNormalized = {};
    let totalSecondsPerDay = {};
    console.log(data);
    return (
        <Layout>
            {error ? <div>failed to load</div> :
            !data ? <div>loading...</div> :
            typeof data['hydra:member'] === 'undefined' || data['hydra:member'].length === 0 ? <div>no data yet...</div> :
            <div className="mt-6">
                <div>
                    {prepareData(data, totalSecondsPerDay, dailiesNormalized)}

                    {Object.entries(dailiesNormalized).map(([_, daily]) => {
                        const dateString = format(new Date(daily.date), 'dd LLLL uuuu' );
                        const formattedDiffInMinPerDay = toHHMMSS(daily.time_worked_in_s - daily.time_break_in_s);
                        return (
                            <div key={dateString} className="bg-white p-3 mb-1 border-gray-300 border-l-4 hover:border-teal-600">
                                <>
                                    <div className="flex text-gray-900 mb-2">
                                        <div>{dateString}</div>
                                        <img src={`../images/icons/${cn({'mail-sent' : daily.is_email_sent}, {'mail-outstanding' : !daily.is_email_sent})}.png`} width="25" className="ml-auto" alt={`${cn({'Daily summary email sent' : daily.is_email_sent}, {'Daily summary email not sent yet' : !daily.is_email_sent})}`}/>
                                        <img src={`../images/icons/${cn({'personio-fail' : daily.is_synced_to_personio}, {'personio-success' : !daily.is_synced_to_personio})}.png`} width="25" className="ml-3" alt={`${cn({'Successfully synced your daily summary info to Personio' : daily.is_email_sent}, {'Time is not synced to Personio yet.' : !daily.is_email_sent})}`}/>
                                    </div>
                                    <div className="flex flex-row items-center text-gray-900">
                                        <div>{format(new Date(daily.start_time), 'HH:ss')} - {format(new Date(daily.end_time), 'HH:ss')}</div>
                                        <div className="ml-auto">{formattedDiffInMinPerDay}</div>
                                    </div>
                                </>
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
                <div
                    className="flex fixed bottom-0 notification bottom bg-teal-500 rounded-full p-4 border-teal-700 border-2 shadow-md cursor-pointer text-white text-lg"
                    onClick={() => toggleDsDetailView(isEditViewVisible)}>
                    <img src="../images/icons/icons8-plus-math-60.png" width="20" height="20" alt="Start Timer"/>
                </div>
                <div className={`flex flex-col transform fixed bottom-0 left-64 w-40 h-full bg-black h-full shadow-xl fixed overflow-auto ease-in-out transition-all duration-200 ${cn({'translate-y-0': isEditViewVisible}, {'-translate-y-full': !isEditViewVisible})}`}>
                    Ds Edit View
                </div>
            </div>
            }
        </Layout>
    );
}

const toggleDsDetailView = (isEditViewVisible) => {

}

/**
 * use proper icons for mail and personio
 */

function useForceUpdate(){
    const [value, setValue] = useState(0); // integer state
    return () => setValue(value => ++value); // update the state to force render
}

const generateSubTimerHtml = (ds) => {
    const dateStart = new Date(ds.date);

    return (
        <div className="flex flex-row items-center ml-2 mt-1">
            <div className="text-sm">{format(dateStart, 'HH:mm')}</div>
            <div className="text-sm ml-auto text-gray-600">{format(dateStart, 'u-MM-dd')}</div>
        </div>
    )
}

const prepareData = (data, totalSecondsPerDay, timersNormalized) => {
    {data['hydra:member'].map((value, key) => {
        typeof value.date_end !== 'undefined' ? value.isRunning = false : value.isRunning = true;
        const date = format(new Date(value.date), 'u-MM-dd');

        if (typeof timersNormalized[date] === 'undefined') {
            timersNormalized[date] = [];
        }
        timersNormalized[date] = value;
    })}
}

export default Dailies;
