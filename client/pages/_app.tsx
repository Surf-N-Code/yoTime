import '../styles/global.css'
import "react-datepicker/dist/react-datepicker.css";
import "react-toggle/style.css";
import { Provider } from 'next-auth/client'

export default function App({ Component, pageProps }) {
    const { session } = pageProps
    return (
        <Provider session={session}>
            <Component {...pageProps} />
        </Provider>
    )
}
