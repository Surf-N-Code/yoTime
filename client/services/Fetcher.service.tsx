export const FetcherFunc = (...args) => {
    const [url, token, type, body, contentType] = args;
    const prefixedUrl = [...url][0] !== '/' ? `/${url}`: url;
    return fetch(`https://localhost:8443${prefixedUrl}`, {
        method: type,
        headers: {
            Accept: 'application/ld+json',
            'Content-Type': contentType ? contentType : 'application/json',
            Authorization: 'Bearer ' + token
        },
        body: JSON.stringify(body),
    })
        .then((response) => {
            return response.json()
        })
        .catch((error) => {
            console.log('fetcherfunc error', error.message);
            throw new Error(error.message);
        });
}
