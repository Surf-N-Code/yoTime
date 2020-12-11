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

    const addOrUpdateTimer = async (timerToEdit: ITimer) => {
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
                const newHydra = [{id: tempId, ...updatedTimer}, ...data['hydra:member']];
                return {...data, "hydra:member": newHydra.sort((a,b) => new Date(b.date_start).getTime() - new Date(a.date_start).getTime())};
            }, false);
            await FetcherFunc(`/timers`, auth.jwt, 'POST', updatedTimer);
            toggleAddTimerView();
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
    }

    return (
        <div className={`${cn({'slide-in-bottom ': isVisible})} timer-edit-drawer z-10 fixed bottom-0 left-0 w-full pl-3 pr-3 border-t-2 border-white bg-gradient-to-br from-teal-400 to-teal-500 shadow-md text-white rounded-tr-lg rounded-tl-lg`}>
            <svg
                className="ml-auto fill-current text-white w-6 h-6 cursor-pointer mt-3"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 18 18"
                onClick={() => toggleAddTimerView()}
            >
                <path d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z"/>
            </svg>
            <div className="flex flex-col px-3 pb-3">
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
            <div className="flex flex-col p-3">
                <div className="mb-2 text-md">
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
                <div className="mb-2 text-md">
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
                <div className="mr-3 mb-1 text-md">
                    Work
                </div>
                <Toggle
                    defaultChecked={isBreakTimer}
                    icons={false}
                    onChange={() => setIsBreakTimer((prevVal) => !prevVal)}
                    className='timer-type-toggle'
                />
                <div className="ml-3 mb-1 text-md">
                    Break
                </div>
                <button className="ml-auto px-10 py-2 font-bold text-white bg-gradient-to-br from-blueGray-500 to-blueGray-400 rounded-full hover:bg-blue-700 focus:outline-none focus:shadow-outline"
                        type="submit"
                        onClick={() => addOrUpdateTimer(timerToEdit)}
                >{timerToEdit ? 'Update' : 'Add'}</button>
            </div>
        </div>
    )
}

export default ManualTimerview;
