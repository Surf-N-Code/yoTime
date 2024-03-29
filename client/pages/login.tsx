import React, {useState} from 'react';
import {Field, Form, Formik, FormikActions, setIn} from 'formik';
import {useRouter} from "next/router";
import cn from 'classnames';
import {Layout} from "../components";
import {LoginAsync, TokenService, useGlobalMessaging, useAuth} from "../services";
import {ILoginIn} from "../types";
import {AuthorizationError} from "../errors";

export const Login = () => {
    const tokenService = new TokenService();
    const [messageState, messageDispatch] = useGlobalMessaging();
    const [authState, authDispatch] = useAuth();
    const router = useRouter();
    const [invalidForm, setInvalidForm] = useState(false);

    return (
        <Layout>
            <Formik
                initialValues={{
                    email: '',
                    password: ''
                }}
                onSubmit={(values: ILoginIn, { setSubmitting }: FormikActions<ILoginIn>) => {
                    LoginAsync(values.email, values.password)
                        .then((res: any) => {
                            setSubmitting(false);
                            const tokenService = new TokenService();
                            console.log(res);
                            tokenService.saveToken(res.token);

                            authDispatch({
                                type: 'setAuthDetails',

                                payload: {
                                    email: values.email,
                                    jwt: res.token
                                }

                            });
                            setInvalidForm(false);
                            router.push('/dashboard')
                        })
                        .catch(error => {
                            if (!(error instanceof AuthorizationError)) {
                                messageDispatch({
                                    type: 'setMessage',
                                    payload: {
                                        message: error.message
                                    }
                                });
                            }
                            setInvalidForm(true);
                        });
                }}>

                {({ isSubmitting }) => (
                    <Form>
                        <div className="container mx-auto">
                            <div className="flex justify-center px-6 my-12">
                                <div className="w-full xl:w-3/4 lg:w-11/12 flex">
                                    <div
                                        className="w-full h-auto bg-gray-400 hidden lg:block lg:w-1/2 bg-cover rounded-l-lg"
                                        style={{"backgroundImage": "url('https://source.unsplash.com/K4mSJ7kc0As/600x800')"}}
                                    />
                                    <div className="w-full lg:w-1/2 bg-white p-5 rounded-lg lg:rounded-l-none">
                                        <h3 className="pt-4 text-2xl text-center">Welcome Back!</h3>
                                        <div className="px-3 pt-6 pb-8 mb-4 bg-white rounded">
                                            <div className="mb-4">
                                                <Field id="email" name="email" placeholder="Email" type="text"
                                                       className={`w-full px-3 py-2 text-sm leading-tight text-gray-700 border ${cn({'border-red-500' : invalidForm})} rounded shadow appearance-none focus:outline-none focus:shadow-outline`}
                                                       onSelect={() => setInvalidForm(false)}
                                                />
                                            </div>
                                            <div className="mb-4">
                                                <Field id="password" name="password" placeholder="Password" type="password"
                                                       className={`w-full px-3 py-2 mb-3 text-sm leading-tight text-gray-700 border ${cn({'border-red-500' : invalidForm})} rounded shadow appearance-none focus:outline-none focus:shadow-outline`}
                                                       onSelect={() => setInvalidForm(false)}
                                                />
                                                {invalidForm ? <p className="text-xs italic text-red-500">Email or password incorrect.</p> : ''}
                                            </div>
                                            <div className="mb-6 text-center">
                                                <button
                                                    className="w-full px-4 py-2 font-bold text-white bg-teal-500 rounded-lg hover:bg-teal-700 focus:outline-none focus:shadow-outline"
                                                    type="submit"
                                                >
                                                    Sign In
                                                </button>
                                            </div>
                                            <hr className="mb-6 border-t"/>
                                            {/*<div className="text-center">*/}
                                            {/*    <a*/}
                                            {/*        className="inline-block text-sm text-blue-500 align-baseline hover:text-blue-800"*/}
                                            {/*        href="./register"*/}
                                            {/*    >*/}
                                            {/*        Create an Account!*/}
                                            {/*    </a>*/}
                                            {/*</div>*/}
                                            <div className="text-center">
                                                <a href="./reset-password"
                                                   className="inline-block text-sm text-blue-500 align-baseline hover:text-blue-900">
                                                    Forgot Password?
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Form>
                )}
            </Formik>
        </Layout>
    )
}

export default Login;
