import Head from 'next/head';
import React, {Component, useState} from "react";
import Link from 'next/link';
import { Menu } from './Menu';
import {useAuth} from "../services/Auth.context";

const siteTitle = 'YoTime - your timetracker for Slack'

export const Layout = (props) => {
    const [auth, authDispatch] = useAuth();
    const [menuIsOpen, setMenuIsOpen] = useState(false);

    return (
        <div className="w-full h-full flex-1">
            <Head>
                {/*<link rel="icon" href="/favicon.ico" />*/}
                <meta name="description"
                    content="Timetracking with ease - YoTime"
                />
                {/*<meta*/}
                {/*    property="og:image"*/}
                {/*    content={`https://og-image.now.sh/${encodeURI(*/}
                {/*        siteTitle*/}
                {/*    )}.png?theme=light&md=0&fontSize=75px&images=https%3A%2F%2Fassets.vercel.com%2Fimage%2Fupload%2Ffront%2Fassets%2Fdesign%2Fnextjs-black-logo.svg`}*/}
                {/*/>*/}
                {/*<meta name="og:title" content={siteTitle} />*/}
                {/*<meta name="twitter:card" content="summary_large_image" />*/}
            </Head>
            {/*<header className="pr-3 z-50 fixed w-full bg-gradient-to-r from-teal-400 to-blue-500 h-20 shadow flex items-center">*/}
            <header className="w-full bg-teal-500 bg-gradient-to-r from-teal-400 to-blue-500 h-20 shadow flex items-center">
                <Link href="/">
                    <img src="../images/icons/icons8-timer-100.png" width="90" className="pl-2 pr-2 border-r border-teal-500 cursor-pointer" alt="Home"/>
                </Link>
                <img src="../images/icons/hamburger.svg" width="20" className="ml-3 cursor-pointer" alt="Menu" onClick={() => setMenuIsOpen((prevMenuIsOpen) => !prevMenuIsOpen)}/>
                <img src="../images/norman.png" className="rounded-full ml-auto h-10 w-10 border-2 border-white shadow" alt="Avatar"/>
            </header>
            <Menu
                menuIsOpen={menuIsOpen}
                onClick={() => setMenuIsOpen((prevMenuIsOpen) => !prevMenuIsOpen)}
                validToken={props.validToken}
            />
            <main className={`pl-3 pr-3`}>
                {/*{auth.email ? children : <Login />}*/}
                {props.children}
            </main>
        </div>
    )
}

export default Layout;
