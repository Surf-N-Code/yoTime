import React, {useEffect, useState} from 'react';
import useSWR, { mutate } from 'swr';
import {useRouter} from 'next/router';
import {differenceInDays, differenceInSeconds, format} from 'date-fns';
import isToday from 'date-fns/isToday';
import cn from 'classnames';
import {v4 as uuidv4} from 'uuid';
import {ActionAnimations, SwipeableList, SwipeableListItem} from '@sandstreamdev/react-swipeable-list';
import '@sandstreamdev/react-swipeable-list/dist/styles.css';
import { CSSTransition, TransitionGroup } from 'react-transition-group';
import { Transition } from 'react-transition-group';
import fetch from 'isomorphic-unfetch';
import {IsoFetcher} from '../services';

import {Alert, Layout, Pagination, ManualTimerview} from '../components';
import {FetcherFunc, TokenService, useAuth, useGlobalMessaging} from '../services';
import {ITimer, ITimerApiResult} from '../types';
import {toHHMMSS} from "../utilities";

import {GetServerSideProps} from "next";
import Cookies from "universal-cookie";
import {log} from "util";
import {sleep} from "../utilities/lib";

export const test = ({validToken, initialData}) => {
    const [auth, authDispatch] = useAuth();
    const url = `/timers?order[dateStart]&page=1`;
    const { data, error, isValidating, mutate } = useSWR<ITimerApiResult>([url, auth.jwt, 'GET'], FetcherFunc, {initialData})

    const startTimer = async (timerType: string) => {
        console.group('START TIMER')
        let tempId = uuidv4();
        const newTimer = {
            date_start: new Date(),
            date_end: null,
            timer_type: timerType
        }

        await mutate((data) => {
            let newHydra = [{id: tempId, ...newTimer}, ...data['hydra:member']].sort((a, b) => new Date(b.date_start).getTime() - new Date(a.date_start).getTime());
            return {
                ...data,
                'hydra:member': [ ... newHydra ]
            }
        }, false);

        const fetchedTimer = await FetcherFunc('timers', auth.jwt, 'POST', newTimer);

        await mutate((data) => {
            return {
                ...data,
                'hydra:member': data['hydra:member'].map((timer) =>
                    timer.id === tempId ? fetchedTimer : timer
                )
            }
        }, false);
        // console.log('resulting timer', resultTimer);
        // setRunningTimer(resultTimer);
        //
        // console.log('----', data['hydra:member']);
        // console.log('temp id', tempId)
        console.groupEnd()
    }

    const hasTimer = typeof data !== 'undefined' && typeof data['hydra:member'] !== 'undefined' && data['hydra:member'].length !== 0;

    return (
        <Layout validToken={validToken}>
            <div className="mt-6 mb-24">
            {error || (data && data['@type'] === 'hydra:Error')? <div>Ups... there was an error fetching your timers</div> :
                <>
                    { hasTimer ?
                        data['hydra:member'].map((timer: ITimer) => {
                            return (
                                <div key={timer.id}>
                                    { timer.id }
                                </div>
                            )
                        })
                        :
                        ''
                    }
                    <div
                        onClick={() => startTimer('break')}
                    >START</div>
                </>
            }
            </div>
        </Layout>
    );
}

export default test;
