import React, {useState} from 'react';
import {Form, Formik, FormikActions, setIn, useField} from 'formik';
import {IRegisterIn} from "../types/auth.types";
import { useAuth } from '../services/Auth.context';
import { useGlobalMessaging } from '../services/GlobalMessaging.context';
import {useRouter} from "next/router";
import TokenService from "../services/Token.service";
import * as Yup from 'yup';
import cn from 'classnames';
import {Layout, FormField} from '../components';
import {LoginAsync} from "../services";
import {ConflictError} from "../errors";
import {AccountSettingsValidation} from "../Form";

const registerEndpint = 'https://localhost:8443/users';

const registerAsync = async (email, password, firstName, lastName) => {
    const tzOffset = new Date().getTimezoneOffset();
    const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    const fullName = firstName + ' ' + lastName;
    const response = await fetch(registerEndpint, {
        method: "POST",
        headers: {
            "Content-type": "application/json; charset=UTF-8",
        },
        body: JSON.stringify({email, password, tzOffset, timezone, fullName, isActive: true}),
    });

    if (!response.ok) {
        const message = `Ups... an error occured. Please try again.`;
        if (response.status === 409) {
            let res = await response.json();
            throw new ConflictError(res['hydra:description']);
        } else {
            throw new Error(message);
        }
    }

    return await response.json();
};

const Register = () => {
    const tokenService = new TokenService();
    const [messageState, messageDispatch] = useGlobalMessaging();
    const [authState, authDispatch] = useAuth();
    const router = useRouter();

    return (
        <Layout>
            <Formik
                initialValues={{
                    email: '',
                    password: '',
                    passwordConfirmation: '',
                    firstName: '',
                    lastName: ''
                }}
                validationSchema={AccountSettingsValidation}
                onSubmit={(values: IRegisterIn, { setSubmitting }: FormikActions<IRegisterIn>) => {
                    registerAsync(values.email, values.password, values.firstName, values.lastName)
                        .then((res: any) => {
                            console.log('values',values);
                            setSubmitting(false);
                            messageDispatch({
                                type: 'removeMessage',
                            });

                            LoginAsync(values.email, values.password)
                                .then((res: any) => {
                                    const tokenService = new TokenService();
                                    console.log('token after login in register', res);
                                    tokenService.saveToken(res.token);

                                    authDispatch({
                                        type: 'setAuthDetails',
                                        payload: {
                                            email: values.email,
                                            jwt: res.token
                                        }

                                    });
                                    setSubmitting(false);
                                    router.push('/')
                                })
                                .catch(error => {
                                    messageDispatch({
                                        type: 'setMessage',
                                        payload: {
                                            message: error.message
                                        }
                                    });
                                });
                    })
                    .catch(error => {
                        console.log(error);
                        let message = error.message;
                        if (error instanceof ConflictError) {
                            message = ''
                        }
                        console.log(error.message);
                        messageDispatch({
                            type: 'setMessage',
                            payload: {
                                message: error.message
                            }
                        });
                    });
                }}>

                {({values, isSubmitting}) => (
                    <Form>
                        <div className="container mx-auto">
                            <div className="flex justify-center px-6 my-12">
                                <div className="w-full xl:w-3/4 lg:w-11/12 flex">
                                    <div
                                        className="w-full h-auto bg-gray-400 hidden lg:block lg:w-5/12 bg-cover rounded-l-lg"
                                        style={{"backgroundImage": "url('https://source.unsplash.com/Mv9hjnEUHR4/600x800')"}}
                                    />
                                    <div className="w-full lg:w-7/12 bg-white p-5 rounded-lg lg:rounded-l-none">
                                        <h3 className="pt-4 text-2xl text-center">Create an Account!</h3>
                                        <div className="px-3 pt-6 pb-8 mb-4 bg-white rounded">
                                            <div className="mb-4 md:flex md:justify-between">
                                                <div className="mb-4 md:mr-2 md:mb-0">
                                                    <FormField label="First Name" id="firstname" name="firstName" type="text" placeholder="First Name" value={values.firstName}/>
                                                </div>
                                                <div className="md:ml-2">
                                                    <FormField label="Last Name" name="lastName" type="text" placeholder="Last Name" value={values.lastName}/>
                                                </div>
                                            </div>
                                            <div className="mb-4">
                                                <FormField label="Email" name="email" type="text" placeholder="john@doe.com" value={values.email}/>
                                            </div>
                                            <div className="mb-4 md:flex md:justify-between">
                                                <div className="mb-4 md:mr-2 md:mb-0">
                                                    <FormField label="Password" id="password" name="password" type="password" placeholder="**********" value={values.password}/>
                                                </div>
                                                <div className="md:ml-2">
                                                    <FormField label="Password Confirmation" id="password-repeat" name="passwordConfirmation" type="password" placeholder="**********"  value={values.passwordConfirmation}/>
                                                </div>
                                            </div>
                                            <div className="mb-6 text-center">
                                                <button
                                                    className="w-full mt-4 px-4 py-2 font-bold text-white bg-teal-500 rounded-full hover:bg-teal-400 focus:outline-none focus:shadow-outline"
                                                    type="submit"
                                                    disabled={isSubmitting}
                                                >{isSubmitting ? 'Loading ...' : 'Sign Up'}</button>
                                            </div>
                                            <hr className="mb-6 border-t" />
                                            <div className="text-center">
                                                <a
                                                    className="inline-block text-sm text-blue-500 align-baseline hover:text-blue-800"
                                                    href="#"
                                                >
                                                    Forgot Password?
                                                </a>
                                            </div>
                                            <div className="text-center">
                                                <a
                                                    className="inline-block text-sm text-blue-500 align-baseline hover:text-blue-800"
                                                    href="/"
                                                >
                                                    Already have an account? Login!
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

export default Register;
