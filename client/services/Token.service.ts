import {GetServerSidePropsContext, NextApiRequest, NextApiResponse, NextPageContext} from 'next/types';
import {FetcherFunc} from '../services';
import Cookies from 'universal-cookie';
import fetch from 'isomorphic-unfetch';

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

  public handleErrors(response: string): string {
    if (response === 'TypeError: Failed to fetch') {
      throw Error('Server error.');
    }
    return response;
  }

  public async checkAuthToken(token: string): Promise<any> {
    // return fetch(
    //     'https://localhost:8443/users',
    //     {
    //       headers: {
    //         Accept: 'application/json',
    //         'Content-Type': 'application/json',
    //         Authorization: 'Bearer ' + token
    //       },
    //       method: 'GET'
    //     }
    // )
    //     .then((response: Response) => {
    //       const res = response.json();
    //       console.log(res);
    //     })
    //     .then(this.handleErrors)
    //     .catch((error) => {
    //       throw error;
    //     });
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
    return typeof token !== 'undefined';
  }

  public getTokenFromCookie() {

  }

  public async authenticateTokenSsr(ctx: GetServerSidePropsContext) {
    const ssr = !!ctx.req;
    // console.log('context in auth',ctx);
    const cookies = new Cookies(ssr ? ctx.req.headers.cookie : null);
    const token = cookies.get('token');
    console.log("token from cookie", token);
    return this.checkAuthToken(token);
  }
}

export default TokenService;
