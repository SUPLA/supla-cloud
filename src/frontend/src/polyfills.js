import {Duration} from "luxon";

// https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/startsWith
if (!String.prototype.startsWith) {
    String.prototype.startsWith = function (search, pos) {
        return this.substr(!pos || pos < 0 ? 0 : +pos, search.length) === search;
    };
}

// https://developer.mozilla.org/en-US/docs/Web/API/ChildNode/remove
// from:https://github.com/jserz/js_piece/blob/master/DOM/ChildNode/remove()/remove().md
(function (arr) {
    arr.forEach(function (item) {
        // eslint-disable-next-line no-prototype-builtins
        if (item.hasOwnProperty('remove')) {
            return;
        }
        Object.defineProperty(item, 'remove', {
            configurable: true,
            enumerable: true,
            writable: true,
            value: function remove() {
                if (this.parentNode !== null)
                    this.parentNode.removeChild(this);
            }
        });
    });
})([Element.prototype, CharacterData.prototype, DocumentType.prototype]);

// https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/find
// https://tc39.github.io/ecma262/#sec-array.prototype.find
if (!Array.prototype.find) {
    Object.defineProperty(Array.prototype, 'find', {
        value: function (predicate) {
            // 1. Let O be ? ToObject(this value).
            if (this == null) {
                throw new TypeError('"this" is null or not defined');
            }

            var o = Object(this);

            // 2. Let len be ? ToLength(? Get(O, "length")).
            var len = o.length >>> 0;

            // 3. If IsCallable(predicate) is false, throw a TypeError exception.
            if (typeof predicate !== 'function') {
                throw new TypeError('predicate must be a function');
            }

            // 4. If thisArg was supplied, let T be thisArg; else let T be undefined.
            var thisArg = arguments[1];

            // 5. Let k be 0.
            var k = 0;

            // 6. Repeat, while k < len
            while (k < len) {
                // a. Let Pk be ! ToString(k).
                // b. Let kValue be ? Get(O, Pk).
                // c. Let testResult be ToBoolean(? Call(predicate, T, « kValue, k, O »)).
                // d. If testResult is true, return kValue.
                var kValue = o[k];
                if (predicate.call(thisArg, kValue, k, o)) {
                    return kValue;
                }
                // e. Increase k by 1.
                k++;
            }

            // 7. Return undefined.
            return undefined;
        },
        configurable: true,
        writable: true
    });
}

// https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/findIndex
// https://tc39.github.io/ecma262/#sec-array.prototype.findIndex
if (!Array.prototype.findIndex) {
    Object.defineProperty(Array.prototype, 'findIndex', {
        value: function (predicate) {
            // 1. Let O be ? ToObject(this value).
            if (this == null) {
                throw new TypeError('"this" is null or not defined');
            }

            var o = Object(this);

            // 2. Let len be ? ToLength(? Get(O, "length")).
            var len = o.length >>> 0;

            // 3. If IsCallable(predicate) is false, throw a TypeError exception.
            if (typeof predicate !== 'function') {
                throw new TypeError('predicate must be a function');
            }

            // 4. If thisArg was supplied, let T be thisArg; else let T be undefined.
            var thisArg = arguments[1];

            // 5. Let k be 0.
            var k = 0;

            // 6. Repeat, while k < len
            while (k < len) {
                // a. Let Pk be ! ToString(k).
                // b. Let kValue be ? Get(O, Pk).
                // c. Let testResult be ToBoolean(? Call(predicate, T, « kValue, k, O »)).
                // d. If testResult is true, return k.
                var kValue = o[k];
                if (predicate.call(thisArg, kValue, k, o)) {
                    return k;
                }
                // e. Increase k by 1.
                k++;
            }

            // 7. Return -1.
            return -1;
        },
        configurable: true,
        writable: true
    });
}

// https://developer.mozilla.org/pl/docs/Web/JavaScript/Referencje/Obiekty/Array/includes#Polyfill
if (!Array.prototype.includes) {
    Array.prototype.includes = function (searchElement /*, fromIndex*/) {
        'use strict';
        var O = Object(this);
        var len = parseInt(O.length) || 0;
        if (len === 0) {
            return false;
        }
        var n = parseInt(arguments[1]) || 0;
        var k;
        if (n >= 0) {
            k = n;
        } else {
            k = len + n;
            if (k < 0) {
                k = 0;
            }
        }
        var currentElement;
        while (k < len) {
            currentElement = O[k];
            if (searchElement === currentElement ||
                (searchElement !== searchElement && currentElement !== currentElement)) { // NaN !== NaN
                return true;
            }
            k++;
        }
        return false;
    };
}

// https://github.com/moment/luxon/issues/1134#issuecomment-1033896418
Duration.prototype.__toHuman__ = Duration.prototype.toHuman;
Duration.prototype.toHuman = function (opts = {}) {
    let duration = this.normalize();
    let durationUnits = [];
    let precision;
    if (typeof opts.precision == "object") {
        precision = Duration.fromObject(opts.precision);
    }
    let remainingDuration = duration;
    //list of all available units
    const allUnits = ["years", "months", "days", "hours", "minutes", "seconds", "milliseconds"];
    let smallestUnitIndex;
    let biggestUnitIndex;
    // check if user has specified a smallest unit that should be displayed
    if (opts.smallestUnit) {
        smallestUnitIndex = allUnits.indexOf(opts.smallestUnit);
    }
    // check if user has specified a biggest unit
    if (opts.biggestUnit) {
        biggestUnitIndex = allUnits.indexOf(opts.biggestUnit);
    }
    // use seconds and years as default for smallest and biggest unit
    if (!((smallestUnitIndex >= 0) && (smallestUnitIndex < allUnits.length))) {
        smallestUnitIndex = allUnits.indexOf("seconds");
    }
    if (!((biggestUnitIndex <= smallestUnitIndex) && (biggestUnitIndex < allUnits.length))) {
        biggestUnitIndex = allUnits.indexOf("years");
    }

    for (let unit of allUnits.slice(biggestUnitIndex, smallestUnitIndex + 1)) {
        const durationInUnit = remainingDuration.as(unit);
        if (durationInUnit >= 1) {
            durationUnits.push(unit);
            let tmp = {};
            tmp[unit] = Math.floor(remainingDuration.as(unit));
            remainingDuration = remainingDuration.minus(Duration.fromObject(tmp)).normalize();

            // check if remaining duration is smaller than precision
            if (remainingDuration < precision) {
                // ok, we're allowed to remove the remaining parts and to round the current unit
                break;
            }
        }

        // check if we have already the maximum count of units allowed
        if (durationUnits.length >= opts.maxUnits) {
            break;
        }
    }
    // after gathering of units that shall be displayed has finished, remove the remaining duration to avoid non-integers
    duration = duration.minus(remainingDuration).normalize();
    duration = duration.shiftTo(...durationUnits);
    if (opts.stripZeroUnits === "all") {
        durationUnits = durationUnits.filter(unit => duration.values[unit] > 0);
    } else if (opts.stripZeroUnits === "end") {
        let mayStrip = true;
        durationUnits = durationUnits.reverse().filter((unit) => {
            if (!mayStrip) {
                return true;
            }
            if (duration.values[unit] === 0) {
                return false;
            } else {
                mayStrip = false;
            }
            return true;
        });
    }
    return duration.shiftTo(...durationUnits).__toHuman__(opts);
}
