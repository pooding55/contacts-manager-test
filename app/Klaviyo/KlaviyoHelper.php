<?php
namespace App\Klaviyo;

use Illuminate\Support\Carbon;
use Klaviyo\Exception\KlaviyoApiException;
use Klaviyo\Exception\KlaviyoException;
use Klaviyo\Klaviyo;
use Klaviyo\Model\EventModel;

class KlaviyoHelper
{
    /**
     * @var Klaviyo
     */
    public Klaviyo $client;

    public function __construct()
    {
        $this->connect();
    }

    /**
     *
     */
    public function connect()
    {
        $client = new Klaviyo( env('KLAVIYO_PRIVATE_API_KEY'), env('KLAVIYO_PUBLIC_API_KEY') );
        $this->client = $client;
    }


    /**
     * @throws KlaviyoException
     * @throws KlaviyoApiException
     */
    public function addMemberAndReturnMemberId(array $data)
    {
        $profile = $this->createProfile($data);

        return $this->client->lists->addMembersToList(  env('KLAVIYO_LIST_ID'), [$profile])[0]['id'];
    }

    /**
     * @param $profileData
     * @return \Klaviyo\Model\ProfileModel
     * @throws KlaviyoException
     * @throws KlaviyoApiException
     */
    public function createProfile($profileData)
    {
        $profile = new \Klaviyo\Model\ProfileModel($profileData);
        $this->client->publicAPI->identify( $profile, true );

        return $profile;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function addMembersFromArray(array $data)
    {
        $profiles = [];
        foreach ($data as $item) {
            try {
                $profiles[] = $this->createProfile([
                    '$last_name' => $item['first_name'],
                    '$first_name' => $item['last_name'],
                    '$phone_number' => $item['phone'],
                    '$email' => $item['email'],
                ]);
            } catch (KlaviyoApiException | KlaviyoException $e) {
            }
        }

        return $this->client->lists->addMembersToList(  env('KLAVIYO_LIST_ID'), $profiles);
    }

    /**
     * @param string $email
     */
    public function removeMemberByEmail(string $email)
    {
        $this->client->lists->removeMembersFromList(  env('KLAVIYO_LIST_ID'), [
            $email
        ]);
    }

    /**
     * @throws KlaviyoException
     */
    public function trackEvent($eventName, $userEmail)
    {
        $event = new EventModel(
            [
                'event' => $eventName,
                'customer_properties' => [
                    '$email' => $userEmail
                ],
                'properties' => [
                    'Date' => Carbon::now()->toDateTimeString()
                ]
            ]
        );

        $this->client->publicAPI->track( $event, true );
    }
}
