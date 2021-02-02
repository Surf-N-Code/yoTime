import React, { useContext, useReducer } from 'react';

import { IGlobalStatus } from '../types/global.types';
import {Alert} from "../components/Alert";

export const GlobalMessagingContext = React.createContext({});

const initialState: IGlobalStatus = { message: '' };

enum ActionType {
	SetDetails = 'setMessage',
	RemoveDetails = 'removeMessage'
}

enum SeverityType {
	info = 'info',
	warning = 'warning',
	error = 'error',
}

interface IState {
	message: string;
	severity: string;
}

interface IAction {
	type: ActionType;
	severity: SeverityType;
	payload: IGlobalStatus;
}

const reducer = (state: IState, action: IAction) => {
	switch (action.type) {
		case ActionType.SetDetails:
			return {
				message: action.payload.message,
				severity: action.payload.severity
			};
		case ActionType.RemoveDetails:
			return {
				message: initialState.message
			};
		default:
			throw new Error(`Unhandled action type: ${action.type}`);
	}
};

export const GlobalMessagingProvider = ({ children }: any) => {
	const [state, dispatch] = useReducer(reducer, initialState);
	return (
		<GlobalMessagingContext.Provider value={[state, dispatch]}>
			{state.message != '' ?
				<Alert message={state.message} severity={state.severity}/> : ''
			}
			{children}
		</GlobalMessagingContext.Provider>
	);
};

// useContext hook - export here to keep code for global auth state
// together in this file, allowing user info to be accessed and updated
// in any functional component using the hook
export const useGlobalMessaging: any = () => useContext(GlobalMessagingContext);
