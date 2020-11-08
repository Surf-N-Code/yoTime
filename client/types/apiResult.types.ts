import {ITimer} from './timer.types';

export interface IApiResult {
    '@context': string;
    '@id': string;
    '@type': string;
    'hydra:member': ITimer[];
    'hydra:search': object;
    'hydra:totalItems': number;
    code?: number;
}
