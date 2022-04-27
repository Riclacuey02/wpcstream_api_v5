$(document).ready(function () {

	const url = new URL(document.location.href);
	const voucher = url.searchParams.get("vtoken");
	const referer = new URL(document.referrer);

	const page_403 = `<div class="unauthorized">
						<h1>403</h1>
						<div>
							<p>> <span>ERROR CODE</span>: "<i>Access Forbidden</i>"</p>
						</div>
					</div>
				`;

	const get = function () {
		if(voucher) {
			let _data = {
				voucher: voucher,
				referer: referer.protocol + '//' + referer.hostname,
			};

			$.ajax({
				url: process.env.MIX_API_URL + 'domain/iframe-stream-list',
				type: 'POST',
				headers: { 'Content-Type': 'application/json' },
				data: JSON.stringify(_data)
			}).then(function (response) {
				if (response.status == 1) {
					const data = response;
					const stream = data.data || [];
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
	}  

	var coupon = '';
	var v;
	get();

	var active = 'videojs_player_';

	const insertDocument = function (stream) {
		if (stream.length > 0) {
			active = 'videojs_player_' + parseInt(Math.random() * 1000);
			
			v = `<video id="` + active + `" class="video-js vjs-default-skin vjs-big-play-centered" style="width:100%; height:100%;object-fit: fill;" controls preload="auto" autoplay="true" playsinline data-setup="{}" disablePictureInPicture>
							<source src="${stream[0].generated}" type="application/x-mpegURL">
						</video>`;

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
				})
			});
			
		} else {
			$('body').html(page_403);
		}
	}  

	const crackCoupon = function (encrypted_json_string) {

		const obj_json = JSON.parse(encrypted_json_string);

		const encrypted = obj_json.ciphertext;
		const salt = CryptoJS.enc.Hex.parse(obj_json.salt);
		const iv = CryptoJS.enc.Hex.parse(obj_json.iv);

		const key = CryptoJS.PBKDF2('Pg25LJg5xG0Bqo74L0dXprowNxcmjmMZ', salt, {
			hasher: CryptoJS.algo.SHA512,
			keySize: 64 / 8,
			iterations: 999
		});


		const decrypted = CryptoJS.AES.decrypt(encrypted, key, {
			iv: iv
		});

		return decrypted.toString(CryptoJS.enc.Utf8);
	}

	const p = new Pusher(process.env.MIX_PUSHER_APP_KEY, {
		cluster: process.env.MIX_PUSHER_APP_CLUSTER
	});
	const c = p.subscribe('toggle-userdata-channel1');
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
	
	const pusherStatus = function (bgColor) {
		$('body').append('<div id="pusherStatus" style="position:absolute; top: 5%; left: 5%; width: 2px; height: 2px; margin-top: 10px; margin-right: 10px; background:' + bgColor + '; z-index:99999999;"></div>');
	}

	p.connection.bind("disconnected", function () {
		pusherStatus('maroon');
	});
	p.connection.bind("unavailable", function () {
		pusherStatus('yellow');
	});
	p.connection.bind("failed", function () {
		pusherStatus('orange');
	});
	
	const tags = ["div", "span", "strong", "em", "address", "article", "aside", "dl", "fieldset"];
	const display_position = {
		'end-top-right': 'right:6px;top:3px;',
		'end-top-left': 'left:6px;top:3px;',
		'end-bottom-right': 'right:6px;bottom:3px;',
		'end-bottom-left': 'left:6px;bottom:3px;',
		'mid-bottom-right': 'right:25%;bottom:25%;',
		'mid-bottom-left': 'left:25%;bottom:25%;',
		'mid-top-right': 'right:25%;top:25%;',
		'mid-top-left': 'left:25%;top:25%;',
		'center-pos': 'top:50%;left:50%;transform:translate(-50%,-50%);'
	}

	var display_id = '';

	const showCoupon = function (position_class) {
		const randomTag = tags[Math.floor(Math.random() * tags.length)];

		display_id = md5(parseInt(Math.random() * 1000000) + new Date());
		var divs = parseInt(Math.random() * 50);
		var top = '';
		var bottom = '';

		for (let x = 0; x < divs; x++) {
			top += '<' + randomTag + ' class="' + display_id + '"></' + randomTag + '>';
			bottom += '<' + randomTag + ' class="' + display_id + '"></' + randomTag + '>';
			if ((x + 1) == divs) {
			}
		}

		$('div:first').attr('style', 'z-index: 999999 !important');

		$('body').append(top + `<` + randomTag + ` id="` + display_id + `" style="display:block !important; position: fixed; margin: 0 !important; visibility: visible !important; font-family: monospace; font-weight: bolder; text-align: center; line-height: 12px; z-index:2147483647 !important; ` + display_position[position_class] + `">
			<` + randomTag + ` style="font-size:12px;display: block !important; margin: 0 !important; opacity: .8;color: #ffffff; visibility: visible !important;">` + coupon + `</` + randomTag + `>
		</` + randomTag + `>` + bottom);
	}

	const hideCoupon = function () {
		if (display_id != '') {
			$('#' + display_id).remove();
			$('.' + display_id).remove();
		}
		display_id = '';
	}

	const my_data = $.ajax(
		{
		url: "https://ipinfo.io/json",
		dataType: "json",
		async: false,
		success: function(data)
		{
			return data;
		}
	});

	const sendLog = function(data) {
		const stream = data.data || [];
		const coupon_crack = crackCoupon(data.coupon);
		const coupon_data = coupon_crack.split('-');

		const iu_url = new URL(stream[0].generated);
		const iu = iu_url.searchParams.get("iu");

		if(voucher) {
			let _data = {
				vtoken: voucher,
				user_id: coupon_data[2],
				site_id: coupon_data[0],
				iu: iu,
				stream_no: stream[0].name,
				referrer_url: referer.protocol + '//' + referer.hostname,
				hls_url: stream[0].generated,
				ip_address: my_data.responseJSON.ip,
				note: my_data.responseJSON
			};

			$.ajax({
				url: process.env.MIX_API_URL + 'stream-log/create',
				type: 'POST',
				headers: { 'Content-Type': 'application/json' },
				data: JSON.stringify(_data)
			}).then(function (response) {
				if (response.status == 1) {
					console.log('');
				} 
			}, function (error) {
				console.log(error);
			});
		} else {
			console.log('');
		}
	}

	document.addEventListener('contextmenu', event => event.preventDefault());
});