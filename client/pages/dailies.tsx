import React, {useEffect, useState} from 'react';
import useSWR from "swr";
import { useRouter } from 'next/router';
import {Layout, DailyTablerow, DailyEditview, Pagination } from '../components';
import {useAuth, TokenService, FetcherFunc, IsoFetcher} from "../services";
import {GetServerSideProps} from "next";
import {IDaily, IDailiesApiResult} from "../types";
import Cookies from "universal-cookie";
import {differenceInSeconds, format} from "date-fns";
import {ITimer} from "../types/timer.types";
import {ITimerApiResult} from "../types/apiResult.types";
import {toHHMMSS} from "../utilities/lib";

export const Dailies = (props) => {
    const router = useRouter();
    const [auth, authDispatch] = useAuth();
    const [dailyToEdit, setDailyToEdit] = useState<IDaily|null>(null);
    const [isEditViewVisible, setIsEditViewVisible] = useState(false);
    const [pageIndex, setPageIndex] = useState(1);
    const url = `/daily_summaries?order[date]&page=${pageIndex}`;
    const today = format(new Date(), 'yyyy-MM-dd');
    const timerUrl = `/timers?date_start[after]=${today}&order[date_start]=asc`;
    const initialData = props.initialData;
    const { data, error, mutate: mutateDailies } = useSWR<IDailiesApiResult>([url, auth.jwt, 'GET'], FetcherFunc, {initialData})
    const { data: timers, error: timerErrors } = useSWR<ITimerApiResult>([timerUrl, auth.jwt, 'GET'], FetcherFunc, {initialData})

    useEffect(() => {
        if (data?.code === 401) {
            const tokenService = new TokenService();
            authDispatch({
                type: 'removeAuthDetails'
            });
            tokenService.deleteToken();
            router.push('/timers');
        }
    }, [data]);

    if (error) return <div>failed to load</div>
    if (!data) return <div>loading...</div>

    const toggleDailyEditView = () => {
        if (isEditViewVisible) {
            setDailyToEdit(null);
        }
        setIsEditViewVisible((prevVal) => {return !prevVal});
    }

    const addDaily = () => {
        setDailyToEdit(null);
        toggleDailyEditView();
    }

    const handleTableRowClick = daily => {
        setDailyToEdit(() => daily);
        toggleDailyEditView();
    }

    return (
        <Layout validToken={props.validToken}>
            <div className="mt-6 mb-24">
            {data?.['hydra:member']?.length > 0 ?
                <>
                    <div>
                        {isEditViewVisible ?
                            <button className={`inset-0 fixed w-full h-full cursor-default bg-black opacity-50`} onClick={() => setIsEditViewVisible(false)}/>
                            : ''
                        }

                        {data['hydra:member'].map((daily: IDaily) => (
                            <DailyTablerow
                                daily={daily}
                                onClick={daily => handleTableRowClick(daily)}
                            />
                        ))}
                    </div>
                    <div>
                        <Pagination
                            currentPage={pageIndex}
                            setPageIndex={setPageIndex}
                            totalPages={Math.ceil(data?.['hydra:member']?.length === 0 ? 1 : data['hydra:totalItems'] / 30)}
                            path="dailies"
                        />
                    </div>
                </>
                :
                <div>
                    <button className="fixed top-20 bottom-0 left-0 w-full h-full bg-black opacity-50"/>
                    <div className="fixed bottom-44 text-white text-lg right-6">Add a daily summary</div>
                    <img src="../images/icons/comic-arrow.svg" width="200" className="fixed bottom-24 right-4 animate-bounce-little"/>
                </div>
            }

                <DailyEditview
                    mutateDailies={mutateDailies}
                    toggleDailyEditView={toggleDailyEditView}
                    isEditViewVisible={isEditViewVisible}
                    dailyToEdit={dailyToEdit}
                    data={data}
                />
                <div
                    className="fixed bottom-0 mb-3 right-3 mr-3 bg-teal-500 rounded-full p-4 border-white border-2 outline-none shadow-md cursor-pointer"
                    onClick={() => addDaily()}>
                    <img src="../images/icons/icons8-plus-math-60.png" width="30" height="30" alt="Start Timer"/>
                </div>
            </div>
        </Layout>
    );
}

export const getServerSideProps: GetServerSideProps = async (context) => {
    const cookies = new Cookies(context.req.headers.cookie);
    const token = cookies.get('token');
    const timers = await IsoFetcher.isofetchAuthed(`${process.env.API_BASE_URL}/daily_summaries?order[date]&page=1`, 'GET', token);
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
    return { props: { validToken, initialData: timers } };
};

export default Dailies;
