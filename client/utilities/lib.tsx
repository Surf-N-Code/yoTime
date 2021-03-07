import React from 'react';

export const toHHMMSS = (seconds: number) => {
    let h   = Math.floor(seconds / 3600)
    let m = Math.floor(seconds / 60) % 60
    let s = seconds % 60

    return [h,m,s]
        .map(v => v < 10 ? "0" + v : v)
        .join(":")
}

export const toHHMM = (seconds: number) => {
    let h   = Math.floor(seconds / 3600)
    let m = Math.floor(seconds / 60) % 60
    let s = seconds % 60

    return [h,m]
        .map(v => v < 10 ? "0" + v : v)
        .join(":")
}

export const sleep = (milliseconds: number) => {
    return new Promise(res => setTimeout(res, milliseconds))
}

export const getUniqueValuesForProperty = (array, uniqueProperty) => {
    let unique = [];
    let distinct = [];
    for( let i = 0; i < array.length; i++ ){
        if(!unique[array[i][uniqueProperty]]){
            distinct.push(array[i][uniqueProperty].substr(0,10));
            unique[array[i][uniqueProperty]] = 1;
        }
    }
    return distinct;
}

export const utcDate = (dateString) => {
    let userDate = new Date(dateString);
    return new Date(Date.UTC(userDate.getUTCFullYear(), userDate.getUTCMonth(), userDate.getUTCDate(), userDate.getUTCHours(), userDate.getUTCMinutes(), userDate.getUTCSeconds()));
}
