import React, { useState } from 'react';
import Layout from '../components/layout';
import { DailyTableRow } from '../components/dailyTableRow';
import useSWR from "swr";
import {format} from 'date-fns';
import { useRouter } from 'next/router';
import { Pagination } from '../components/pagination';
import {DailyEditView} from "../components/dailyEditView";

export const Dailies = () => {
    const router = useRouter();
    const currentPage = Number(typeof router.query.page !== 'undefined' ? router.query.page : 1);
    const url = 'https://localhost:8443/users/1/daily_summaries?order[date]&page='+currentPage;
    const fetcher = (...args) => fetch(...args).then(res => res.json());
    const { data, error } = useSWR(url, fetcher)

    let initialEndDate = new Date();
    initialEndDate.setHours(initialEndDate.getHours() + 6);
    const [startDate, setStartDate] = useState(new Date());
    const [endDate, setEndDate] = useState(initialEndDate);
    const [sendMail, setSendMail] = useState(0);

    const [isEditViewVisible, setIsEditViewVisible] = useState(0);

    let dailiesNormalized = {};
    let totalSecondsPerDay = {};

    const toggleDsDetailView = (daily = null) => {
        setIsEditViewVisible(!isEditViewVisible);
        if (daily !== null) {
            console.log("setting start time to: ", daily.start_time);
            setStartDate(new Date(daily.start_time));
            setEndDate(new Date(daily.end_time));
        }
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

    if (error) return <div>failed to load</div>
    if (!data) return <div>loading...</div>

    console.log(data);
    return (
        <Layout>
            {typeof data['hydra:member'] === 'undefined' || data['hydra:member'].length === 0 ? <div>no data yet...</div> :
            <div className={`mt-6`}>
                <div>
                    {prepareData(data, totalSecondsPerDay, dailiesNormalized)}

                    {Object.entries(dailiesNormalized).map(([_, daily]) => {
                        return (
                            <DailyTableRow
                                daily={daily}
                                onClick={daily => toggleDsDetailView(daily)}
                            />
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
                    className="flex fixed bottom-0 notification bottom bg-teal-500 rounded-full p-6 border-teal-700 border-2 shadow-md cursor-pointer text-white text-lg"
                    onClick={() => toggleDsDetailView()}>
                    <img src="../images/icons/icons8-plus-math-60.png" width="25" height="25" alt="Start Timer"/>
                </div>
                <DailyEditView
                    startDate={startDate}
                    endDate={endDate}
                    setStartDate={(date) => setStartDate(date)}
                    setEndDate={(date) => setEndDate(date)}
                    setSendMail={(date) => setSendMail(date)}
                    sendMail={sendMail}
                    isEditViewVisible={isEditViewVisible}
                    onClick={() => toggleDsDetailView()}
                />
            </div>
            }
        </Layout>
    );
}

/**
 * use proper icons for mail and personio
 * time picking in datetime picker not showing minutes
 * show right timepicker to the left
 * add mail functionality
 * add save functionality
 */


export default Dailies;
