<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class CjtestComponent extends Component
{
    protected $listeners = ['refreshCoinJoinsTestNetComponent' => '$refresh'];

    public function render()
    {
        try {
            $cjTest = Http::get('https://wasabiwallet.co/WabiSabi/human-monitor')->json()['roundStates'];
            $statusUrl = 'https://wasabiwallet.co/WabiSabi/status';

            $headers = [
                'Accept: application/json',
                'Content-Type: application/json-patch+json',
            ];
            foreach ($cjTest as $key => $value) {
                $data = [
                    'roundCheckpoints' => [
                        [
                            'roundId' => $value['roundId'],
                            'stateId' => 0,
                        ],
                    ],
                ];

                $ch = curl_init($statusUrl);

                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $response = curl_exec($ch);

                if (curl_errno($ch)) {
                    echo 'Curl error: ' . curl_error($ch);
                }

                curl_close($ch);
                if(isset(json_decode($response, true)['roundStates'][0]['coinjoinState']['events'][0]['roundParameters']['allowedInputAmounts']))
                {
                    $cjTest[$key]['allowedInputAmounts'] = json_decode($response, true)['roundStates'][0]['coinjoinState']['events'][0]['roundParameters']['allowedInputAmounts'];
                }
            }
            $nodata = null;
        } catch (\Exception $th) {
            $nodata = 'No data from server!';
            $cjTest = null;
        }
        return view('livewire.cjtest-component',['cjTest' => $cjTest,'nodata' => $nodata]);
    }
}
