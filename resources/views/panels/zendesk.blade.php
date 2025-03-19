@if(config('static.area') == 'User')

    <!-- Start of deartime Zendesk Widget script -->
    <script id="ze-snippet" src="https://static.zdassets.com/ekr/snippet.js?key=1de2769a-0722-4ee1-a70e-990ecd327795"> </script>
    @auth
        <script type="text/javascript">
            @php

                $user = auth()->user();
                $payload = [
                  'name' => $user->name ,
                  'email' => $user->email,
                  'iat' => time(),
                  'exp' => time()+(86400),
                  'external_id' => $user->uuid
                ];
                $token = \Firebase\JWT\JWT::encode($payload,config('static.zenDeskSecret'));
            @endphp
                window.zESettings = {webWidget: {authenticate: {chat: {jwtFn: function(callback) {callback('{{$token}}');}}}}};
        </script>
    @endauth
    <!-- End of deartime Zendesk Widget script -->

@endif
