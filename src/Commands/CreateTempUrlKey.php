<?php

namespace Sausin\LaravelOvh\Commands;

use Illuminate\Console\Command;

class CreateTempUrlKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ovh:create-temp-url-key';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the temp url key, allowing the use of Storage::temporaryUrl()';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // generate the key with sha512sum of time()

        $this->info("read https://docs.ovh.com/gb/en/public-cloud/share_an_object_via_a_temporary_url/#generate-the-key for explanation of what we're doing here");

        $temp_url_key = hash('sha512', time());
        $client = new \GuzzleHttp\Client();

        // ( https://docs.ovh.com/gb/en/public-cloud/managing_tokens/ )
        // Request token creation
        //  curl -X POST ${OS_AUTH_URL}auth/tokens -H "Content-Type: application/json" -d ' { "auth": { "identity": { "methods": ["password"], "password": { "user": { "name": "'$OS_USERNAME'", "domain": { "id": "default" }, "password": "'$OS_PASSWORD'" } } }, "scope": { "project": { "name": "'$OS_TENANT_NAME'", "domain": { "id": "default" } } } } }' | python -mjson.tool

        $payload = [
            "auth" =>   [
                "identity"  => [
                    "methods"   =>  [
                        "password"
                    ],
                    "password" => [
                        "user"  =>  [
                            "name"      => config('filesystems.disks.ovh.user'),
                            "domain"    => [
                                "id"    => "default"
                            ],
                            "password" => config('filesystems.disks.ovh.pass')
                        ]
                    ]
                ],
                "scope" =>  [
                    "project" => [
                        "name" => config('filesystems.disks.ovh.tenantName'),
                        "domain" => [
                            "id" => "default"
                        ]
                    ]
                ]
            ],
        ];

        $this->info("getting auth token from ".config('filesystems.disks.ovh.server').'auth/tokens');

        $response = $client->request('POST', config('filesystems.disks.ovh.server').'auth/tokens', [
            'json'   =>  $payload
        ]);

        $data = json_decode($response->getBody());

        // Retrieve the token ID variables and publicURL endpoint
        foreach($data->token->catalog as $catalog):
            if ( $catalog->type == 'object-store' ):
                foreach($catalog->endpoints as $endpoint):
                    if ( $endpoint->region == config('filesystems.disks.ovh.region') ):
                        // $auth_token = $endpoint->id;
                        $endpoint = $endpoint->url;
                        break(2);
                    endif;
                endforeach;
            endif;
        endforeach;

        // export token=$(curl -is -X POST ${OS_AUTH_URL}auth/tokens -H "Content-Type: application/json" -d ' { "auth": { "identity": { "methods": ["password"], "password": { "user": { "name": "'$OS_USERNAME'", "domain": { "id": "default" }, "password": "'$OS_PASSWORD'" } } }, "scope": { "project": { "name": "'$OS_TENANT_NAME'", "domain": { "id": "default" } } } } }' | grep '^X-Subject-Token' | cut -d" " -f2)
        $headers = $response->getHeaders();
        $auth_token = $headers["X-Subject-Token"][0];


        // then define the temp-url-key header ( https://docs.ovh.com/gb/en/public-cloud/share_an_object_via_a_temporary_url/#generate-the-key )
        // curl -i -X POST \ -H "X-Account-Meta-Temp-URL-Key: 12345" \ -H "X-Auth-Token: abcdef12345" \ https://storage.sbg1.cloud.ovh.net/v1/AUTH_ProjectID

        $payload = [
            'headers'   =>  [
                'X-Account-Meta-Temp-URL-Key'   =>  $temp_url_key,
                'X-Auth-Token'                  =>  $auth_token
            ]
        ];
        $this->info("posting to ".$endpoint);

        $response = $client->request('POST', $endpoint, $payload);

        $this->alert("add this line to your .env file");
        $this->info("OVH_URL_KEY=".$temp_url_key.PHP_EOL);

    }



}
