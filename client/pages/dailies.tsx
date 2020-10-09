import React, { useState } from 'react';
import Layout from '../components/layout';
import useSWR from "swr";
import {format, differenceInMinutes, differenceInSeconds} from 'date-fns';
import { toHHMMSS, toHHMM } from '../utilities/lib';
import cn from 'classnames';
import { useRouter } from 'next/router';
import { Pagination } from '../components/pagination';
import DatePicker, { registerLocale, setDefaultLocale } from  "react-datepicker";
import de from 'date-fns/locale/de'
import Switch from "react-switch";

export const Dailies = () => {
    const [startDate, setStartDate] = useState(new Date());
    let initialEndDate = new Date();
    initialEndDate.setHours(initialEndDate.getHours() + 6);
    const [endDate, setEndDate] = useState(initialEndDate);
    const [isEditViewVisible, setIsEditViewVisible] = useState(0);
    const [sendMail, setSendMail] = useState(0);
    const forceUpdate = useForceUpdate();
    registerLocale('de', de)
    setDefaultLocale('de')

    const router = useRouter();
    const currentPage = Number(typeof router.query.page !== 'undefined' ? router.query.page : 1);
    const url = 'https://localhost:8443/users/1/daily_summaries?order[date]&page='+currentPage;
    const fetcher = (...args) => fetch(...args).then(res => res.json());

    const { data, error } = useSWR(url, fetcher)

    let dailiesNormalized = {};
    let totalSecondsPerDay = {};

    const TimeInput = ({ value, onClick }) => {
        let date = value.split(' ');
        return (
            <button className="py-5 p-3" onClick={onClick}>
                {date[0]}<br />{date[1]}
            </button>
        )
    };

    console.log(data);
    return (
        <Layout>
            {error ? <div>failed to load</div> :
            !data ? <div>loading...</div> :
            typeof data['hydra:member'] === 'undefined' || data['hydra:member'].length === 0 ? <div>no data yet...</div> :
            <div className={`mt-6`}>
                <div>
                    {prepareData(data, totalSecondsPerDay, dailiesNormalized)}

                    {Object.entries(dailiesNormalized).map(([_, daily]) => {
                        const dateString = format(new Date(daily.date), 'dd LLLL uuuu' );
                        const formattedDiffInMinPerDay = toHHMMSS(daily.time_worked_in_s - daily.time_break_in_s);
                        return (
                            <div
                                key={dateString}
                                className="bg-white p-3 mb-1 border-gray-300 border-l-4 hover:border-teal-600"
                                onClick={() => toggleDsDetailView(isEditViewVisible, setIsEditViewVisible, setStartDate, setEndDate, daily)}
                            >
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
                    onClick={() => toggleDsDetailView(isEditViewVisible, setIsEditViewVisible, setStartDate, setEndDate)}>
                    <img src="../images/icons/icons8-plus-math-60.png" width="20" height="20" alt="Start Timer"/>
                </div>
                {/*<div className={`flex flex-col transform z-0 left-0 w-full bg-black shadow-xl overflow-auto ease-in-out transition-all duration-200 ${cn({'h-0': isEditViewVisible}, {'h-screen': !isEditViewVisible})}`}>*/}
                {/*    Ds Edit View*/}
                {/*</div>*/}
                <div
                    className={`fixed z-0 left-0 bottom-0 w-full h-full bg-gray-900 opacity-75${cn({' hidden': !isEditViewVisible})}`}
                />
                <div className={`flex z-10 items-center justify-center fixed left-0 bottom-0 w-full h-full${cn({' hidden': !isEditViewVisible})}`}>
                    <div className="m-3 bg-white rounded-lg w-full">
                        <div className="flex flex-col items-start p-4">
                            <div className="flex items-center w-full">
                                <div className="text-gray-900 font-medium text-lg">Add your daily summary</div>
                                <svg
                                    className="ml-auto fill-current text-gray-700 w-6 h-6 cursor-pointer"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 18 18"
                                    onClick={() => toggleDsDetailView(isEditViewVisible, setIsEditViewVisible, setStartDate, setEndDate)}
                                >
                                    <path d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z"/>
                                </svg>
                            </div>

                            <div className="flex w-full mt-4 justify-center items-center">
                                <div className={`bg-blue-100`}>
                                    <DatePicker
                                        closeOnScroll={false}
                                        selected={startDate}
                                        onChange={date => setStartDate(date)}
                                        locale={de}
                                        showTimeSelect
                                        timeIntervals={1}
                                        dateFormat="dd.M.uuuu HH:ss"
                                        customInput={<TimeInput />}
                                    />
                                </div>
                                <div className={`flex flex-col items-center ml-auto`}>
                                    <div>Total:</div>
                                    <div>{toHHMM(differenceInSeconds(endDate, startDate))}</div>
                                </div>
                                <div className={`ml-auto bg-blue-100`}>
                                    <DatePicker
                                        closeOnScroll={false}
                                        selected={endDate}
                                        onChange={date => setEndDate(date)}
                                        locale={de}
                                        showTimeSelect
                                        timeIntervals={1}
                                        dateFormat="dd.M.uuuu HH:ss"
                                        customInput={<TimeInput />}
                                    />
                                </div>
                            </div>
                            <textarea
                                // value={this.state.textAreaValue}
                                // onChange={this.handleChange}
                                rows={5}
                                placeholder="Put the description of your day here..."
                                // cols={}
                                className={`w-full mt-4 border-2 focus:outline-none p-2`}
                            />
                            <div className={`flex content-center items-center`}>
                                <div className={`text-gray-900 mt-4 text-lg`}>Send mail?</div>
                                <Switch
                                    onChange={setSendMail}
                                    checked={sendMail}
                                    onColor="#38b2ac"
                                    onHandleColor="#ffff"
                                    handleDiameter={25}
                                    uncheckedIcon={false}
                                    checkedIcon={false}
                                    boxShadow="0px 1px 5px rgba(0, 0, 0, 0.6)"
                                    height={20}
                                    width={50}
                                    className="react-switch mt-4 ml-4"
                                    id="material-switch"
                                />
                            </div>

                                <div className="w-full mt-4">
                                    <button
                                        className="bg-teal-500 hover:bg-teal-600 text-white py-2 px-4 w-full rounded"
                                        onClick={() => toggleDsDetailView(isEditViewVisible, setIsEditViewVisible, setStartDate, setEndDate)}
                                    >
                                        Save
                                    </button>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
            }
        </Layout>
    );
}

const toggleDsDetailView = (isEditViewVisible, setIsEditViewVisible,  setStartDate, setEndDate, daily = null) => {
    setIsEditViewVisible(!isEditViewVisible);
    if (daily !== null) {
        setStartDate(new Date(daily.start_time));
        setEndDate(new Date(daily.end_time));
    }
}

const log = () => {
    console.log("hier");
}

/**
 * use proper icons for mail and personio
 * onclick bg of modal close it
 * time picking in datetime picker not showing minutes
 * show right timepicker to the left
 * add mail functionality
 * add save functionality
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
