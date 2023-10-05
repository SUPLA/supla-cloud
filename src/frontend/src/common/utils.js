import changeCase from "change-case";

export function safeJsonParse(possiblyJson) {
    try {
        return JSON.parse(possiblyJson);
    } catch (e) {
        return undefined;
    }
}

export function generatePassword(length, strong = false) {
    let text = "";
    const groups = ['abcdefghijkmnopqrstuvwxyz', '0123456789', 'ABCDEFGHIJKLMNPRSTUWXYZ', '!@#$%^&*_+=;:<>,.?/'];
    for (let i = 0; i < length; i++) {
        const possible = groups[Math.floor(Math.random() * (strong ? 4 : 2))];
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }
    return text;
}

export function subjectEndpointUrl(subject) {
    return `${changeCase.paramCase(subject.subjectType)}s/${subject.subjectId || subject.id}`;
}

export function removeByValue(array, value) {
    const index = array.indexOf(value);
    if (index !== -1) {
        array.splice(array.indexOf(value), 1);
    }
}

export function deepCopy(object) {
    if (object) {
        return JSON.parse(JSON.stringify(object));
    } else {
        return object;
    }
}

export function urlParams(paramsAsObject) {
    return Object.keys(paramsAsObject).map(function (k) {
        return encodeURIComponent(k) + '=' + encodeURIComponent(paramsAsObject[k])
    }).join('&');
}

export function extendObject(a, b) {
    for (let key in b) {
        if (Object.prototype.hasOwnProperty.call(b, key)) {
            a[key] = b[key];
        }
    }
    return a;
}
