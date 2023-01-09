<head>
    <link media="all" rel="stylesheet" href="{{ mix('css/video-js.css', 'Ottohm/PlayerWebSDK') }}" />
    <link rel="icon" href="https://www.bcci.tv/assets/images/bcci.png" type="image/x-icon" />
    <link media="all" rel="stylesheet" href="{{ mix('css/videojs-resolution-switcher.css', 'Ottohm/PlayerWebSDK') }}" />
  
    <script src="{{ mix('js/jquery.min.js', 'Ottohm/PlayerWebSDK') }}"></script>
    <script src="{{ mix('js/video.js', 'Ottohm/PlayerWebSDK') }}"></script>
  </head>
  
  <body>
    <video-js id="player" controls preload="auto" autoplay playsinline>
      <source id="sourceId" type="application/x-mpegURL" />
    </video-js>
  
    <script src="{{ mix('/js/videojs-resolution-switcher.js', 'Ottohm/PlayerWebSDK') }}"></script>
    <script src="{{ mix('/js/axios.min.js', 'Ottohm/PlayerWebSDK') }}"></script>
    <script src="{{ mix('/js/hmac-sha1.js', 'Ottohm/PlayerWebSDK') }}"></script>
    <script src="{{ mix('/js/common.js', 'Ottohm/PlayerWebSDK') }}"></script>
    <script src="{{ mix('/js/valve.js', 'Ottohm/PlayerWebSDK') }}"></script>
    <script src="{{ mix('/js/videojs.watermark.js', 'Ottohm/PlayerWebSDK') }}"></script>
    <script src="{{ mix('/js/videojs-contrib-quality-levels.js', 'Ottohm/PlayerWebSDK') }}"></script>
    <script src="{{ mix('/js/videojs-http-source-selector.js', 'Ottohm/PlayerWebSDK') }}"></script>
    <script>
      let videoId = {{ $id }};
      let videoUrl, videoTitleName;
      let oldVideoId = videoId;
      const fp = new Fingerprint2();
      let analyticsObject = [];
  
      function getNumberFromUrl(string) {
         return +string.match(/\d+/)[0];
      }

      function analyticsAPIData(obj) {
        let current_datetime = new Date();
        let formatted_date =
          current_datetime.getUTCFullYear() +
          "-" +
          appendLeadingZeroes(current_datetime.getUTCMonth() + 1) +
          "-" +
          appendLeadingZeroes(current_datetime.getUTCDate()) +
          "T" +
          appendLeadingZeroes(current_datetime.getUTCHours()) +
          ":" +
          appendLeadingZeroes(current_datetime.getUTCMinutes()) +
          ":" +
          appendLeadingZeroes(current_datetime.getUTCSeconds());
        const finalAnalyticsData = {
          deviceManufacture: "",
          browserType: window.module.init().browser.name,
          userAgent: navigator.userAgent,
          domain: "OttohmPlayer",
          source: "",
          destination: "",
          user: `${obj.ip}_${obj.fingerprint}`,
          startTimeMs: obj.startTimeMs ?? "",
          rebufferingSeconds: obj.rebufferingSeconds ?? "",
          deviceModel: "Browser",
          deviceOSVersion: window.module.init().os.version.toString(),
          deviceBrand: "",
          deviceId: obj.fingerprint,
          deviceType: "Web",
          deviceOS: window.module.init().os.name,
          ip: obj.ip,
          networkType: navigator?.connection?.effectiveType,
          wifiCellular: "",
          mOperator: "",
          event: obj.event,
          logSessionId: obj.logSessionId,
          country: obj.country,
          region: obj.region,
          city: obj.city,
          timezone: obj.timezone,
          latitude: obj.latitude,
          longitude: obj.longitude,
          videoId: obj.videoId,
          bitrate: obj.bitrate ?? "",
          frameRate: obj.frameRate ?? "",
          width: obj.width,
          height: obj.height,
          totalWatchTimeInMilliseconds: obj.totalWatchTimeInMilliseconds ?? "",
          errorMessage: obj.errorMessage ?? "",
          subtitleLanguage: "",
          audioLanguage: "",
          droppedFrames: "",
          playerName: "VideoJS",
          playerVersion: "7.1.0",
          engagedCompletePercentage: obj.engagedCompletePercentage ?? 0,
          extras: [],
          logGeneratedDateTime: formatted_date,
          videoName: obj.videoName,
          errorCode: obj.errorCode ?? "",
          totalDurationInMilliseconds: obj.totalDurationInMilliseconds ?? "",
          renditionUrl: obj.renditionUrl,
          renditionMimeType: "application/x-mpegURL",
        };
        analyticsObject.push(finalAnalyticsData);
        return finalAnalyticsData;
      }
  
      function appendLeadingZeroes(n) {
        if (n <= 9) {
          return "0" + n;
        }
        return n
      }

      let eventObj = {}
  
      function formatWithEncrypt(method, path, queryParams, date) {
        let secretKey = `{{ $secretKey }}`
        let current_datetime = date
        let formatted_date = current_datetime.getUTCFullYear() +
          "-" +
          appendLeadingZeroes(current_datetime.getUTCMonth() + 1) +
          "-" +
          appendLeadingZeroes(current_datetime.getUTCDate()) +
          " " +
          appendLeadingZeroes(current_datetime.getUTCHours()) +
          ":" +
          appendLeadingZeroes(current_datetime.getUTCMinutes()) +
          ":" +
          appendLeadingZeroes(current_datetime.getUTCSeconds());
        let textToEncrypt = `${method}${path}${queryParams}${formatted_date}`
        let hmac = b64_hmac_sha1(secretKey, textToEncrypt);
        return hmac;
      }

    
  
      fp.get((result) => {
        axios.get(
          `{{ $getVideoDetails }}/analytics?accessToken=${encodeURIComponent(formatWithEncrypt('GET', '/analytics', '', new Date()))}`
        ).then(res => {
          const analyticsData = res.data;
          axios.get(
            `{{ $getVideoDetails }}/api/v1/video/url?videoId=${videoId}&accessToken=${encodeURIComponent(formatWithEncrypt('GET', '/api/v1/video/url', `?videoId=${videoId}`, new Date()))}`
          ).then(res => {
            const playerMaster = res.data.data
            $("#sourceId").attr('src', playerMaster.video_url);
            axios.post(
              `{{ $getVideoDetails }}/drm-token-generator?videoid=${videoId}&accessToken=${encodeURIComponent(formatWithEncrypt('POST', '/drm-token-generator', `?videoid=${videoId}`, new Date()))}`
            ).then(res => {
              var options = {
                errorDisplay: true,
                plugins: {
                  httpSourceSelector: {
                    default: "low",
                  },
                }
              };
              var player = videojs("player", options);
              player.httpSourceSelector();
              player.watermark({
                file: "{{ mix('/image/bcci.png', 'Ottohm/PlayerWebSDK') }}",
                xpos: 100,
                ypos: 0,
                opacity: 0.9,
              });
              // player.ready
              eventObj = {
                width: player.currentDimensions("width").width,
                height: player.currentDimensions("height").height,
                videoName: playerMaster.content_name,
                logSessionId: analyticsData.logSessionId,
                country: analyticsData.country,
                region: analyticsData.region,
                city: analyticsData.city,
                timezone: analyticsData.timezone,
                latitude: analyticsData.latitude,
                longitude: analyticsData.longitude,
                ip: analyticsData.ip,
                videoId: videoId,
                fingerprint: result,
                renditionUrl: playerMaster.video_url,
              };
              eventObj.event = "PLAYER_VIDEO_LOAD";
              analyticsAPIData(eventObj);
              var prefix = "skd://100000000000001";
              var urlTpl =
                `{{ $getVideoDetails }}/drm-key-provider?token=${res.data}`;
              eventObj.event = "PLAYER_VIDEO_FIRST_FRAME_RENDER";
              analyticsAPIData(eventObj);
              const playerLoad = new Date().valueOf();
              let currentTime = 0;
              videojs.options.hls.overrideNative = true;
              videojs.options.html5.nativeAudioTracks = false;
              videojs.options.html5.nativeTextTracks = false;
              player.on("loadstart", function (e) {
                const playerLoaded = new Date().valueOf();
                eventObj.startTimeMs = +playerLoaded - playerLoad;
                // Video Load
                if (!navigator.userAgent.includes("Mobile")) {
                  player.tech().hls.xhr.beforeRequest = function (
                    options) {
                    // required for detecting only the key requests
                    if (!options.uri.startsWith(prefix)) {
                      return;
                    }
                    options.headers = options.headers || {};
                    options.headers["x-access-token"] =
                      formatWithEncrypt(
                        "GET",
                        "/drm-key-provider",
                        `?token=${res.data}`,
                        new Date()
                      );
                    options.uri = urlTpl.replace(
                      "{key}",
                      options.uri.substring(prefix.length)
                    );
                    eventObj.bitrate = player
                      .tech(true)
                      .hls.playlists.media()
                      .attributes.BANDWIDTH.toString();
                    eventObj.frameRate = player
                      .tech(true)
                      .hls.playlists.media().attributes[
                      "FRAME-RATE"];
                    analyticsAPIData(eventObj);
                    // first frame render
                    //startTimeMS
                  };
                };
              });
  
  
              player.on("play", (e) => {
                eventObj.event = "PLAYER_VIDEO_READY";
                oldVideoId = null;
                eventObj = {
                  ...eventObj,
                  videoName: videoTitleName || playerMaster.content_name,
                  renditionUrl: videoUrl || playerMaster.video_url,
                  videoId: videoUrl
                    ? getNumberFromUrl(videoUrl)
                    : getNumberFromUrl(playerMaster.video_url),
                };
                analyticsAPIData(eventObj);
              });
              player.on("canplay", (e) => {
                eventObj.event = "PLAYER_VIDEO_READY";
                eventObj = {
                  ...eventObj,
                  videoName: videoTitleName || playerMaster.content_name,
                  renditionUrl: videoUrl || playerMaster.video_url,
                  videoId: videoUrl
                    ? getNumberFromUrl(videoUrl)
                    : getNumberFromUrl(playerMaster.video_url),
                };
                analyticsAPIData(eventObj);
              });
              player.on("timeupdate", (e) => {
                eventObj.totalWatchTimeInMilliseconds = Math.round(
                  player.currentTime() * 1000
                );
                eventObj.totalDurationInMilliseconds = Math.round(
                  player.duration() * 1000
                );
                eventObj.engagedCompletePercentage = +Math.trunc(
                  (player.currentTime() / player.duration()) * 100
                );
                eventObj.event = "PLAYER_VIDEO_ENGAGE_PERCENT";
                eventObj = {
                  ...eventObj,
                  videoName: videoTitleName || playerMaster.content_name,
                  renditionUrl: videoUrl || playerMaster.video_url,
                  videoId: videoUrl
                    ? getNumberFromUrl(videoUrl)
                    : getNumberFromUrl(playerMaster.video_url),
                };
                if (eventObj.engagedCompletePercentage > 95)
                  eventObj.event = "PLAYER_VIDEO_COMPLETED";
                analyticsAPIData(eventObj);
              });
              player.on("error", (e) => {
                eventObj.errorCode = e.target.player.error_.code;
                eventObj.errorMessage = e.target.player.error_.message;
                eventObj.event = "PLAYER_VIDEO_ERROR";
                eventObj = {
                  ...eventObj,
                  videoName: videoTitleName || playerMaster.content_name,
                  renditionUrl: videoUrl || playerMaster.video_url,
                  videoId: videoUrl
                    ? getNumberFromUrl(videoUrl)
                    : getNumberFromUrl(playerMaster.video_url),
                };
                analyticsAPIData(eventObj);
              });
              player.on("abort", (e) => {
                eventObj.event = "PLAYER_VIDEO_EXIT";
                eventObj = {
                  ...eventObj,
                  videoName: playerMaster.content_name,
                  videoId: getNumberFromUrl(playerMaster.video_url),
                  renditionUrl: playerMaster.video_url,
                };
                analyticsAPIData(eventObj);
              });
              player.on("ended", (e) => {
                eventObj.event = "PLAYER_VIDEO_EXIT";
                eventObj = {
                  ...eventObj,
                  videoName: videoTitleName || playerMaster.content_name,
                  renditionUrl: videoUrl || playerMaster.video_url,
                  videoId: videoUrl
                    ? getNumberFromUrl(videoUrl)
                    : getNumberFromUrl(playerMaster.video_url),
                };
                analyticsAPIData(eventObj);
              });
              let rebufferingSecondsLoading = 0;
              player.on("waiting", (e) => {
                eventObj.event = "PLAYER_VIDEO_BUFFER";
                eventObj = {
                  ...eventObj,
                  videoName: videoTitleName || playerMaster.content_name,
                  renditionUrl: videoUrl || playerMaster.video_url,
                  videoId: videoUrl
                    ? getNumberFromUrl(videoUrl)
                    : getNumberFromUrl(playerMaster.video_url),
                };
                analyticsAPIData(eventObj);
                currentTime = player.currentTime();
                rebufferingSecondsLoading = new Date().valueOf();
              });
              window.addEventListener("online", (event) => {
                player.src({
                  type: player.currentType(),
                  src: player.currentSrc(),
                });
                player.load();
                player.currentTime(currentTime);
                eventObj.event = "PLAYER_VIDEO_READY";
                eventObj.rebufferingSeconds = +new Date().valueOf() -
                  rebufferingSecondsLoading;
                eventObj = {
                  ...eventObj,
                  videoName: videoTitleName || playerMaster.content_name,
                  renditionUrl: videoUrl || playerMaster.video_url,
                  videoId: videoUrl
                    ? getNumberFromUrl(videoUrl)
                    : getNumberFromUrl(playerMaster.video_url),
                };
                analyticsAPIData(eventObj);
              });
              setInterval(function () {
                if (
                  eventObj.engagedCompletePercentage <= 100 &&
                  analyticsObject.length > 0
                ) {
                  eventObj.event = "PLAYER_VIDEO_ENGAGEMENT";
                  analyticsAPIData(eventObj);
                  axios
                    .post(
                      `{{ $getVideoDetails }}/analytics?accessToken=${encodeURIComponent(
                        formatWithEncrypt(
                          "POST",
                          "/analytics",
                          ``,
                          new Date()
                        )
                      )}`,
                      analyticsObject
                    )
                    .then((res) => {
                      analyticsObject = [];
                    });
                }
              }, 30000);
              clearInterval();
            });
          });
        });
      });
  
  
      function exit() {
        var player = videojs.getPlayer('player');
        player.dispose();
        eventObj.event = "PLAYER_VIDEO_EXIT";
        analyticsAPIData(eventObj);
        axios.post(`{{ $getVideoDetails }}/analytics?accessToken=${encodeURIComponent(
          formatWithEncrypt("POST", "/analytics", ``, new Date()))}`, analyticsObject)
          .then((res) => {
            analyticsObject = [];
          });
      }
  
      function newVideoPLay(id) {
        oldVideoId = videoId;
        videoId = id;
        axios
          .get(
            `{{ $getVideoDetails }}/api/v1/video/url?videoId=${videoId}&accessToken=${encodeURIComponent(
              formatWithEncrypt(
                "GET",
                "/api/v1/video/url",
                `?videoId=${videoId}`,
                new Date()
              )
            )}`
          )
          .then((playerDetail) => {
            axios
              .post(
                `{{ $getVideoDetails }}/drm-token-generator?videoid=${videoId}&accessToken=${encodeURIComponent(
                  formatWithEncrypt(
                    "POST",
                    "/drm-token-generator",
                    `?videoid=${videoId}`,
                    new Date()
                  )
                )}`
              )
              .then((response) => {
                var options = {
                  errorDisplay: true,
                  plugins: {
                    httpSourceSelector: {
                      default: "low",
                    },
                  },
                };
                var newPlayer = videojs("player", options);
                newPlayer.httpSourceSelector();
                newPlayer.watermark({
                  file: "{{ mix('/image/bcci.png', 'Ottohm/PlayerWebSDK') }}",
                  xpos: 100,
                  ypos: 0,
                  opacity: 0.9,
                });
                newPlayer.src(playerDetail.data.data.video_url);
                videoUrl = playerDetail.data.data.video_url;
                videoTitleName =
                  playerDetail.data.data.content_name;
                eventObj = {
                  ...eventObj,
                  videoName: playerDetail.data.data.content_name,
                  renditionUrl: playerDetail.data.data.video_url,
                  videoId: getNumberFromUrl(playerDetail.data.data.video_url),
                  totalDurationInMilliseconds: 0,
                  startTimeMs: 0,
                };
                eventObj.event = "PLAYER_VIDEO_LOAD";
                analyticsAPIData(eventObj);
                var prefix = "skd://100000000000001";
                var urlTpl = `{{ $getVideoDetails }}/drm-key-provider?token=${response.data}`;
                eventObj.event =
                  "PLAYER_VIDEO_FIRST_FRAME_RENDER";
                analyticsAPIData(eventObj);
                newPlayer.on("loadstart", function (e) {
                  const playerLoaded = new Date().valueOf();
                  // Video Load
                  if (!navigator.userAgent.includes("Mobile")) {
                    newPlayer.tech().hls.xhr.beforeRequest = function (
                      options
                    ) {
                      // required for detecting only the key requests
                      if (!options.uri.startsWith(prefix)) {
                        return;
                      }
                      options.headers = options.headers || {};
                      options.headers["x-access-token"] =
                        formatWithEncrypt(
                          "GET",
                          "/drm-key-provider",
                          `?token=${response.data}`,
                          new Date()
                        );
                      options.uri = urlTpl.replace(
                        "{key}",
                        options.uri.substring(prefix.length)
                      );
                      eventObj.bitrate = newPlayer.tech(true)
                        .hls.playlists.media()
                        .attributes.BANDWIDTH.toString();
                      eventObj.frameRate = newPlayer.tech(true)
                        .hls.playlists.media().attributes[
                        "FRAME-RATE"];
                      analyticsAPIData(eventObj);
                    };
                  }
                });
                newPlayer.play();
              });
          });
      }
    </script>
  </body>