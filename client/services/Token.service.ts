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

  public checkAuthToken(token: string): Promise<any> {
    const loginAsync = async (token) => {
      const response = await fetch('authentication_validate', {
        method: "POST",
        headers: {
          "Content-type": "application/json; charset=UTF-8",
        },
        body: JSON.stringify({token}),
      });

      if (!response.ok) {
        const message = `An error has occured: ${response.status}`;
        throw new Error(message);
      }

      return await response;
    };
  }

  public async authenticateTokenSsr(ctx: NextPageContext) {
    // const router = useRouter();
    console.log(ctx);
    // const cookies = new Cookies(ctx.req.headers.cookie);
    // const token = cookies.get('token');
    // console.log(ctx);

    // const response = await this.checkAuthToken(token);
    // console.log('response',response)
    // if (!response.success) {
    //   this.deleteToken();
    //   router.push('/logout');
    // }
  }
}

export default TokenService;
