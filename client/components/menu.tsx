import cn from 'classnames';
import React from 'react';
import Link from 'next/link'

type MenuProps = {
    menuIsOpen: boolean,
    onClick: Function
}

export const Menu = ({ menuIsOpen, onClick }: MenuProps ) => {
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
            <ul className="flex flex-col flex-grow">
                <li key="dashboard" className="menu__item text-gray-800 mt-4 hover:bg-gray-100"><Link href="/dashboard"><a>Dashboard</a></Link></li>
                <li key="punch" className="menu__item text-gray-800 hover:bg-gray-100 border-l-4 border-blue-500 text-blue-500"><Link href="/timers"><a>Timers</a></Link></li>
                <li key="daily-summary" className="menu__item text-gray-800 hover:bg-gray-100"><Link href="/dailies"><a>Daily Summary</a></Link></li>
                <li key="personio" className="menu__item text-gray-800 hover:bg-gray-100"><Link href="/personio"><a>Personio</a></Link></li>
                <li key="seetings" className="menu__item text-gray-800 mt-auto hover:bg-gray-100"><Link href="/settings"><a>Settings</a></Link></li>
                <li key="logout" className="menu__item text-gray-800 mb-4 hover:bg-gray-100"><Link href="/logout"><a>Logout</a></Link></li>
            </ul>
        </aside>
    )
}
