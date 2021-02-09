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
    console.log('url', url.pathname+url.search);

    // const initialData = props.initialData.dailies;
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

    const { data, error } = ReportingData(pageIndex, sortOption, sortAscending, selectedUser, props.initialData.dailies);
    ReportingData(pageIndex+1, sortOption, sortAscending, selectedUser, props.initialData.dailies);

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

    const selectSortOption = (option) => {
        console.log('set sortoption to : ', option);
        setSortOption(option);
        setIsSortingSelectVisible((prevVal) => !prevVal);
        console.log('State sortoption', sortOption)
    }

    if (error) return <div>failed to load</div>
    if (!data) return <div>loading...</div>

    const setUserFilter = (user: IUser) => {
        console.log('set user filter to:', user);
        setIsUserSelectVisible(prev => !prev);
        setSelectedUser((prevUser) => {
            if (prevUser?.["@id"] === user["@id"]) {
                console.log('delete selected user');
                return null;
            }
            console.log('set selected user id to:', user["@id"]);
            return user;
        });
        console.log(selectedUser);
    }

    const setSortAscending_ = () => {
        console.log('sort ascending', sortAscending);
        setSortAscending((prevVal) => !prevVal);
    }

    return (
        <Layout validToken={props.validToken}>
            <div className="mt-6 mb-24 z-10">
                <div onClick={() => setSortAscending((prevVal) => !prevVal)}>button</div>
                {
                    data['hydra:member'].map((daily: IDaily, i) => {
                        return (<div>{daily.date +'___'+ daily.user.first_name}</div>)
                    })
                }

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
    // let userQueryParam = null;
    // let pageQueryParam = null;
    // if (context.query.hasOwnProperty('user')) {
    //     userQueryParam = context.query.user;
    // }
    //
    // if (context.query.hasOwnProperty('page')) {
    //     pageQueryParam = context.query.page;
    // }

    let url = new URL(`${process.env.API_BASE_URL}/daily_summaries?order[date]=desc`);
    // if (pageQueryParam) {
    //     url.searchParams.append('page', pageQueryParam)
    // }
    //
    // if (userQueryParam) {
    //     url.pathname = `${userQueryParam}/daily_summaries`;
    // }

    const token = cookies.get('token');
    const dailies = await IsoFetcher.isofetchAuthed(url.href, 'GET', token);
    const users = await IsoFetcher.isofetchAuthed(`${process.env.API_BASE_URL}/users`, 'GET', token);
    // let selectedUser = null;
    // if (typeof users !== 'undefined' && users['hydra:member'] !== 'undefined') {
    //     let user = users['hydra:member'].filter((user) => user['@id'] === userQueryParam);
    //     if (user.length > 0) {
    //         selectedUser = user[0];
    //     }
    // }
    //
    // if (typeof users !== 'undefined' && users['hydra:totalItems'] === 1) {
    //     selectedUser = users['hydra:member'][0];
    // }

    return { props: { validToken, ApiBaseUrl: process.env.API_BASE_URL, initialData: dailies } };
};
export default Reporting;
