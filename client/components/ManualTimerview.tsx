import React, {useEffect, useState} from "react";
import TimePicker from "react-time-picker/dist/entry.nostyle";
import DatePicker from 'react-date-picker/dist/entry.nostyle';
import 'react-date-picker/dist/DatePicker.css';
import 'react-calendar/dist/Calendar.css';
import {isBefore} from "date-fns";
import {useGlobalMessaging} from "../services";
import {v4 as uuidv4} from 'uuid';
import {FetcherFunc} from "../services/Fetcher.service";
import Toggle from 'react-toggle'
import {useAuth} from "../services/Auth.context";
import cn from 'classnames';
import {ITimer} from "../types/timer.types";

export const ManualTimerview = ({mutateTimers, toggleAddTimerView, isVisible, timerToEdit}) => {
    const [isBreakTimer, setIsBreakTimer] = useState(false);
    const [startTimer, setStartTime] = useState('09:00');
    const [endTime, setEndTime] = useState('18:00');
    const [date, setDate] = useState(new Date());
    const [messageState, messageDispatch] = useGlobalMessaging();
    const [auth, authDispatch] = useAuth();
    // const [isEditTimer, setIsEditTimer] = useState(false);
    // const [timerToEdit, setTimerToEdit] = useState<ITimer>();
    console.log('manual timer view', timerToEdit);

    useEffect(() => {
        if (timerToEdit) {
            console.log('use effect edit timerToEdit ', timerToEdit)
            const startDate = new Date(timerToEdit.date_start);
            const endDate = new Date(timerToEdit.date_end);
            setStartTime(startDate.toTimeString().substr(0,5));
            setEndTime(endDate.toTimeString().substr(0,5));
            setDate(startDate);
            // setIsEditTimer(true);
            // setTimerToEdit(timerToEdit);
        }
    }, [timerToEdit]);

    const addTimerManually = async (timerToEdit: ITimer) => {
        const hs = Number(startTimer.split(':')[0]);
        const ms = Number(startTimer.split(':')[1]);
        const he = Number(endTime.split(':')[0]);
        const me = Number(endTime.split(':')[1]);
        const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate(), hs, ms, 0);
        const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate(), he, me, 0);
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
        const updatedTimer = {
            '@Type': 'Timer',
            date_start: startDate,
            date_end: endDate,
            timer_type: isBreakTimer ? 'break' : 'work'
        }

        console.log('timer in add timer', timerToEdit);
        if (!timerToEdit) {
            console.log('adding timer');
            await mutateTimers((data) => {
                let newData = {...data};
                const t = {id: tempId, ...updatedTimer};
                newData["hydra:member"].push({id: tempId, ...updatedTimer});
                return {...data, "hydra:member": [...newData['hydra:member']]};
            }, false);
            await FetcherFunc(`/timers`, auth.jwt, 'POST', updatedTimer);
            toggleAddTimerView();
            // setIsEditTimer(false);
            return;
        }
        await mutateTimers((data) => {
            let newData = {...data};
            newData["hydra:member"].map((timer) => {
                if (timer.id === timerToEdit.id) {
                    timer.date_start = startDate;
                    timer.date_end = endDate;
                    timer.timer_type = isBreakTimer ? 'break' : 'work';
                }
            })
            return {...data, "hydra:member": [...newData['hydra:member']]};
        }, false);
        await FetcherFunc(`/timers/${timerToEdit.id}`, auth.jwt, 'PATCH', updatedTimer, 'application/merge-patch+json');
        toggleAddTimerView();
        // setIsEditTimer(false);
    }

    return (
        <div className={`${cn({'slide-in-bottom': isVisible}, {'slide-out-bottom': !isVisible})} z-10 fixed bottom-0 left-0 w-full pl-3 pr-3 border-t-2 border-white bg-gradient-to-br from-teal-400 to-teal-500 shadow-md text-white rounded-tr-lg rounded-tl-lg`}>
            <div className="flex flex-col p-3 mt-3">
                <div className="mb-2 text-lg">
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
            <div className="flex flex-col p-3">
                <div className="mb-2 text-lg">
                    Start Time
                </div>
                <TimePicker
                    onChange={setStartTime}
                    value={startTimer}
                    disableClock={true}
                    format={"HH:mm"}
                    minTime={"00:00"}
                    maxTime={"23:59"}
                    clearIcon={null}
                />
            </div>
            <div className="flex flex-col p-3">
                <div className="mb-2 text-lg">
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
            <div className="flex items-center p-3 mb-3 ml-auto">
                <div className="mr-3 mb-1 text-lg">
                    Work
                </div>
                <Toggle
                    defaultChecked={isBreakTimer}
                    icons={false}
                    onChange={() => setIsBreakTimer((prevVal) => !prevVal)}
                    className='timer-type-toggle'
                />
                <div className="ml-3 mb-1 text-lg">
                    Break
                </div>
                <button className="ml-auto px-4 py-2 font-bold text-white border-gray-200 border-2 bg-gradient-to-br from-blue-500 to-blue-400 rounded-full hover:bg-blue-700 focus:outline-none focus:shadow-outline"
                        type="submit"
                        onClick={() => addTimerManually(timerToEdit)}
                >Add</button>
            </div>
        </div>
    )
}

export default ManualTimerview;
