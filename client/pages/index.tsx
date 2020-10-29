import React from 'react';
import Layout from '../components/layout';
import { NextPageContext } from 'next';
import TokenService from "../services/Token.service";

export const Home = (props) => {
    return (
        <Layout>
            <h1>Home</h1>
        </Layout>
    )
}

Home.getInitialProps = async () => {
    const tokenService = new TokenService();
    console.log('awaited authenticateTokenSSR', await tokenService.authenticateTokenSsr());

    return {};
};

export default Home;
