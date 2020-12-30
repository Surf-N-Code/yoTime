import {GetServerSidePropsContext, NextApiRequest, NextApiResponse, NextPageContext} from 'next/types';
import {FetcherFunc, IsoFetcher} from '../services';
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
    const res = await IsoFetcher.isofetchAuthed(`${process.env.API_BASE_URL}/users`, 'GET', token);
    return typeof res['hydra:member'] !== 'undefined'
  }

  public getTokenFromCookie() {

  }

  public async authenticateTokenSsr(ctx: GetServerSidePropsContext) {
    const ssr = !!ctx.req;
    // console.log('context in auth',ctx);
    const cookies = new Cookies(ssr ? ctx.req.headers.cookie : null);
    const token = cookies.get('token');
    console.log("token from cookie", token);
    console.log('is valid token', await this.checkAuthToken(token));
    return this.checkAuthToken(token);
  }
}

export default TokenService;
