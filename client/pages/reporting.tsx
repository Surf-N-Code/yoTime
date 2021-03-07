import React, {useEffect, useState} from 'react';
import {Layout, Pagination} from "../components";
import {useRouter} from "next/router";
import useSWR from "swr";
import cn from 'classnames';
import {useAuth} from "../services/Auth.context";
import {IDailiesApiResult, IUserApiResult} from "../types/apiResult.types";
import {IDaily} from "../types/daily.types";
import {IsoFetcher, FetcherFunc, TokenService} from "../services";
import {format} from "date-fns";
import {toHHMM} from "../utilities";
import {GetServerSideProps} from "next";
import Cookies from "universal-cookie";
import {IUser} from "../types/user.types";
import Link from "next/link";

const ReportingData = (pageIndex, sortOption, sortAscending, selectedUser, initialData) => {
    const [auth, authDispatch] = useAuth();
    let url = new URL(`https://localhost:8443/daily_summaries?order[${sortOption}]=${sortAscending ? 'asc' : 'desc'}`);
    if (pageIndex) {
        url.searchParams.append('page', pageIndex)
    }

    if (selectedUser) {
        url.pathname = `${selectedUser['@id']}/daily_summaries`;
    }

    return useSWR<IDailiesApiResult>([url.pathname+url.search, auth.jwt, 'GET'], FetcherFunc, {initialData})
}

