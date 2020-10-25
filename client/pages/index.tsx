import React from 'react';
import Layout from '../components/layout';
import { NextPageContext } from 'next';
import TokenService from "../services/Token.service";
import {useAuth} from "../services/Auth.context";

export const Home = (props) => {
    console.log('home props',props);
    return (
        <Layout>
            <h1>Home</h1>
        </Layout>
    )
}

Home.getInitialProps = async (ctx: NextPageContext) => {
    console.log('in initial props', ctx)
    const tokenService = new TokenService();
    await tokenService.authenticateTokenSsr(ctx);

    return {};
};

export default Home;
