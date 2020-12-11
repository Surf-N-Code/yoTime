import {differenceInSeconds, format, isBefore, isSameDay} from "date-fns";
import React, {useEffect, useState} from "react";
import cn from 'classnames';
import {toHHMM} from "../utilities/lib";
import de from "date-fns/locale/de";
import Toggle from 'react-toggle'
import {v4 as uuidv4} from 'uuid';
import TimePicker from "react-time-picker/dist/entry.nostyle";
import DatePicker from 'react-date-picker/dist/entry.nostyle';
import 'react-date-picker/dist/DatePicker.css';
import 'react-calendar/dist/Calendar.css';
import {ITimer} from "../types/timer.types";
import {FetcherFunc, useGlobalMessaging, useAuth} from "../services";
import {IDaily} from "../types/daily.types";
import {FormField} from "./FormField";
import {create} from "domain";
import {IDailiesApiResult} from "../types/apiResult.types";

interface IProps {
    mutateDailies: Function;
    toggleDailyEditView: Function;
    isEditViewVisible: boolean;
    dailyToEdit: IDaily;
    data: IDailiesApiResult[];
}

export const DailyEditview = ({mutateDailies, toggleDailyEditView, isEditViewVisible, dailyToEdit, data}: IProps) => {
    const [dailySummaryText, setDailySummaryText] = useState('');
    const [startTime, setStartTime] = useState('09:00');
    const [endTime, setEndTime] = useState('18:00');
    const [sendMail, setSendMail] = useState(true);
    const [auth, setAuth] = useAuth();
    const [date, setDate] = useState(new Date());
    const [breakDuration, setBreakDuration] = useState('00:00');
    const [message, messageDispatch] = useGlobalMessaging();
    const forceUpdate = useForceUpdate();
    const [value, onChange] = useState(new Date());

    useEffect(() => {
        if (dailyToEdit) {
            console.log('use effect edit dailyToEdit ', dailyToEdit)
            const startDate = new Date(dailyToEdit.start_time);
            const endDate = new Date(dailyToEdit.end_time);
            setStartTime(startDate.toTimeString().substr(0,5));
            setEndTime(endDate.toTimeString().substr(0,5));
            setDate(startDate);
            setBreakDuration(toHHMM(dailyToEdit.time_break_in_s))
            setDailySummaryText(dailyToEdit.daily_summary)
            return;
        }

        setStartTime('09:00');
        setEndTime('18:00');
        setDate(new Date());
    }, [dailyToEdit]);

    const createDateFromString = (string) => {
        const hs = Number(string.split(':')[0]);
        const ms = Number(string.split(':')[1]);
        return new Date(date.getFullYear(), date.getMonth(), date.getDate(), hs, ms, 0);
    }

    const deleteDaily = async (dailyId) => {
        await mutateDailies((data) => {
            let newData = {...data};
            return {...data, "hydra:member": [...newData["hydra:member"].filter(daily => daily.id !== dailyId)]};
        }, false);

        fetch(`https://localhost:8443/daily_summaries/${dailyId}`, {
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
        toggleDailyEditView();
        setBreakDuration('00:00');
    }

    const addOrUpdateDaily = async (dailyToEdit: IDaily) => {
        toggleDailyEditView();
        const startDate = createDateFromString(startTime);
        const endDate = createDateFromString(endTime);
        const startBreak = createDateFromString('00:00');
        const breakEnd = createDateFromString(breakDuration);
        if (isBefore(endDate, startDate)) {
            messageDispatch({
                type: 'setMessage',
                payload: {
                    message: 'The end time should be after the start time.'
                }
            })
            return;
        }
        const tempId = uuidv4();

        const updatedDaily = {
            date: date,
            daily_summary: dailySummaryText,
            is_email_sent: sendMail,
            is_synced_to_personio: true,
            start_time: startDate,
            end_time: endDate,
            time_break_in_s: differenceInSeconds(breakEnd, startBreak),
            time_worked_in_s: differenceInSeconds(endDate, startDate)
        }

        if (!dailyToEdit) {
            if (data["hydra:member"].some((daily) => isSameDay(new Date(daily.date), new Date(updatedDaily.date)))) {
                messageDispatch({
                    type: 'setMessage',
                    payload: {
                        message: 'You already have a daily summary for that day'
                    }
                })
                return;
            }

            if (updatedDaily.daily_summary === '') {
                messageDispatch({
                    type: 'setMessage',
                    payload: {
                        message: 'Please provide a text for your daily summary'
                    }
                })
                return;
            }
            await mutateDailies((data) => {
                const newHydra = [{id: tempId, ...updatedDaily}, ...data['hydra:member']];
                return {...data, "hydra:member": newHydra.sort((a,b) => new Date(b.start_time).getTime() - new Date(a.end_time).getTime())};
            }, false);
            await FetcherFunc(`/daily_summaries`, auth.jwt, 'POST', updatedDaily);
            return;
        }

        await mutateDailies((data) => {
            let newData = {...data};
            newData["hydra:member"].map((daily) => {
                if (daily.id === dailyToEdit.id) {
                    daily.start_time = startDate;
                    daily.end_time = endDate;
                    daily.date = date;
                    daily.daily_summary = dailySummaryText;
                    daily.is_email_sent = sendMail;
                    daily.is_synced_to_personio = true;
                    daily.time_break_in_s = differenceInSeconds(breakEnd, startBreak);
                    daily.time_worked_in_s = differenceInSeconds(endDate, startDate);
                }
            })
            return {...data, "hydra:member": [...newData['hydra:member']]};
        }, false);
        await FetcherFunc(`/daily_summaries/${dailyToEdit.id}`, auth.jwt, 'PATCH', updatedDaily, 'application/merge-patch+json');
        toggleDailyEditView();
        setBreakDuration('00:00');
    }

    return (
        <div>
            <div className={`${cn({'slide-in-bottom-daily': isEditViewVisible})} daily-edit-drawer z-10 fixed bottom-0 left-0 w-full pl-3 pr-3 border-t-2 border-white bg-gradient-to-br from-teal-400 to-teal-500 shadow-md text-white rounded-tr-lg rounded-tl-lg`}>
                <div className="flex flex-col pt-3 pb-5 px-3">
                    <svg
                        className="ml-auto fill-current text-white w-6 h-6 cursor-pointer mr-3"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 18 18"
                        onClick={() => toggleDailyEditView()}
                    >
                        <path d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z"/>
                    </svg>
                    <div className="flex flex-col">
                        <div className="mb-2 text-md">
                            Date
                        </div>
                        <DatePicker
                            className={'timer-date-picker'}
                            calendarIcon={null}
                            clearIcon={null}
                            onChange={setDate}
                            value={date}
                        />
                    </div>
                    <div className="flex">
                        <div className="flex flex-col py-3 w-4/12">
                            <div className="mb-2 text-md text-center">
                                Start Time
                            </div>
                            <TimePicker
                                onChange={setStartTime}
                                value={startTime}
                                disableClock={true}
                                format={"HH:mm"}
                                minTime={"00:00"}
                                maxTime={"23:59"}
                                clearIcon={null}
                            />
                        </div>
                        <div className={`flex flex-col py-3 items-center ml-auto`}>
                            <div>Break:</div>
                            <div className="mt-2">
                                <TimePicker
                                    onChange={setBreakDuration}
                                    value={breakDuration}
                                    disableClock={true}
                                    format={"HH:mm"}
                                    minTime={"00:00"}
                                    maxTime={"23:59"}
                                    clearIcon={null}
                                />
                            </div>
                        </div>
                        <div className="flex flex-col py-3 ml-auto w-4/12">
                            <div className="mb-2 text-md text-center">
                                End Time
                            </div>
                            <TimePicker
                                onChange={setEndTime}
                                value={endTime}
                                disableClock={true}
                                format={"HH:mm"}
                                minTime={"00:00"}
                                maxTime={"23:59"}
                                clearIcon={null}
                            />
                        </div>
                    </div>
                    <textarea
                        value={dailySummaryText}
                        onChange={(e) => setDailySummaryText(e.target.value)}
                        rows={5}
                        placeholder="Put the description of your day here..."
                        className={`w-full mt-4 border-2 focus:outline-none p-2 text-blueGray-500`}
                    />

                    <div className="flex items-center mt-3">
                        {dailyToEdit === null || (dailyToEdit && !dailyToEdit.is_email_sent) ?
                            <div className="flex mt-2">
                                Send E-Mail?
                                <Toggle
                                    defaultChecked={sendMail}
                                    icons={true}
                                    onChange={setSendMail}
                                    className='mail-toggle ml-3'
                                />
                            </div> : ''
                        }
                        <button
                            className={`${cn({'w-full ': dailyToEdit && dailyToEdit.is_email_sent})}mt-3 ml-auto px-10 py-2 font-bold text-white bg-gradient-to-br from-blueGray-500 to-blueGray-400 rounded-lg hover:bg-blue-700 focus:outline-none focus:shadow-outline`}
                            type="submit"
                            onClick={() => addOrUpdateDaily(dailyToEdit)}
                        >{dailyToEdit ? 'Update' : 'Add'}</button>
                    </div>
                    {dailyToEdit ?
                        <button
                            className="w-full mt-5 ml-auto px-10 py-2 font-bold text-white bg-rose-500 rounded-lg hover:bg-rose-600 focus:outline-none focus:shadow-outline"
                            type="submit"
                            onClick={() => deleteDaily(dailyToEdit.id)}
                        >Delete</button> : ''
                    }
                </div>
            </div>
        </div>
    )
}

const useForceUpdate = () => {
    const [value, setValue] = useState(0); // integer state
    return () => setValue(value => ++value); // update the state to force render
}
