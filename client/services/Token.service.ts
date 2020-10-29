import { NextPageContext } from 'next/types';

import Cookies from 'universal-cookie';
import {useRouter} from "next/router";

class TokenService {
  public saveToken(token: string) {
    const cookies = new Cookies();
    cookies.set('token', token, { path: '/' });
    return Promise.resolve();
  }

  public deleteToken() {
    const cookies = new Cookies();
    cookies.remove('token', { path: '/' });
    return;
  }

  public checkAuthToken(token: string): Boolean {
    // const loginAsync = async (token) => {
    //   const response = await fetch('authentication_validate', {
    //     method: "POST",
    //     headers: {
    //       "Content-type": "application/json; charset=UTF-8",
    //     },
    //     body: JSON.stringify({token}),
    //   });
    //
    //   if (!response.ok) {
    //     const message = `An error has occured: ${response.status}`;
    //     throw new Error(message);
    //   }
    //
    //   return await response;
    // };
    return true;
  }

  public async authenticateTokenSsr() {
    // const router = useRouter();
    const cookies = new Cookies(null);
    const token = cookies.get('token');
    console.log('token in cookie', token);

    const response = this.checkAuthToken(token);
    console.log('response',response)
    // if (!response.success) {
    //   this.deleteToken();
    //   router.push('/logout');
    // }
  }
}

export default TokenService;
