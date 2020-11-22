import React, {useState} from 'react';
import { useAuth, TokenService } from '../services';
import {useRouter} from "next/router";

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
