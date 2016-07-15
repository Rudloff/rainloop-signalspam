<?php
//Should we use a namespace?

class SignalSpamPlugin extends \RainLoop\Plugins\AbstractPlugin
{
    /**
     * @return void
     */
    public function init()
    {
        $this->addHook('filter.action-params', 'sendToSignalSpam');
    }

    /**
     * @return array
     */
    public function configMapping()
    {
        $oUser = \RainLoop\Plugins\Property::NewInstance('user')->SetLabel('Signal Spam username');
        $oPass = \RainLoop\Plugins\Property::NewInstance('pass')->SetLabel('Signal Spam password');

        return array($oUser, $oPass);
    }

    public function sendToSignalSpam($sMethodName, $aCurrentActionParams)
    {
        //We don't use the Guzzle lib packaged in RainLoop because it throwes this error:
        //Call to undefined function GuzzleHttp\\deprecation_proxy()
        require_once __DIR__.'/vendor/autoload.php';

        if ($sMethodName == 'DoMessageMove') {
            $actions = $this->Manager()->Actions();
            $oAccount = $actions->getAccountFromToken();
            $spamFolder = $actions->SettingsProvider(true)->Load($oAccount)->GetConf('SpamFolder', '');

            if ($aCurrentActionParams['ToFolder'] == $spamFolder) {
                $oAccount->IncConnectAndLoginHelper($actions->Plugins(), $actions->MailClient(), $actions->Config());
                $mailClient = $actions->MailClient();
                $aUids = explode(',', $aCurrentActionParams['Uids']);
                foreach ($aUids as $iUid) {
                    $mailClient->MessageMimeStream(function ($rResource) {
                        if (\is_resource($rResource)) {
                            $client = new \GuzzleHttp\Client();
                            $mailContent = stream_get_contents($rResource);
                            $res = $client->request(
                                'POST',
                                'https://www.signal-spam.fr/api/signaler',
                                array(
                                    'auth' => array(
                                        $this->Config()->Get('plugin', 'user', ''),
                                        $this->Config()->Get('plugin', 'pass', '')
                                    ),
                                    'multipart'=>array(
                                        array(
                                            'name'=>'message',
                                            'contents'=>base64_encode($mailContent)
                                        )
                                    )
                                )
                            );
                        }
                    }, $aCurrentActionParams['FromFolder'], intval($iUid));
                }
            }
        }
    }
}
