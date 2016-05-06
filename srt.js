var OS = require('opensubtitles-api');

var call_1, call_2, call_3;
var start = Date.now(), ms1, ms2, ms3;
var imdbid = '0898266', show = 'The Big Bang Theory', s = '01', ep = '01';

/** HTTPS search **/
var OpenSubtitles = new OS({
        useragent: 'OSTestUserAgent',
        endpoint: 'https://api.opensubtitles.org:443/xml-rpc'
});

OpenSubtitles.search({
//        season: s,
 //       episode: ep,
        imdbid: 'tt1267297',
  //      limit: 'all'
        sublanguageid: 'eng'
})
.then(function (subtitles) {
    console.log(subtitles);
})

