import '../styles/global.css'
import "react-datepicker/dist/react-datepicker.css";
import "react-toggle/style.css";
import { AuthProvider } from '../services/Auth.context';
import { GlobalMessagingProvider } from '../services/GlobalMessaging.context';
import { AppProps } from 'next/app';

export default function App ({ Component, pageProps }: AppProps) {
    console.log(pageProps);
    return (
        <AuthProvider>
            <GlobalMessagingProvider>
                <Component {...pageProps} />
            </GlobalMessagingProvider>
        </AuthProvider>
    );
}