export const Reporting = (props) => {
    const router = useRouter();
    const [auth, authDispatch] = useAuth();
    const [isDailySummaryVisible, setIsDailySummaryVisible] = useState(false);
    const [selectedUser, setSelectedUser] = useState(null);
    const [hoverSelectedUser, setHoverSelectedUser] = useState(false);
    const [isUserSelectVisible, setIsUserSelectVisible] = useState(false);
    const [isSortingSelectVisible, setIsSortingSelectVisible] = useState(false);
    const [sortAscending, setSortAscending] = useState(false);
    const [pageIndex, setPageIndex] = useState(1);
    const [sortOption, setSortOption] = useState<'date' | 'user.first_name'>('date');

    const { data: dailies, error: dailiesError } = ReportingData(pageIndex, sortOption, sortAscending, selectedUser, props.initialData.dailies);
    ReportingData(pageIndex+1, sortOption, sortAscending, selectedUser, props.initialData.dailies);

    const { data: users, error: usersError } = useSWR<IUserApiResult>(['users', auth.jwt, 'GET'], FetcherFunc);

    useEffect(() => {
        if (dailies && typeof dailies.code !== 'undefined' && dailies.code === 401) {
            const tokenService = new TokenService();
            authDispatch({
                type: 'removeAuthDetails'
            });
            tokenService.deleteToken();
            router.push('/reporting');
        }
    }, [dailies]);

    const selectSortOption = (option) => {
        setSortOption(option);
        setIsSortingSelectVisible(prev => !prev);
    }

    if (dailiesError) return <div>failed to load</div>
    if (!dailies) return <div>loading...</div>

    const colWidthMd = selectedUser ? 'md:w-1/5' : 'md:w-1/6';
    const multipleUsers = users?.['hydra:totalItems'] > 1;

    console.log('users',users, 'multiple users', multipleUsers);
    const setUserFilter = (user: IUser) => {
        setIsUserSelectVisible(prev => !prev);
        setIsSortingSelectVisible(prev => !prev)
        setSelectedUser((prevUser) => {
            if (prevUser?.["@id"] === user["@id"]) {
                return null;
            }
            return user;
        });
    }

    return (
        <Layout validToken={props.validToken}>
            <div className="mt-6 mb-24 z-10">
                {dailies?.['hydra:member']?.length === 0 ? <div>There are no daily summaries for your organisation yet</div>
                    :
                    <>
                        {isUserSelectVisible ? <div className="w-full h-full absolute left-0 top-0" onClick={() => setIsUserSelectVisible(prev => !prev)}/> : ''}
                        {isSortingSelectVisible ? <div className="w-full h-full absolute left-0 top-0" onClick={() => setIsSortingSelectVisible(prev => !prev)}/> : ''}
                        <div className="flex">
                            {multipleUsers ?
                                <div>
                                    <div className="relative inline-block text-left">
                                        <div>
                                            <button type="button"
                                                    className="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
                                                    id="options-menu" aria-haspopup="true" aria-expanded="true"
                                                    onClick={() => {setIsUserSelectVisible(prev => !prev)}}
                                            >
                                                {selectedUser ? selectedUser?.first_name +' '+ selectedUser?.last_name : 'Employee'}
                                                <svg className={`-mr - 1 ml-2 h-5 w-5 transform transition ease-out ${cn({'-rotate-90': !isUserSelectVisible})}`} xmlns="http://www.w3.org/2000/svg"
                                                     viewBox="0 0 20 20" aria-hidden="true">
                                                    <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                          clipRule="evenodd"/>
                                                </svg>
                                            </button>
                                        </div>

                                        <div
                                            className={`${cn({
                                                'transition ease-out duration-100 transform opacity-100 scale-100 ': isUserSelectVisible,
                                                'transition ease-in duration-75 transform opacity-0 scale-95 z-0 hidden ': !isUserSelectVisible
                                            })}origin-top-right absolute left-0 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 transition`}>
                                            <div role="menu" aria-orientation="vertical"
                                                 aria-labelledby="options-menu">
                                                {users['hydra:member'].map((user: IUser, i) => {
                                                    return (
                                                        <a
                                                            key={user["@id"]}
                                                            className={`flex items-center block cursor-pointer pl-4 py-2 text-sm text-gray-700 hover:bg-gray-100 ${cn({'bg-gray-100 js-user-selected': selectedUser?.['@id'] === user['@id']}, {'hover:rounded-b-md': users['hydra:totalItems'] === i+1}, {'hover:rounded-t-md': i === 0})} hover:text-gray-900`}
                                                            onClick={() => setUserFilter(user)}
                                                            onMouseEnter={() => setHoverSelectedUser(selectedUser?.['@id'] === user['@id'])}
                                                            onMouseLeave={() => setHoverSelectedUser(false)}
                                                        >
                                                            <div
                                                                className="pr-3 w-5/6">{user.first_name} {user.last_name}</div>
                                                            {
                                                                selectedUser?.['@id'] === user['@id'] ?
                                                                    <div className="w-1/6">
                                                                        <svg
                                                                            className={`js-close-icon h-4 w-4 fill-current text-red-500 ${cn({'hidden': !hoverSelectedUser && selectedUser?.['@id'] === user['@id']})}`}
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path strokeLinecap="round"
                                                                                  strokeLinejoin="round" strokeWidth="2"
                                                                                  d="M6 18L18 6M6 6l12 12"/>
                                                                        </svg>
                                                                        <svg
                                                                            className={`js-green-checkmark h-4 w-4 fill-current text-teal-500  ${cn({'hidden': hoverSelectedUser})}`}
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            viewBox="0 0 24 24">
                                                                            <path
                                                                                d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10S2 17.514 2 12 6.486 2 12 2zm0-2C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm6.25 8.891l-1.421-1.409-6.105 6.218-3.078-2.937-1.396 1.436 4.5 4.319 7.5-7.627z"/>
                                                                        </svg>
                                                                    </div>
                                                                    : ''
                                                            }
                                                        </a>
                                                    )
                                                })}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                :
                                ''
                            }

                            {multipleUsers ?
                                <div>
                                    <div className="relative inline-block text-left ml-2">
                                        <div>
                                            <button type="button"
                                                    className="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
                                                    id="options-menu" aria-haspopup="true" aria-expanded="true"
                                                    onClick={() => {
                                                        setIsSortingSelectVisible(prev => !prev);
                                                    }}
                                            >
                                                {sortOption ? `Sorted by ${sortOption === 'user.first_name' ? 'User' : 'Date'}` : 'Sorting'}
                                                <svg
                                                    className={`-mr - 1 ml-2 h-5 w-5 transform transition ease-out ${cn({'-rotate-90': !isSortingSelectVisible})}`}
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20" aria-hidden="true">
                                                    <path
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clipRule="evenodd"/>
                                                </svg>
                                            </button>
                                        </div>

                                        <div
                                            className={`${cn({
                                                'transition ease-out duration-100 transform opacity-100 scale-100 ': isSortingSelectVisible,
                                                'transition ease-in duration-75 transform opacity-0 scale-95 z-0 hidden ': !isSortingSelectVisible
                                            })}origin-top-right absolute left-0 w-20 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 transition`}>
                                            <div role="menu" aria-orientation="vertical"
                                                 aria-labelledby="options-menu">
                                                <a
                                                    className={`flex items-center block cursor-pointer pl-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:rounded-t-md hover:text-gray-900`}
                                                    onClick={() => selectSortOption('date')}
                                                >
                                                    <div className="pr-3">Date</div>
                                                </a>
                                                <a
                                                    className={`flex items-center block cursor-pointer pl-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:rounded-b-md hover:text-gray-900`}
                                                    onClick={() => selectSortOption('user.first_name')}
                                                >
                                                    <div className="pr-3">User</div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                : ''
                            }

                            <div className={`inline-flex items-center cursor-pointer${cn({' ml-2': multipleUsers})}`}>
                                <div className="mr-2">{multipleUsers ? '' : 'Sort by date:'}</div>
                                <svg
                                    className={`inline-flex w-6 h-6${cn({' transform rotate-180': !sortAscending})}`}
                                    onClick={() => setSortAscending(prev => !prev)}
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                >
                                    <title>{sortAscending ? 'Sorted by date in ascending order' : 'Sorted by date in descending order'}</title>
                                    <path className="secondary"
                                          d="M18 13v7a1 1 0 0 1-2 0v-7h-3a1 1 0 0 1-.7-1.7l4-4a1 1 0 0 1 1.4 0l4 4A1 1 0 0 1 21 13h-3z"/>
                                    <path className="primary"
                                          d="M3 3h13a1 1 0 0 1 0 2H3a1 1 0 1 1 0-2zm0 4h9a1 1 0 0 1 0 2H3a1 1 0 1 1 0-2zm0 4h5a1 1 0 0 1 0 2H3a1 1 0 0 1 0-2z"/>
                                </svg>
                            </div>
                        </div>


                        <dl className="bg-white rounded-md mt-6">
                            <div key="daily-tale-header" className="flex items-center hidden lg:flex py-3 px-3 border-b-0 border-bottom-gray-200">
                                <dt className={`font-bold ${colWidthMd} lg:w-1/12`}>Date</dt>
                                {props.selectedUser ? '' : <dt className="font-bold w-1/6 lg:w-1/12">User</dt>}
                                <dt className={`font-bold ${colWidthMd} ${cn({'lg:w-7/12': props.selectedUser}, {'lg:w-6/12': !props.selectedUser})} hidden lg:block`}>Daily</dt>
                                <dt className={`font-bold ${colWidthMd} lg:w-1/12 md:hidden text-center`}>Net Work (h:m)</dt>
                                <dt className={`font-bold ${colWidthMd} lg:w-1/12 hidden md:block text-center`}>Work (h:m)</dt>
                                <dt className={`font-bold ${colWidthMd} lg:w-1/12 hidden md:block text-center`}>Break (h:m)</dt>
                                <dt className={`font-bold ${colWidthMd} lg:w-1/12 text-center`}>Start</dt>
                                <dt className={`font-bold ${colWidthMd} lg:w-1/12 text-center`}>End</dt>
                            </div>
                            {dailies['hydra:member'].map((daily: IDaily, i) => {
                                return (
                                    <div  key={daily.id}>
                                        <div className={`js-daily-parent flex flex-col lg:hidden items-start py-4 px-3 position-relative border-b-2 ${cn({' bg-gray-100': i % 2 != 0 })}`}>
                                            <div>{format(new Date(daily.date), 'dd.MM.yy')}</div>
                                            {props.selectedUser ? '' : <div className="mt-3 text-lg font-bold">{daily.user.first_name} {daily.user.last_name}</div>}
                                            <div className={`flex w-full${cn({' mt-3' : props.selectedUser})}`}>
                                                <div className="flex flex-col">
                                                    <div>Sign-In:</div>
                                                    <div>Sign-Out:</div>
                                                </div>
                                                <div className="flex flex-col ml-4">
                                                    <div>{format(new Date(daily.start_time), 'HH:mm')}</div>
                                                    <div>{format(new Date(daily.end_time), 'HH:mm')}</div>
                                                </div>

                                                <div className="flex flex-col ml-auto">
                                                    <div>Worktime:</div>
                                                    <div>Breaktime:</div>
                                                </div>
                                                <div className="flex flex-col ml-4">
                                                    <div>{toHHMM(daily.time_worked_in_s - daily.time_break_in_s)}</div>
                                                    <div>{toHHMM(daily.time_break_in_s)}</div>
                                                </div>
                                            </div>
                                            <div
                                                className={`w-full mt-3 overflow-x-hidden text-left lg:hidden`}
                                                onClick={() => setIsDailySummaryVisible(false)}
                                            >
                                                {daily.daily_summary}
                                            </div>
                                        </div>
                                        <div className={`js-daily-parent items-center hidden lg:flex py-4 px-3 position-relative ${cn({' bg-gray-100': i % 2 == 0})}`}>
                                            <dt className={`${colWidthMd} lg:w-1/12 text-left overflow-x-hidden pr-3`}>{format(new Date(daily.date), 'dd.MM.yy')}</dt>
                                            {props.selectedUser ? '' : <dt className="w-1/6 lg:w-1/12 overflow-x-hidden pr-3 hidden lg:block">{daily.user.first_name} {daily.user.last_name}</dt>}
                                            {props.selectedUser ? '' : <dt className="w-1/6 lg:w-1/12 overflow-x-hidden pr-3 lg:hidden">{daily.user.first_name} {daily.user.last_name.substr(0,1)}.</dt>}
                                            <dt className={`${colWidthMd} ${cn({'lg:w-7/12':props.selectedUser}, {'lg:w-6/12':!props.selectedUser})} overflow-x-hidden hidden lg:block pr-3`}>{daily.daily_summary}</dt>
                                            <dt className={`${colWidthMd} lg:w-1/12 overflow-x-hidden md:hidden text-center`}>{toHHMM(daily.time_worked_in_s - daily.time_break_in_s)}</dt>
                                            <dt className={`${colWidthMd} lg:w-1/12 overflow-x-hidden hidden md:block text-center`}>{toHHMM(daily.time_worked_in_s)}</dt>
                                            <dt className={`${colWidthMd} lg:w-1/12 overflow-x-hidden hidden md:block text-center`}>{toHHMM(daily.time_break_in_s)}</dt>
                                            <dt className={`${colWidthMd} lg:w-1/12 overflow-x-hidden text-center`}>{format(new Date(daily.start_time), 'HH:mm')}</dt>
                                            <dt className={`${colWidthMd} lg:w-1/12 overflow-x-hidden text-center`}>{format(new Date(daily.end_time), 'HH:mm')}</dt>
                                        </div>
                                    </div>
                                )
                            })}
                        </dl>
                    </>
                }
                <div>
                    <Pagination
                        currentPage={pageIndex}
                        setPageIndex={setPageIndex}
                        totalPages={Math.ceil(typeof dailies === 'undefined' || typeof dailies['hydra:member'] === 'undefined' || dailies['hydra:member'].length === 0 ? 30 / 30 : dailies['hydra:totalItems'] / 30)}
                        path={'reporting'}
                    />
                </div>
            </div>
        </Layout>
    )
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
    let url = new URL(`${process.env.API_BASE_URL}/daily_summaries?order[date]=desc`);
    const token = cookies.get('token');
    const dailies = await IsoFetcher.isofetchAuthed(url.href, 'GET', token);
    return { props: { validToken, ApiBaseUrl: process.env.API_BASE_URL, initialData: dailies} };
};
export default Reporting;
