import React from 'react';
import Layout from '../components/layout';
// import { Loader } from '../components/loading';
import useSWR from 'swr';

export const Dashboard = () => {
    // const { data, error } = useSWR('https://localhost:8443/timers', fetch)
    // console.log(data.json());
    // if (error) return <div>failed to load</div>
    // if (!data) return <div>loading...</div>
    // return <div>hello {JSON.stringify(data)}!</div>


    // const url = 'https://localhost:8443/timers';
    // const fetcher = (...args) => fetch(...args).then(res => res.json());
    //
    // const { data, error } = useSWR(url, fetcher)
    //
    // console.log(data)
    //
    // if (error) return <div>failed to load</div>;
    // if (!data) return <div>loading...</div>;
    //
    // return (
    //     <Layout>
    //         <h1>Fetching data with SWR</h1>
    //         <div>
    //             {data['hydra:member'].map((value, key) => {
    //                 console.log(key);
    //                 console.log(value);
    //                 return (
    //                     <div key={value['@id']}>
    //                         <h2>{value.date_start}</h2>
    //                         <h2>{value.date_end}</h2>
    //                     </div>
    //                 )
    //             })}
    //         </div>
    //     </Layout>
    // );

    return (
        <Layout>
            <h1>Dashboard</h1>
        </Layout>
    )
}

export default Dashboard;
