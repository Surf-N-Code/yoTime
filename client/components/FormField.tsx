import {useField} from "formik";
import React from "react";
import cn from 'classnames';

export const FormField = ({ label, ...props }) => {
    const [field, meta] = useField(props);

    return (
        <>
            {label ? <label htmlFor={props.id || props.name} className={"block mb-2 text-sm font-medium text-gray-600"}>{label}</label> : ''}
            <input className={`${cn({'border-red-500':meta.touched && meta.error})} w-full px-3 py-2 text-sm leading-tight text-gray-700 border rounded shadow appearance-none focus:outline-none focus:shadow-outline`} {...field} {...props} />
            {meta.touched && meta.error ? (
                <p className="text-xs italic text-red-500">{meta.error}</p>
            ) : null}
        </>
    )
}
