import React from 'react';
import Layout from '../components/layout';
import isToday from "date-fns/isToday";

export const Dailies = () => {
    return (
        <Layout>
            <h1>Dailies</h1>
            {/*{isToday(dateStart) ? <img src="../images/icons/mail-sent.png" className="ml-3" alt="Daily summary mail successfully sent" width="25"/> : <img src="../images/icons/mail-outstanding.png" className="ml-3" alt="Daily Summary mail not sent" width="25"/> }*/}
            {/*{isToday(dateStart) ? <img src="../images/personio-success.png" className="ml-3" alt="Daily summary mail successfully sent" width="22"/> : <img src="../images/personio-fail.png" className="ml-3" alt="Daily Summary mail not sent" width="22"/> }*/}
        </Layout>
    )
}

export default Dailies;
