import {ITimer} from './timer.types';
import {IDailySummary} from './dailySummary.types';
import {IUser} from "./user.types";

interface BaseApiResult {
    '@context': string;
    '@id': string;
    '@type': string;
    'hydra:search': object;
    'hydra:totalItems': number;
    code?: number;
}

export interface ITimerApiResult extends BaseApiResult {
    'hydra:member': ITimer[];
}

export interface IUserApiResult extends BaseApiResult {
    'hydra:member': IUser[];
}

export interface IDailiesApiResult extends BaseApiResult {
    'hydra:member': IDailySummary[];
}
