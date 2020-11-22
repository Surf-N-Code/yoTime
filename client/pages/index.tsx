import React from 'react';
import {Layout} from '../components';
import {TokenService} from "../services";
import {GetServerSideProps} from "next";

export const Home = ({validToken}) => {
    return (
        <Layout validToken={validToken}>
            <h1>Home</h1>
        </Layout>
    )
}

export default Home;

export const getServerSideProps: GetServerSideProps = (async (context) => {
    const tokenService = new TokenService();
    const validToken = await tokenService.authenticateTokenSsr(context)
    return { props: { validToken } };
});
