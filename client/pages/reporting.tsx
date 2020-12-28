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

export const Dashboard = (props) => {
    const router = useRouter();
    const [auth, authDispatch] = useAuth();
    const [isDailySummaryVisible, setIsDailySummaryVisible] = useState(false);
    const [dailyText, setDailyText] = useState('');
    const [selectedUser, setSelectedUser] = useState(null);
    const [isUserSelectVisible, setIsUserSelectVisible] = useState(false);
    const currentPage = typeof router.query.page !== 'undefined' ? router.query.page : 1;
    const userQueryParam = typeof router.query.user !== 'undefined' ? router.query.user : null;
    console.log(router.query);
    let url = new URL(`${props.ApiBaseUrl}/daily_summaries?order[date]`);
    if (currentPage) {
        console.log('append page', currentPage);
        url.searchParams.append('page', currentPage)
    }

    if (userQueryParam) {
        url.pathname = `${userQueryParam}/daily_summaries`;
    }
    console.log('url', url.pathname,url.search);

    const initialData = props.initialData.dailies;
    const { data, error, mutate: mutateDailies } = useSWR<IDailiesApiResult>([url.pathname+url.search, auth.jwt, 'GET'], FetcherFunc, {initialData})

    // const userUrl = `/daily_summaries?order[date]&page=${currentPage}`;
    // const { data: userData, error: userError } = useSWR<IUserApiResult>([userUrl, auth.jwt, 'GET'], FetcherFunc, {initialData})

    console.log(data);
    useEffect(() => {
        console.log('router', router.query)
        if (router.query.user) {
            router.push( `/reporting?user=${router.query.user}`);
            console.log('pushed to router');
        }
    }, [router.query.user])

    useEffect(() => {
        setSelectedUser(props.selectedUser[0]);
    }, [props.selectedUser])

    useEffect(() => {
        if (data && typeof data.code !== 'undefined' && data.code === 401) {
            const tokenService = new TokenService();
            authDispatch({
                type: 'removeAuthDetails'
            });
            tokenService.deleteToken();
            router.push('/reporting');
        }
    }, [data]);

    if (error) return <div>failed to load</div>
    if (!data) return <div>loading...</div>

    const showDailySummary = (e, daily: IDaily) => {
        let dailyRow = document.getElementsByClassName('js-daily-popup');
        for (let d = 0; d < dailyRow.length; d++) {
            dailyRow[d].classList.add('hidden');
        }
        let children = [];
        if (e.target.classList.contains('daily-parent')) {
            children = e.target.children;
        } else {
            children = e.target.parentElement.children
        }
        for (let i = 0; i < children.length; i++) {
            let child = children[i];
            if (child.classList.contains('js-daily-popup')) {
                child.classList.toggle('hidden');
            }
        }
        setIsDailySummaryVisible((prev) => !prev);
        setDailyText(daily.daily_summary);
    }

    const colWidthSm = selectedUser ? 'w-1/4' : 'w-1/5';
    const colWidthMd = selectedUser ? 'md:w-1/5' : 'md:w-1/6';
    console.log(colWidthSm, selectedUser, 'col width')

    return (
        <Layout>
            <div className="mt-6 mb-24">
                {typeof data['hydra:member'] === 'undefined' || data['hydra:member'].length === 0 ? <div>no data yet</div>
                    :
                    <>
                        <div>
                            <div className="relative inline-block text-left">
                                <div>
                                    <button type="button"
                                            className="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
                                            id="options-menu" aria-haspopup="true" aria-expanded="true"
                                            onClick={() => {setIsUserSelectVisible(prev => !prev)}}
                                    >
                                        {props.selectedUser[0] ? (props.selectedUser[0].first_name +' '+ props.selectedUser[0].last_name) : 'Employee'}
                                        <svg className="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                                             viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fillRule="evenodd"
                                                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                  clipRule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                                <div
                                    className={`${cn({'transition ease-out duration-100 transform opacity-100 scale-100 ': isUserSelectVisible, 'transition ease-in duration-75 transform opacity-0 scale-95 ': !isUserSelectVisible})}origin-top-right absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 transition border-2 border-teal-200`}>
                                    <div className="py-1" role="menu" aria-orientation="vertical"
                                         aria-labelledby="options-menu">
                                        {props.initialData.users['hydra:member'].map((user: IUser) => (
                                            <Link href={`/reporting?user=${user['@id']}`} key={user["@id"]}>
                                                <a
                                                    className="block cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                                    onClick={() => setIsUserSelectVisible(prev => !prev)}
                                                >
                                                    {user.first_name} {user.last_name}
                                                </a>
                                            </Link>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <dl className="bg-white rounded-md mt-6">
                            <div key="daily-tale-header" className="flex py-3 px-3 border-b-0 border-bottom-gray-200">
                                <dt className={`font-bold ${colWidthSm} ${colWidthMd} lg:w-1/12`}>Date</dt>
                                {props.selectedUser[0] ? '' : <dt className="font-bold w-1/6 lg:w-1/12">User</dt>}
                                <dt className={`font-bold ${colWidthSm} ${colWidthMd} ${cn({'lg:w-5/12': props.selectedUser[0]}, {'lg:w-4/12': !props.selectedUser[0]})} hidden lg:block`}>Daily</dt>
                                <dt className={`font-bold ${colWidthSm} ${colWidthMd} lg:w-1/12 md:hidden text-center`}>Net Work (h:m)</dt>
                                <dt className={`font-bold ${colWidthSm} ${colWidthMd} lg:w-2/12 hidden md:block text-center`}>Work (h:m)</dt>
                                <dt className={`font-bold ${colWidthSm} ${colWidthMd} lg:w-2/12 hidden md:block text-center`}>Break (h:m)</dt>
                                <dt className={`font-bold ${colWidthSm} ${colWidthMd} lg:w-1/12 text-center`}>Start</dt>
                                <dt className={`font-bold ${colWidthSm} ${colWidthMd} lg:w-1/12 text-center`}>End</dt>
                            </div>
                            {data['hydra:member'].map((daily: IDaily, i) => {
                                return (
                                <div key={daily.id}
                                     className={`js-daily-parent flex items-center py-4 px-3 position-relative cursor-pointer${cn({' bg-gray-50': i % 2 == 0})}`}
                                     onClick={(e) => showDailySummary(e, daily)}>
                                    <dt className={`${colWidthSm} ${colWidthMd} lg:w-1/12 text-left overflow-x-hidden pr-3`}>{format(new Date(daily.date), 'dd.MM.yy')}</dt>
                                    {props.selectedUser[0] ? '' : <dt className="w-1/6 lg:w-1/12 overflow-x-hidden pr-3 hidden lg:block">{daily.user.first_name} {daily.user.last_name}</dt>}
                                    {props.selectedUser[0] ? '' : <dt className="w-1/6 lg:w-1/12 overflow-x-hidden pr-3 lg:hidden">{daily.user.first_name} {daily.user.last_name.substr(0,1)}.</dt>}
                                    <dt className={`${colWidthSm} ${colWidthMd} ${cn({'lg:w-5/12':props.selectedUser[0]}, {'lg:w-4/12':!props.selectedUser[0]})} overflow-x-hidden hidden lg:block pr-3`}>{daily.daily_summary}</dt>
                                    <dt className={`${colWidthSm} ${colWidthMd} lg:w-1/12 overflow-x-hidden md:hidden text-center`}>{toHHMM(daily.time_worked_in_s - daily.time_break_in_s)}</dt>
                                    <dt className={`${colWidthSm} ${colWidthMd} lg:w-2/12 overflow-x-hidden hidden md:block text-center`}>{toHHMM(daily.time_worked_in_s)}</dt>
                                    <dt className={`${colWidthSm} ${colWidthMd} lg:w-2/12 overflow-x-hidden hidden md:block text-center`}>{toHHMM(daily.time_break_in_s)}</dt>
                                    <dt className={`${colWidthSm} ${colWidthMd} lg:w-1/12 overflow-x-hidden text-center`}>{format(new Date(daily.start_time), 'HH:mm')}</dt>
                                    <dt className={`${colWidthSm} ${colWidthMd} lg:w-1/12 overflow-x-hidden text-center`}>{format(new Date(daily.end_time), 'HH:mm')}</dt>
                                    <dt className={`w-10/12 js-daily-popup absolute border-2 border-teal-200 bg-white shadow-md p-3 rounded-md hidden`}>
                                        <div
                                            className="font-bold">{daily.user.first_name} {daily.user.last_name}</div>
                                        <div className="mt-3">{daily.daily_summary}</div>
                                    </dt>
                                </div>
                                )
                            })}
                        </dl>
                    </>
                }
                <div>
                    <Pagination
                        currentPage={currentPage}
                        totalPages={Math.ceil(typeof data === 'undefined' || typeof data['hydra:member'] === 'undefined' || data['hydra:member'].length === 0 ? 30 / 30 : data['hydra:totalItems'] / 30)}
                        path={'reporting'}
                        urlParams={`&user=${userQueryParam}`}
                    />
                </div>
            </div>
        </Layout>
    )
}

export const getServerSideProps: GetServerSideProps = async (context) => {
    const cookies = new Cookies(context.req.headers.cookie);
    let userQueryParam = null;
    let pageQueryParam = null;
    if (context.query.hasOwnProperty('user')) {
        userQueryParam = context.query.user;
    }

    if (context.query.hasOwnProperty('page')) {
        pageQueryParam = context.query.page;
    }

    let url = new URL(`${process.env.API_BASE_URL}/daily_summaries?order[date]`);
    if (pageQueryParam) {
        url.searchParams.append('page', pageQueryParam)
    }

    if (userQueryParam) {
        url.pathname = `${userQueryParam}/daily_summaries`;
    }

    const token = cookies.get('token');
    const dailies = await IsoFetcher.isofetchAuthed(url.href, 'GET', token);
    const users = await IsoFetcher.isofetchAuthed(`${process.env.API_BASE_URL}/users`, 'GET', token);
    let selectedUser = null;
    if (typeof users !== 'undefined' && users['hydra:member'] !== 'undefined') {
        selectedUser = users['hydra:member'].filter((user) => user['@id'] === userQueryParam);
    }
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
    return { props: { validToken, ApiBaseUrl: process.env.API_BASE_URL, selectedUser: selectedUser, initialData: { dailies, users } } };
};
export default Dashboard;
