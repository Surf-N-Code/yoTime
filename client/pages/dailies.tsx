import React, {useEffect, useState} from 'react';
import useSWR from "swr";
import { useRouter } from 'next/router';
import {Layout, DailyTablerow, DailyEditview, Pagination } from '../components';
import {useAuth, TokenService, FetcherFunc, IsoFetcher} from "../services";
import {GetServerSideProps} from "next";
import {IDaily, IDailiesApiResult} from "../types";
import Cookies from "universal-cookie";

export const Dailies = (props) => {
    const router = useRouter();
    const [auth, authDispatch] = useAuth();
    const [dailyToEdit, setDailyToEdit] = useState<IDaily|null>(null);
    const [isEditViewVisible, setIsEditViewVisible] = useState(false);
    const currentPage = Number(typeof router.query.page !== 'undefined' ? router.query.page : 1);
    const url = `/daily_summaries?order[date]&page=${currentPage}`;
    const initialData = props.initialData;
    const { data, error, mutate: mutateDailies } = useSWR<IDailiesApiResult>([url, auth.jwt, 'GET'], FetcherFunc, {initialData})

    useEffect(() => {
        if (data && typeof data.code !== 'undefined' && data.code === 401) {
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
        console.log('editing daily', daily)
        setDailyToEdit(() => daily);
        console.log('dialy to edit', dailyToEdit);
        toggleDailyEditView();
    }

    return (
        <Layout validToken={props.validToken}>
            <div className="mt-6 mb-24">
            {typeof data['hydra:member'] === 'undefined' || data['hydra:member'].length === 0 ? <div>no data yet...</div> :
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
                            currentPage={currentPage}
                            totalPages={Math.ceil(data['hydra:totalItems'] / 30)}
                        />
                    </div>
                </>
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
    const timers = await IsoFetcher.isofetchAuthed(`${process.env.API_BASE_URL}daily_summaries?order[date]&page=1`, null, 'GET', token);
    const tokenService = new TokenService();
    const validToken = await tokenService.authenticateTokenSsr(context)
    if (!validToken) {
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
