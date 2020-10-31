import React, {useState} from 'react';
import {Field, Form, Formik, FormikActions, setIn} from 'formik';
import {ILoginIn} from "../types/auth.types";
import { useAuth } from '../services/Auth.context';
import { useGlobalMessaging } from '../services/GlobalMessaging.context';
import {useRouter} from "next/router";
import cn from 'classnames';
import TokenService from "../services/Token.service";

const loginEndpint = 'https://localhost:8443/token';
const loginAsync = async (email, password) => {
    const response = await fetch(loginEndpint, {
        method: "POST",
        headers: {
            "Content-type": "application/json; charset=UTF-8",
        },
        body: JSON.stringify({email, password}),
    });

    if (!response.ok) {
        const message = `An error has occured: ${response.status}`;
        throw new Error(message);
    }

    return await response.json();
};

export const Login = () => {
    const tokenService = new TokenService();
    const [messageState, messageDispatch] = useGlobalMessaging();
    const [authState, authDispatch] = useAuth();
    const router = useRouter();
    const [invalidForm, setInvalidForm] = useState(false);

    return (
        <Formik
            initialValues={{
                email: '',
                password: ''
            }}
            onSubmit={(values: ILoginIn, { setSubmitting }: FormikActions<ILoginIn>) => {
                loginAsync(values.email, values.password)
                    .then((res: any) => {
                        setSubmitting(false);
                        const tokenService = new TokenService();
                        console.log(res);
                        tokenService.saveToken(res.token);

                        authDispatch({
                            type: 'setAuthDetails',

                            payload: {
                                email: values.email
                            }
                        });
                        setInvalidForm(false);
                        router.push('/')
                })
                .catch(error => {
                    messageDispatch({
                        type: 'setMessage',
                        payload: {
                            message: error.message
                        }
                    });
                    setInvalidForm(true);
                });
            }}
            render={() => (
                <Form>
                    <div className="container mx-auto">
                        <div className="flex justify-center px-6 my-12">
                            <div className="w-full xl:w-3/4 lg:w-11/12 flex">
                                <div
                                    className="w-full h-auto bg-gray-400 hidden lg:block lg:w-1/2 bg-cover rounded-l-lg"
                                    style={{ "backgroundImage": "url('https://source.unsplash.com/K4mSJ7kc0As/600x800')" }}
                                ></div>
                                <div className="w-full lg:w-1/2 bg-white p-5 rounded-lg lg:rounded-l-none">
                                    <h3 className="pt-4 text-2xl text-center">Welcome Back!</h3>
                                    <form className="px-8 pt-6 pb-8 mb-4 bg-white rounded">
                                        <div className="mb-4">
                                            <label className="block mb-2 text-sm font-bold text-gray-700" htmlFor="username">
                                                Email
                                            </label>
                                            <Field id="email" name="email" placeholder="Email" type="text"
                                                   className={`w-full px-3 py-2 text-sm leading-tight text-gray-700 border ${cn({'border-red-500' : invalidForm})} rounded shadow appearance-none focus:outline-none focus:shadow-outline`}
                                                   onSelect={() => setInvalidForm(false)}
                                            />
                                        </div>
                                        <div className="mb-4">
                                            <label className="block mb-2 text-sm font-bold text-gray-700" htmlFor="password">
                                                Password
                                            </label>
                                            <Field id="password" name="password" placeholder="Password" type="password"
                                                   className={`w-full px-3 py-2 mb-3 text-sm leading-tight text-gray-700 border ${cn({'border-red-500' : invalidForm})} rounded shadow appearance-none focus:outline-none focus:shadow-outline`}
                                                   onSelect={() => setInvalidForm(false)}
                                            />
                                            {invalidForm ? <p className="text-xs italic text-red-500">Email or password incorrect.</p> : ''}
                                        </div>
                                        <div className="mb-6 text-center">
                                            <button
                                                className="w-full px-4 py-2 font-bold text-white bg-blue-500 rounded-full hover:bg-blue-700 focus:outline-none focus:shadow-outline"
                                                type="submit"
                                            >
                                                Sign In
                                            </button>
                                        </div>
                                        <hr className="mb-6 border-t"/>
                                        <div className="text-center">
                                            <a
                                                className="inline-block text-sm text-blue-500 align-baseline hover:text-blue-800"
                                                href="./register"
                                            >
                                                Create an Account!
                                            </a>
                                        </div>
                                        <div className="text-center">
                                            <button
                                                className="inline-block text-sm text-blue-500 align-baseline hover:text-blue-800"
                                                type="submit"
                                            >
                                                Forgot Password?
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </Form>
            )}
        />
    )
}
