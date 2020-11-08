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

export const waitForSyncedTimer = async (condition, time) => {
    return await new Promise(resolve => {
        const interval = setInterval(() => {
            if (condition) {
                resolve();
                clearInterval(interval);
            }
        }, time);
    });
}
