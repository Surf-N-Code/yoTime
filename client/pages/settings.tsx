import React, {useEffect, useState} from 'react';
import {Layout, FormField} from '../components';
import {GetServerSideProps} from "next";
import {TokenService} from "../services";
import {Form, Formik, FormikActions} from "formik";
import * as Yup from 'yup';
import {AccountSettingsValidation} from '../Form'
import {IUserSettings, IUserApiResult} from "../types";
import useSWR from "swr";
import {useAuth, FetcherFunc, LoginAsync, useGlobalMessaging} from "../services";
import {useRouter} from "next/router";

interface IUserSettingsUpdate {
    firstName?: string;
    lastName?: string;
    oldPassword?: string;
    password?: string;
    email?: string;
}

export const Settings = (props) => {
    const [auth, authDispatch] = useAuth();
    const [messageState, messageDispatch] = useGlobalMessaging();
    const { data, error } = useSWR<IUserApiResult>(['/users', auth.jwt, 'GET'], FetcherFunc)
    const [formError, setFormError] = useState(false);
    const [currentUser, setCurrentUser] = useState<IUserApiResult>(null);
    const router = useRouter();

    useEffect(() => {
        if (!data || typeof data["hydra:member"] === 'undefined') return;
        console.log(data["hydra:member"][0]);
        setCurrentUser(data["hydra:member"][0])
    }, [data])

    useEffect(() => {
        console.log(currentUser);
    }, [currentUser])

    useEffect(() => {
        console.log('data', data);
        if (data && typeof data.code !== 'undefined' && data.code === 401) {
            const tokenService = new TokenService();
            authDispatch({
                type: 'removeAuthDetails'
            });
            tokenService.deleteToken();
            router.push('/timers');
        }
    }, [data]);

    return (
        <Layout validToken={props.validToken}>
            {error || (data && data['@type'] === 'hydra:Error')? <div>Ups... there was an error fetching your timers</div> :
                typeof data === 'undefined' || typeof data['hydra:member'] === 'undefined' || data['hydra:member'].length === 0 ? <div>You have no timers yet</div> :
            <Formik
                initialValues={{
                    firstName: data["hydra:member"][0].first_name || '',
                    lastName: data["hydra:member"][0].last_name || '',
                    oldPassword: '',
                    password: '',
                    email: data["hydra:member"][0].email || ''
                }}

                validationSchema={Yup.object().shape({
                    firstName: Yup.string()
                        .min(3, 'Must be at least 3 characters')
                        .max(15, 'Must be 15 characters or lesse'),
                    lastName: Yup.string()
                        .min(3, 'Must be at least 3 characters')
                        .max(15, 'Must be 15 characters or lesse'),
                    email: Yup.string().notRequired()
                        .email('Invalid email'),
                    oldPassword: Yup.string().notRequired(),
                    password: Yup.mixed()
                        .notRequired()
                })}

                onSubmit={ async (values: IUserSettings, { setSubmitting }: FormikActions<IUserSettings>) => {
                    console.log(values);
                    let dataToUpdate: IUserSettingsUpdate = {};
                    for (let prop in values) {
                        if (values[prop] !== '') {
                            dataToUpdate[prop] = values[prop];
                        }
                    }

                    console.log('data to update', dataToUpdate);
                    let invalidMessage = ''
                    if (dataToUpdate.hasOwnProperty('password') && !dataToUpdate.hasOwnProperty('oldPassword')) {
                        invalidMessage = 'In order to change your password, you need to provide your current one.'
                        setFormError(true);
                    }

                    if (dataToUpdate.hasOwnProperty('password') && dataToUpdate.hasOwnProperty('oldPassword') && dataToUpdate.oldPassword === dataToUpdate.password) {
                        invalidMessage = 'The provided passwords are identical'
                        setFormError(true);
                    }

                    if (dataToUpdate.hasOwnProperty('password') && dataToUpdate.hasOwnProperty('oldPassword')) {
                        console.log('should login')
                        await LoginAsync(dataToUpdate.email, dataToUpdate.oldPassword)
                            .then(() => {
                                console.log('valid login')
                                setFormError(false);
                            })
                            .catch(error => {
                                console.log('error loggin in')
                                invalidMessage = 'Your current password is incorrect'
                                setFormError(true);
                            });
                    }

                    console.log('outside invalid', invalidMessage);
                    if (invalidMessage !== '') {
                        console.log('invalid message: ', invalidMessage);
                        messageDispatch({
                            type: 'setMessage',
                            payload: {
                                message: invalidMessage
                            }
                        });
                    }

                    if (!formError) {
                        let res = await FetcherFunc(`${currentUser["@id"]}`, auth.jwt, 'PATCH', dataToUpdate, 'application/merge-patch+json');
                        if (dataToUpdate.hasOwnProperty('password') || dataToUpdate.hasOwnProperty('email')) {
                            await LoginAsync(dataToUpdate.email, dataToUpdate.password)
                                .then(() => {
                                    setFormError(false);
                                })
                                .catch(error => {
                                    messageDispatch({
                                        type: 'setMessage',
                                        payload: {
                                            message: 'Ups, an error occurred verifying your new email or password. Please try again.'
                                        }
                                    });
                                });
                        }

                        values.oldPassword = '';
                        values.password = '';
                    }
                    console.log(values);
                }}
            >
                {({values, isSubmitting}) => (
                    //Enable auto saving - https://github.com/formium/formik/issues/172#issuecomment-500162524
                    <Form className="my-12">
                        <div className="bg-white shadow overflow-hidden sm:rounded-lg">
                            <div className="px-4 py-5 sm:px-6">
                                <h3 className="text-lg leading-6 font-medium text-gray-900">
                                    Personal Settings
                                </h3>
                            </div>
                            <div className="border-t border-gray-200">
                                <dl>
                                    <div className="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt className="text-sm font-medium text-gray-500">
                                            First name
                                        </dt>
                                        <dd className="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                            <FormField label={false} name="firstName" type="text" placeholder="Norman" value={values.firstName}/>
                                        </dd>
                                    </div>
                                    <div className="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt className="text-sm font-medium text-gray-500">
                                            Last name
                                        </dt>
                                        <dd className="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                            <FormField label={false} name="lastName" type="text" placeholder="Norman" value={values.lastName}/>
                                        </dd>
                                    </div>
                                    {/*<div className="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">*/}
                                    {/*    <dt className="text-sm font-medium text-gray-500">*/}
                                    {/*        Email*/}
                                    {/*    </dt>*/}
                                    {/*    <dd className="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">*/}
                                    {/*        <FormField label={false} name="email" type="text" placeholder="ndilthey@gmail.om" value={values.email}/>*/}
                                    {/*    </dd>*/}
                                    {/*</div>*/}
                                    <div className="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt className="text-sm font-medium text-gray-500">
                                            Current password
                                        </dt>
                                        <dd className="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                            <FormField label={false} name="oldPassword" type="password" placeholder="********"/>
                                        </dd>
                                    </div>
                                    <div className="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt className="text-sm font-medium text-gray-500">
                                            New password
                                        </dt>
                                        <dd className="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                            <FormField label={false} name="password" type="password" placeholder="********"/>
                                        </dd>
                                    </div>
                                    <div className="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <button
                                            className="w-full mt-4 px-4 py-2 font-bold text-white bg-blue-500 rounded-full hover:bg-blue-700 focus:outline-none focus:shadow-outline"
                                            type="submit"
                                            disabled={isSubmitting}
                                        >{isSubmitting ? 'Loading ...' : 'Update'}</button>
                                    </div>

                                </dl>
                            </div>
                        </div>
                    </Form>
                )}
            </Formik>
            }
        </Layout>
    )
}

export default Settings;

export const getServerSideProps: GetServerSideProps = (async (context) => {
    const tokenService = new TokenService();
    const validToken = await tokenService.authenticateTokenSsr(context)
    if (!validToken) {
        return {
            redirect: {
                permanent: false,
                destination: '/login',
            },
        }
    }
    return { props: { validToken } };
});
