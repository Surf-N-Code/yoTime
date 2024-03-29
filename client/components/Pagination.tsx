import React from 'react';
import Link from "next/link";
import cn from 'classnames';

type PaginationProps = {
    currentPage: number,
    setPageIndex: Function,
    totalPages: number,
}

export const Pagination = ({currentPage, setPageIndex, totalPages}: PaginationProps) => {
    if (totalPages === 0 || totalPages === 1) {
        return (<></>);
    }
    return (
        <div className="flex flex-col items-center my-12">
        <div className="flex text-gray-700">
            {currentPage > 1 ?
                // <Link href={`${path}?page=${currentPage-1}${urlParams !== null && typeof urlParams !== 'undefined' ? urlParams : ''}`}>
                    <a className="h-12 w-12 mr-1 flex justify-center items-center rounded-full cursor-pointer" onClick={() => setPageIndex(currentPage-1)}>
                        <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2" strokeLinecap="round"
                             strokeLinejoin="round" className="feather feather-chevron-left w-6 h-6">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                    </a>
                // </Link>
                :
                <div className="h-12 w-12 mr-1 flex justify-center items-center rounded-full cursor-pointer opacity-25">
                    <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2" strokeLinecap="round"
                         strokeLinejoin="round" className="feather feather-chevron-left w-6 h-6">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                </div>
            }
            <div className="flex h-12 font-medium rounded-full bg-gray-200">
                {generatePagination(totalPages, currentPage, setPageIndex)}
                <div className="w-12 h-12 md:hidden flex justify-center items-center cursor-pointer leading-5 transition duration-150 ease-in rounded-full bg-teal-600 text-white">{currentPage}</div>
            </div>

            {currentPage <= totalPages - 1 ?
                <a className="h-12 w-12 mr-1 flex justify-center items-center rounded-full cursor-pointer"  onClick={() => setPageIndex(currentPage+1)}>
                    <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2" strokeLinecap="round"
                         strokeLinejoin="round" className="feather feather-chevron-right w-6 h-6">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </a>
                :
                <div className="h-12 w-12 mr-1 flex justify-center items-center rounded-full cursor-pointer opacity-25">
                    <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2" strokeLinecap="round"
                         strokeLinejoin="round" className="feather feather-chevron-right w-6 h-6">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </div>
            }
        </div>
    </div>
    )
}

const generatePagination = (totalPages: number, currentPage: number, setPageIndex: Function) => {
    let pageIndicatorLow = 0;
    let pageIndicatorHigh = 0;
    return (
        Array.from({length: totalPages}, (_, i) => i + 1).map((page, value) => {
            if (page > 1 && page < currentPage -1) {
                pageIndicatorLow++;
            }
            if (page > 1 && page > currentPage +1) {
                pageIndicatorHigh++;
            }

            return (
                page === 1 || page === totalPages || page === currentPage || page === currentPage-1 || page === currentPage+1 ?
                    // <Link key={`page_${page}`} href={`http://localhost:3000/${path}?page=${page}`}>
                        <a
                            key={`page_${page}`}
                            className={`w-12 md:flex justify-center items-center hidden cursor-pointer leading-5 transition duration-150 ease-in hover:bg-white rounded-full${cn({' bg-teal-600 text-white hover:text-gray-700': currentPage === page})}`}
                            onClick={() => setPageIndex(page)}
                        >{page}</a>
                    // </Link>
                    :
                    (pageIndicatorLow === 1 && pageIndicatorHigh === 0) || pageIndicatorHigh === 1?
                        <div key={`page_${page}`} className={`w-12 md:flex justify-center items-center hidden cursor-default leading-5 transition duration-150 ease-in rounded-full`}>...</div>
                        :
                        null
            )
        })
    )
}
