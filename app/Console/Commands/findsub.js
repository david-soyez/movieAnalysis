var OS = require('opensubtitles-api');

var start = Date.now(), ms1, ms2, ms3;
var imdbid = '0898266', show = 'The Big Bang Theory', s = '01', ep = '01';

var imdbid = process.argv[2];
var lang = process.argv[3];


/** HTTPS search **/
var OpenSubtitles = new OS({
        useragent: 'OSTestUserAgentTemp',
        endpoint: 'https://api.opensubtitles.org:443/xml-rpc'
});

OpenSubtitles.search({
//        season: s,
 //       episode: ep,
        imdbid: imdbid,
        limit: 'all',
        sublanguageid: lang
})
.then(function (subtitles) {
    console.log(JSON.stringify(subtitles));
})

