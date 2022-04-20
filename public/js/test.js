
$(document).ready(function () {
    console.log(process.env.MIX_API_URL);
    function init_streams() {
        if (document.referrer && typeof USER_ID != 'undefined' && typeof SITE_ID != 'undefined') {
            dnote += `G2:N,`;

            var domain = new URL(document.referrer);
            let _data = {
                domain: domain.protocol + '//' + domain.hostname
            };

            _data['user_id'] = USER_ID;
            _data['site'] = SITE_ID;
            _data['vtoken'] = VTOKEN;

            $.ajax({
                url: API_URL + 'domain/iframe-stream-list',
                type: 'POST',
                headers: { 'Content-Type': 'application/json' },
                data: JSON.stringify(_data)
            }).then(function (response) {
                dnote += `G3:S,`;
                if (response.status == 1) {
                    dnote += `G4:${response.status},`;
                    streams = response.data || [];
                    if (streams.length > 0) {
                        dnote += `G5:${streams.length},`;
                        changeRandomStream(streams);
                        return;
                    }
                }
                dnote += `G6:N,`;
                $('div#player').html(page_403);
            }, function (error) {
                dnote += `G7:F,`;
                console.log('Error occured', error)
            });
        } else {
            dnote += `G8:N,`;
            $('div#player').html(page_403);
        }
    }  

    init_streams();
});