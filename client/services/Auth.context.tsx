import React, { useContext, useEffect, useReducer } from 'react';

import {IAuthInfo, IJwt} from '../types/auth.types';
import {GetServerSideProps} from "next";
import {TokenService} from "../services";
import jwt_decode from "jwt-decode";

export const AuthStateContext = React.createContext({});

const initialState: IAuthInfo = { email: null, jwt: null, initials: null };

enum ActionType {
	SetDetails = 'setAuthDetails',
	RemoveDetails = 'removeAuthDetails'
}

interface IAction {
	type: ActionType;
	payload: IAuthInfo;
}

const reducer: React.Reducer<{}, IAction> = (state, action) => {
	switch (action.type) {
		case ActionType.SetDetails:
			const decoded: IJwt = jwt_decode(action.payload.jwt);
			return {
				email: action.payload.email,
				jwt: action.payload.jwt,
				initials: decoded.initials
			};
		case ActionType.RemoveDetails:
			return {
				email: initialState.email,
				jwt: initialState.jwt,
				initials: initialState.initials
			};
		default:
			throw new Error(`Unhandled action type: ${action.type}`);
	}
};

export const AuthProvider = ({ children }: any) => {
	let localState = null;
	if (typeof localStorage !== 'undefined' && localStorage.getItem('userInfo')) {
		localState = JSON.parse(localStorage.getItem('userInfo') || '');
	}
	console.log('local state',localState, initialState);
	const [state, dispatch] = useReducer(reducer, localState || initialState);

	if (typeof localStorage !== 'undefined') {
		useEffect(() => {
			localStorage.setItem('userInfo', JSON.stringify(state));
		}, [state]);
	}
	return (
		<AuthStateContext.Provider value={[state, dispatch]}>
			{children}
		</AuthStateContext.Provider>
	);
};

// useContext hook - export here to keep code for global auth state
// together in this file, allowing user info to be accessed and updated
// in any functional component using the hook
export const useAuth: any = () => useContext(AuthStateContext);


export const getServerSideProps: GetServerSideProps = (async (context) => {
	const tokenService = new TokenService();
	const validToken = await tokenService.authenticateTokenSsr(context)
	if (!validToken) {
		return {
			redirect: {
				permanent: false,
				destination: '/login',
			},
		}
	}
	return { props: { } };
});
