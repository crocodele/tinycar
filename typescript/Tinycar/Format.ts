module Tinycar
{
    export module Format
    {

        // Format specified unix timestamp into specified locale format
        export function toDate(time:number, format:string):string
        {
            // Invalid source value
            if (typeof time !== 'number' || time === 0)
                return null;

            // Create a date in local time, assuming that the
            // timestamp is always for UTC time
            var date = new Date(0);
            date.setUTCSeconds(time);

            // Part handlers
            var parts = {

                d : ():string =>
                {
                    let value = parts.j();
                    return '' + (value < 10 ? '0' + value : value);
                },
                G : ():number =>
                {
                    return date.getHours();
                },
                H : ():string =>
                {
                    let value = parts.G();
                    return '' + (value < 10 ? '0' + value : value);
                },
                i : ():string =>
                {
                    let value = date.getMinutes();
                    return '' + (value < 10 ? '0' + value : value);
                },
                j : ():number =>
                {
                    return date.getDate();
                },
                l : ():string =>
                {
                    return Tinycar.Locale.getText(
                        'calendar_day_' + date.getDay()
                    );
                },
                m : ():string =>
                {
                    let value = parts.n();
                    return '' + (value < 10 ? '0' + value : value);
                },
                n : ():number =>
                {
                    return (date.getMonth() + 1);
                },
                Y : ():number =>
                {
                    return date.getFullYear();
                }

            };

            let result = '';

            // Replace each value with corresponding
            // function handler
            for (let i = 0, j = format.length; i < j; ++i)
            {
                let letter = format.substring(i, i + 1);

                result += (parts.hasOwnProperty(letter) ?
                    parts[letter]() : letter
                );
            }

            return result;
        }

        // Convert specified date string into a unix timestamp
        export function toTime(source:string):number
        {

            // Invalid string to study
            if (typeof source !== 'string' || source.length === 0)
                return null;

            // Patterns to check for
            let patterns = [

                // d.m.Y H:i
                {
                    pattern : /([\d]{1,2})\.([\d]{1,2})\.([\d]{4})[\s]{0,}([\d]{1,2})\:([\d]{1,2})/,
                    matches : [3, 2, 1, 4, 5, null]
                },
                // d.m.Y
                {
                    pattern : /([\d]{1,2})\.([\d]{1,2})\.([\d]{4})/,
                    matches : [3, 2, 1, null, null, null]
                },
                // Y-m-d H:i
                {
                    pattern : /([\d]{4})\-([\d]{1,2})\-([\d]{1,2})[\s]{0,}([\d]{1,2})\:([\d]{1,2})/,
                    matches : [1, 2, 3, 4, 5    , null]
                },
                // Y-m-d
                {
                    pattern : /([\d]{4})\-([\d]{1,2})\-([\d]{1,2})/,
                    matches : [1, 2, 3, null, null, null]
                }

            ];

            let match;
            let matches;

            // Check for each pattern
            for (let i = 0, j = patterns.length; i < j; ++i)
            {
                // Get match for pattern
                match = source.match(patterns[i].pattern);

                // No match
                if (match === null)
                    continue;

                // Matches map
                matches = patterns[i].matches;

                // Result date object
                let result = new Date();

                // Set year, month and day
                result.setFullYear(parseInt(match[matches[0]]));
                result.setMonth(parseInt(match[matches[1]]) - 1);
                result.setDate(parseInt(match[matches[2]]));

                // Set hours
                result.setHours((matches[3] !== null ?
                    parseInt(match[matches[3]]) : 0
                ));

                // Set minutes
                result.setMinutes((matches[4] !== null ?
                    parseInt(match[matches[4]]) : 0
                ));

                // Set seconds
                result.setSeconds((matches[5] !== null ?
                    parseInt(match[matches[5]]) : 0
                ));

                // Get unix timestamp for UTC
                return Math.round(
                    (result.getTime() + result.getTimezoneOffset()) / 1000
                );
            }

            return null;
        }

    }

}
