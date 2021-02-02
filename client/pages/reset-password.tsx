import React, {useState} from 'react';
import {Form, Formik, FormikActions, setIn, useField} from 'formik';
import {IRegisterIn, IResetPassword} from "../types/auth.types";
import { useGlobalMessaging } from '../services/GlobalMessaging.context';
import {Layout, FormField} from '../components';
import {ResetPasswordValidation} from "../Form";

const resetEndpoint = 'https://localhost:8443/reset-password';

const resetPasswordAsync = async (email) => {
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

    return (
        <Layout>
            <Formik
                initialValues={{
                    email: '',
                }}
                validationSchema={ResetPasswordValidation}
                onSubmit={(values: IResetPassword, { setSubmitting }: FormikActions<IRegisterIn>) => {
                    resetPasswordAsync(values.email)
                        .then((res: any) => {
                            setSubmitting(false);
                            messageDispatch({
                                type: 'removeMessage',
                            });
                            messageDispatch({
                                type: 'setMessage',
                                payload: {
                                    severity: 'info',
                                    message: 'If you have an account with YoTime, we will send you an E-Mail to reset your password.'
                                }
                            });
                    })
                    .catch(error => {
                        setSubmitting(false);
                        messageDispatch({
                            type: 'setMessage',
                            payload: {
                                severity: 'error',
                                message: error.message
                            }
                        });
                    });
                }}>

                {({values, isSubmitting}) => (
                    <Form>
                        <div className="container mx-auto">
                            <div className="flex justify-center px-6 my-12">
                                <div className="w-full lg:w-4/12 bg-white p-5 rounded-lg">
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
                    </Form>
                )}
            </Formik>
        </Layout>
    )
}

export default ResetPassword;
