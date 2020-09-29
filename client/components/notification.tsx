import React from 'react';

type NotificationParams = {
}

export const Notification = () => {
    return (
        <div className="flex max-w-md w-11/12 bg-teal-500 text-white shadow-lg rounded-lg overflow-hidden fixed bottom-0 notification bottom">
            <div className="flex items-center px-2 py-3">
                <img className="w-12 h-12 object-cover rounded-full" src="https://images.unsplash.com/photo-1477118476589-bff2c5c4cfbb?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=500&q=60"/>
                    <div className="mx-3">
                        <h2 className="text-xl font-semibold text-white">Hello john</h2>
                        <p className="tex-white">Sara was replied on the <a href="#" className="text-white">Upload Image</a>.</p>
                    </div>
            </div>
        </div>
    )
}
