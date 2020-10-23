import {differenceInSeconds} from "date-fns";
import React from "react";
import cn from 'classnames';
import {toHHMM} from "../utilities/lib";
import de from "date-fns/locale/de";
import Toggle from 'react-toggle'
import DatePicker, { registerLocale, setDefaultLocale } from  "react-datepicker";

export const DailyEditView = ({startDate, endDate, setStartDate, setEndDate, setSendMail, sendMail, isEditViewVisible, onClick}) => {

    console.log(startDate, endDate);
    const TimeInput = ({ value, onClick }) => {
        let date = value.split(' ');
        console.log(date, value);
        return (
            <button className="py-5 p-3" onClick={onClick}>
                {date[0]}<br />{date[1]}
            </button>
        )
    };
    registerLocale('de', de)
    setDefaultLocale('de')

    return (
        <div>
            <div
                className={`fixed left-0 bottom-0 w-full h-full bg-gray-900 opacity-75${cn({' hidden': !isEditViewVisible})}`}
                onClick={() => onClick()}
            />
            <div className={`flex items-center justify-center fixed left-0 bottom-0 w-full${cn({' hidden': !isEditViewVisible})}`}>
                <div className="m-3 bg-white rounded-lg w-full">
                    <div className="flex flex-col items-start p-4">
                        <div className="flex items-center w-full">
                            <div className="text-gray-900 font-medium text-lg">Add your daily summary</div>
                            <svg
                                className="ml-auto fill-current text-gray-700 w-6 h-6 cursor-pointer"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 18 18"
                                onClick={() => onClick()}
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
                                    dateFormat="dd.M.uuuu HH:mm"
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
                                    dateFormat="dd.M.uuuu HH:mm"
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
                        <div className={`flex content-center items-center mt-4`}>
                            <div className={`text-gray-900 text-lg mr-3`}>Send mail?</div>
                            <Toggle
                                defaultChecked={sendMail}
                                icons={false}
                                onChange={setSendMail}
                                className='mail-toggle'
                            />
                        </div>

                        <div className="w-full mt-4">
                            <button
                                className="bg-gradient-to-br from-teal-400 to-teal-500 hover:from-teal-500 hover:to-teal-600 text-white py-2 px-4 w-full rounded"
                                onClick={() => onClick()}
                            >
                                Save
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
}
