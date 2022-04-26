/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/js/app.js":
/*!*****************************!*\
  !*** ./resources/js/app.js ***!
  \*****************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

// require('./bootstrap');
__webpack_require__(/*! ./stream */ "./resources/js/stream.js");

/***/ }),

/***/ "./resources/js/stream.js":
/*!********************************!*\
  !*** ./resources/js/stream.js ***!
  \********************************/
/***/ (() => {

$(document).ready(function () {
  var url = new URL(document.location.href);
  var voucher = url.searchParams.get("vtoken");
  var referer = new URL(document.referrer);
  var page_403 = "<div class=\"unauthorized\">\n\t\t\t\t\t\t<h1>403</h1>\n\t\t\t\t\t\t<div>\n\t\t\t\t\t\t\t<p>> <span>ERROR CODE</span>: \"<i>Access Forbidden</i>\"</p>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t";

  var get = function get() {
    if (voucher) {
      var _data = {
        voucher: voucher,
        referer: referer.protocol + '//' + referer.hostname
      };
      $.ajax({
        url: "https://ara-sss.net/api/" + 'domain/iframe-stream-list',
        type: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        data: JSON.stringify(_data)
      }).then(function (response) {
        if (response.status == 1) {
          var data = response;
          var stream = data.data || [];
          coupon = crackCoupon(data.coupon);
          insertDocument(stream);
          setTimeout(sendLog(data), 5000);
        } else {
          $('body').html(page_403);
        }
      }, function (error) {
        $('body').html(page_403);
      });
    } else {
      $('body').html(page_403);
    }
  };

  var coupon;
  var v;
  get();
  var active = 'videojs_player_';

  var insertDocument = function insertDocument(stream) {
    if (stream.length > 0) {
      active = 'videojs_player_' + parseInt(Math.random() * 1000);
      v = "<video id=\"" + active + "\" class=\"video-js vjs-default-skin vjs-big-play-centered\" style=\"width:100%; height:100%;object-fit: fill;\" controls preload=\"auto\" autoplay=\"true\" playsinline data-setup=\"{}\" disablePictureInPicture>\n\t\t\t\t\t\t\t<source src=\"".concat(stream[0].generated, "\" type=\"application/x-mpegURL\">\n\t\t\t\t\t\t</video>");
      $('body').html(v);
      var overrideNative = false;
      var player = videojs(active, {
        controlBar: {
          pictureInPictureToggle: false,
          volumePanel: true,
          playToggle: false
        },
        bigPlayButton: true,
        type: "application/x-mpegURL",
        autoplay: true,
        poster: "images/" + stream[0].thumbnail,
        inactivityTimeout: 0,
        html5: {
          hls: {
            overrideNative: !videojs.browser.IS_ANY_SAFARI
          },
          nativeVideoTracks: !overrideNative,
          nativeAudioTracks: !overrideNative,
          nativeTextTracks: !overrideNative
        },
        vhs: {
          liveRangeSafeTimeDelta: 0
        }
      });
      player.ready(function () {
        player.tech_.off('dblclick');
        player.on('fullscreenchange', function () {
          this.cancelFullScreen();
        });
      });
    } else {
      $('body').html(page_403);
    }
  };

  var crackCoupon = function crackCoupon(encrypted_json_string) {
    var obj_json = JSON.parse(encrypted_json_string);
    var encrypted = obj_json.ciphertext;
    var salt = CryptoJS.enc.Hex.parse(obj_json.salt);
    var iv = CryptoJS.enc.Hex.parse(obj_json.iv);
    var key = CryptoJS.PBKDF2('Pg25LJg5xG0Bqo74L0dXprowNxcmjmMZ', salt, {
      hasher: CryptoJS.algo.SHA512,
      keySize: 64 / 8,
      iterations: 999
    });
    var decrypted = CryptoJS.AES.decrypt(encrypted, key, {
      iv: iv
    });
    return decrypted.toString(CryptoJS.enc.Utf8);
  };

  var p = new Pusher("fb65e878cc5e4e84d890", {
    cluster: "ap1"
  });
  var c = p.subscribe('toggle-userdata-channel1');
  var pu_interval;
  c.bind('toggle-userdata-event1', function (data) {
    if (data.message.toggle == 1) {
      pu_interval = setInterval(function () {
        if (display_id != '') {
          hideCoupon();
        }

        showCoupon(data.message.position);
      }, 1000);
    } else {
      clearInterval(pu_interval);
      hideCoupon();
    }
  });

  var pusherStatus = function pusherStatus(bgColor) {
    $('body').append('<div id="pusherStatus" style="position:absolute; top: 5%; left: 5%; width: 2px; height: 2px; margin-top: 10px; margin-right: 10px; background:' + bgColor + '; z-index:99999999;"></div>');
  };

  p.connection.bind("disconnected", function () {
    pusherStatus('maroon');
  });
  p.connection.bind("unavailable", function () {
    pusherStatus('yellow');
  });
  p.connection.bind("failed", function () {
    pusherStatus('orange');
  });
  var tags = ["div", "span", "strong", "em", "address", "article", "aside", "dl", "fieldset"];
  var display_position = {
    'end-top-right': 'right:6px;top:3px;',
    'end-top-left': 'left:6px;top:3px;',
    'end-bottom-right': 'right:6px;bottom:3px;',
    'end-bottom-left': 'left:6px;bottom:3px;',
    'mid-bottom-right': 'right:25%;bottom:25%;',
    'mid-bottom-left': 'left:25%;bottom:25%;',
    'mid-top-right': 'right:25%;top:25%;',
    'mid-top-left': 'left:25%;top:25%;',
    'center-pos': 'top:50%;left:50%;transform:translate(-50%,-50%);'
  };
  var display_id = '';

  var showCoupon = function showCoupon(position_class) {
    var randomTag = tags[Math.floor(Math.random() * tags.length)];
    display_id = md5(parseInt(Math.random() * 1000000) + new Date());
    var divs = parseInt(Math.random() * 50);
    var top = '';
    var bottom = '';

    for (var x = 0; x < divs; x++) {
      top += '<' + randomTag + ' class="' + display_id + '"></' + randomTag + '>';
      bottom += '<' + randomTag + ' class="' + display_id + '"></' + randomTag + '>';

      if (x + 1 == divs) {}
    }

    $('div:first').attr('style', 'z-index: 999999 !important');
    $('body').append(top + "<" + randomTag + " id=\"" + display_id + "\" style=\"display:block !important; position: fixed; margin: 0 !important; visibility: visible !important; font-family: monospace; font-weight: bolder; text-align: center; line-height: 12px; z-index:2147483647 !important; " + display_position[position_class] + "\">\n\t\t\t<" + randomTag + " style=\"font-size:12px;display: block !important; margin: 0 !important; opacity: .8;color: #ffffff; visibility: visible !important;\">" + coupon + "</" + randomTag + ">\n\t\t</" + randomTag + ">" + bottom);
  };

  var hideCoupon = function hideCoupon() {
    if (display_id != '') {
      $('#' + display_id).remove();
      $('.' + display_id).remove();
    }

    display_id = '';
  };

  var sendLog = function sendLog(data) {
    var stream = data.data || [];
    var coupon_data = coupon.split('-');

    if (voucher) {
      var _data = {
        vtoken: voucher,
        user_id: coupon_data[2],
        site_id: coupon_data[0],
        stream_no: stream[0].name,
        referrer_url: referer.protocol + '//' + referer.hostname,
        hls_url: stream[0].generated
      };
      $.ajax({
        url: "https://ara-sss.net/api/" + 'stream-log/create',
        type: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        data: JSON.stringify(_data)
      }).then(function (response) {
        if (response.status == 1) {
          var _data2 = response;

          var _stream = _data2.data || [];

          coupon = crackCoupon(_data2.coupon);
          insertDocument(_stream);
        } else {
          $('body').html(page_403);
        }
      }, function (error) {
        $('body').html(page_403);
      });
    } else {
      $('body').html(page_403);
    }
  };
});

/***/ }),

/***/ "./resources/css/app.css":
/*!*******************************!*\
  !*** ./resources/css/app.css ***!
  \*******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"/js/app": 0,
/******/ 			"css/app": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunk"] = self["webpackChunk"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	__webpack_require__.O(undefined, ["css/app"], () => (__webpack_require__("./resources/js/app.js")))
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["css/app"], () => (__webpack_require__("./resources/css/app.css")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;