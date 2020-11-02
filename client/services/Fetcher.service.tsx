export const fetcherFunc = (...args) => {
    const [url, token, type, body, contentType] = args;
    console.log("fetching: " + url);
    return fetch(`https://localhost:8443/${url}`, {
        method: type,
        headers: {
            Accept: 'application/ld+json',
            // Accept: 'application/json',
            'Content-Type': contentType ? contentType : 'application/json',
            Authorization: 'Bearer ' + token
        },
        body: JSON.stringify(body),
    }).then(response => response.json());
}

export default fetcherFunc;
