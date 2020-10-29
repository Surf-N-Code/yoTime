import React, {useState} from 'react';
import { useAuth } from '../services/Auth.context';
import {useRouter} from "next/router";
import TokenService from "../services/Token.service";

export const Logout = () => {
    const tokenService = new TokenService();
    const [authState, authDispatch] = useAuth();
    const router = useRouter();

    React.useEffect(() => {
            authDispatch({
                type: 'removeAuthDetails'
            });

            tokenService.deleteToken();

            router.push('/');
    }, []);

    return (React.Fragment);
}

export default Logout;
