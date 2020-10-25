import Head from 'next/head';
import React, { Component } from "react";
import Link from 'next/link';
import { Menu } from './menu';
import {Login} from "../pages/login";

export const siteTitle = 'YoTime - your timetracker for Slack'

type LayoutState = {
    menuIsOpen: boolean;
    isLoggedIn: boolean;
}

class Layout extends Component<{children}, LayoutState> {
    readonly state = {
        menuIsOpen: false,
        isLoggedIn: false
    };

    toggleMenu = () => {
        this.setState({menuIsOpen: !this.state.menuIsOpen})
    }

    render() {
        return (
            <div className="w-full h-full flex-1">
                <Head>
                    <link rel="icon" href="/favicon.ico" />
                    <meta
                        name="description"
                        content="Learn how to build a personal website using Next.js"
                    />
                    <meta
                        property="og:image"
                        content={`https://og-image.now.sh/${encodeURI(
                            siteTitle
                        )}.png?theme=light&md=0&fontSize=75px&images=https%3A%2F%2Fassets.vercel.com%2Fimage%2Fupload%2Ffront%2Fassets%2Fdesign%2Fnextjs-black-logo.svg`}
                    />
                    <meta name="og:title" content={siteTitle} />
                    <meta name="twitter:card" content="summary_large_image" />
                </Head>
                <header className="pr-3 z-10 fixed w-full bg-gradient-to-r to-blue-500 from-teal-400 h-20 shadow flex items-center">
                    <Link href="/">
                        <img src="../images/icons/icons8-timer-100.png" width="90" className="pl-2 pr-2 border-r border-teal-500 cursor-pointer" alt="Home"/>
                    </Link>
                    <img src="../images/icons/hamburger.svg" width="20" className="ml-3 cursor-pointer" alt="Menu" onClick={() => this.toggleMenu()}/>
                    <img src="../images/norman.png" className="rounded-full ml-auto h-10 w-10 border-2 border-white shadow" alt="Avatar"/>
                </header>
                <Menu
                    menuIsOpen={this.state.menuIsOpen}
                    onClick={() => this.toggleMenu()}
                />
                <main className={`pt-20 pl-3 pr-3`}>
                    {this.state.isLoggedIn ? this.props.children : <Login/>}
                </main>
            </div>
        )
    }
}

export default Layout;
