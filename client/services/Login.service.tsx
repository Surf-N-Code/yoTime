import {AuthorizationError} from '../errors';

const loginEndpint = 'https://localhost:8443/token';
export const LoginAsync = async (email, password) => {
    const response = await fetch(loginEndpint, {
        method: "POST",
        headers: {
            "Content-type": "application/json; charset=UTF-8",
        },
        body: JSON.stringify({email, password}),
    });

    if (!response.ok) {
        if (response.status === 401) {
            throw new AuthorizationError('Invalid credentials');
        } else {
            const message = `An error has occured: ${response.status}`;
            throw new Error(message);
        }
    }

    return await response.json();
};

export default LoginAsync;
