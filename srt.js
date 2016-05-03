var OS = require('opensubtitles-api');
var OpenSubtitles = new OS('OSTestUserAgent');

var srt = {};

OpenSubtitles.login()
        .then(function(token){
            srt.start(token);
        })
    .catch(function(err){
            console.log(err);
        });

srt.start = function(token) {
        OpenSubtitles.search({
            token: token,
            sublanguageid: 'eng',
            imdbid: '3774114'
        }).then(function (subtitles) {
            console.log(subtitles);
    });

}

