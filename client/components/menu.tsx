import cn from 'classnames';
import React from 'react';
import Link from 'next/link'
import {useRouter} from "next/router";

type MenuProps = {
    menuIsOpen: boolean,
    onClick: Function
}

export const Menu = ({ menuIsOpen, onClick }: MenuProps ) => {
    const router = useRouter();
    return (
        <aside
            className={`flex flex-col transform fixed top-0 left-64 w-40 h-full bg-white h-full shadow-xl fixed overflow-auto ease-in-out transition-all duration-200 ${cn({'translate-x-0': menuIsOpen}, {'-translate-x-full': !menuIsOpen})}`}
            onClick={() => onClick()}
        >
            <div className="pr-3 bg-white shadow flex items-center h-20">
                <Link href="/">
                    <img src="../images/icons/icons8-timer-100.png" width="90" className="pl-2 pr-2 border-r border-teal-100 cursor-pointer" alt="Logo"/>
                </Link>
                <img src="../images/icons/close.svg" width="25" className="ml-5 cursor-pointer" alt="Menu" onClick={() => onClick()}/>
            </div>
            <ul className="flex flex-col flex-grow mt-4">
                {generateMainMenuItem('dashboard', true, 'dashboard', 'Dashboard', router, false)}
                {generateMainMenuItem('timers', false, 'timers', 'Timers', router, false)}
                {generateMainMenuItem('dailies', false, 'dailies', 'Dailies', router, false)}
                {generateMainMenuItem('personio', false, 'personio', 'Personio', router, false)}
                {generateMainMenuItem('settings', false, 'settings', 'Settings', router, true)}
                {generateMainMenuItem('logout', false, 'logout', 'Logout', router, false)}
            </ul>
        </aside>
    )
}

const generateMainMenuItem = (key, isActive, href, linkText, router, doSeparateFromTop) => {
    return <li key={key} className={`text-sm cursor-pointer text-gray-800 hover:bg-gray-100${cn({' border-l-4 border-blue-500 text-blue-500': router.pathname === '/'+href}, {' mt-auto': doSeparateFromTop})}`}><Link href={`/${href}`}><a className="w-full h-full py-2 pl-3 block">{linkText}</a></Link></li>
}
