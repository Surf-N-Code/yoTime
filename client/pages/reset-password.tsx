import React, {useState} from 'react';
import {Form, Formik, FormikActions, setIn, useField} from 'formik';
import {IRegisterIn, IResetPassword} from "../types/auth.types";
import { useAuth } from '../services/Auth.context';
import { useGlobalMessaging } from '../services/GlobalMessaging.context';
import {useRouter} from "next/router";
import TokenService from "../services/Token.service";
import * as Yup from 'yup';
import cn from 'classnames';
import {Layout, FormField} from '../components';
import {LoginAsync} from "../services";
import {ConflictError} from "../errors";
import {ResetPasswordValidation} from "../Form";

const resetEndpoint = 'https://localhost:8443/forgot-password';

const resetPasswordAsync = async (email) => {
    console.log(email);
    const response = await fetch(resetEndpoint, {
        method: "POST",
        headers: {
            "Content-type": "application/json; charset=UTF-8",
        },
        body: JSON.stringify({email}),
    });

    console.log(response);
    if (!response.ok) {
        const message = `Ups... an error occured. Please try again.`;
        throw new Error(message);
    }
    return await response.json();
};

const ResetPassword = () => {
    const [messageState, messageDispatch] = useGlobalMessaging();

    console.log('hier');
    return (
        <Layout>
            <Formik
                initialValues={{
                    email: '',
                }}
                validationSchema={ForgotPasswordValidation}
                onSubmit={(values: IResetPassword, { setSubmitting }: FormikActions<IRegisterIn>) => {
                    console.log('submit');
                    resetPasswordAsync(values.email)
                        .then((res: any) => {
                            console.log('values',values);
                            setSubmitting(false);
                            messageDispatch({
                                type: 'removeMessage',
                            });
                            messageDispatch({
                                type: 'setMessage',
                                payload: {
                                    message: 'If you have an account with YoTime, we will send you an E-Mail to reset your password.'
                                }
                            });
                    })
                    .catch(error => {
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
                                        <h3 className="pt-4 text-2xl text-center">Reset your password!</h3>
                                        <div className="px-3 pt-6 pb-4 mb-4 bg-white rounded">
                                            <div className="mb-4">
                                                <FormField label="Email" name="email" type="text" placeholder="john@doe.com" value={values.email}/>
                                            </div>

                                            <div className="text-center">
                                                <button
                                                    className="w-full mt-4 px-4 py-2 font-bold text-white bg-teal-500 rounded-full hover:bg-teal-400 focus:outline-none focus:shadow-outline"
                                                    type="submit"
                                                    disabled={isSubmitting}
                                                >{isSubmitting ? 'Loading ...' : 'Reset Password'}</button>
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

export default ResetPassword;
