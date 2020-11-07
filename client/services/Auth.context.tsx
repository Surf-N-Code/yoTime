import React, { useContext, useEffect, useReducer } from 'react';

import { IAuthInfo } from '../types/auth.types';

export const AuthStateContext = React.createContext({});

const initialState: IAuthInfo = { email: null, jwt: null };

enum ActionType {
	SetDetails = 'setAuthDetails',
	RemoveDetails = 'removeAuthDetails'
}

interface IAction {
	type: ActionType;
	payload: IAuthInfo;
}

const reducer: React.Reducer<{}, IAction> = (state, action) => {
	console.log('reducer action',action);
	switch (action.type) {
		case ActionType.SetDetails:
			console.log(action.payload);
			return {
				email: action.payload.email,
				jwt: action.payload.jwt
			};
		case ActionType.RemoveDetails:
			console.log('setting to initialState', initialState);
			return {
				email: initialState.email,
				jwt: initialState.jwt
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
