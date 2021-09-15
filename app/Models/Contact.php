<?php

namespace App\Models;

use App\Klaviyo\KlaviyoHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Klaviyo\Exception\KlaviyoApiException;
use Klaviyo\Exception\KlaviyoException;

/**
 * App\Models\Contact
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Contact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Contact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Contact query()
 * @method static \Illuminate\Database\Eloquent\Builder|Contact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contact whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contact whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contact wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contact whereUserId($value)
 * @mixin \Eloquent
 */
class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'user_id',
        'klaviyo_id'
    ];

    /**
     * @return \string[][]
     */
    public static function getValidationRules()
    {
        return [
            'first_name' => ['required', 'string', 'min:1', 'max:20'],
            'last_name' => ['required', 'string', 'min:1', 'max:20'],
            'email' => ['required', 'string', 'email', 'unique:contacts'],
            'phone' => ['required', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10'],
        ];
    }

    /**
     * @param array $data
     * @param bool $needValidation
     * @return Contact|array|Model|\string[][]
     */
    public static function createForAuthUser(array $data, bool $needValidation = true)
    {

        if($needValidation) {
            $validation = Validator::make($data, self::getValidationRules());

            if($validation->fails())  {
                return $validation->errors()->all();
            }
        }

        $klaviyoHelper = new KlaviyoHelper();
        try {
            $newMemberId = $klaviyoHelper->addMemberAndReturnMemberId([
                '$last_name' => $data['first_name'],
                '$first_name' => $data['last_name'],
                '$phone_number' => $data['phone'],
                '$email' => $data['email'],
            ]);
            return self::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'user_id' => Auth::user()->id,
                'klaviyo_id' => $newMemberId
            ]);
        } catch (KlaviyoApiException | KlaviyoException $e) {
            return ['api' => ['Synchronization problems, please try later']];
        }
    }

    /**
     * @param array $contacts
     * @return array
     */
    public static function createContactsFromArrayForAuthUser(array $contacts)
    {
        $validContacts = [];
        foreach ($contacts as $contact) {
            $validation = Validator::make($contact, self::getValidationRules());

            if(!$validation->fails())  {
                $validContacts[] = $contact;
            }
        }

        $klaviyoHelper = new KlaviyoHelper();
        $addedContacts = $klaviyoHelper->addMembersFromArray($validContacts);

        $validContactsCollection = collect($validContacts);

        foreach ($addedContacts as $addedContact) {
            $originalContact = $validContactsCollection->where('email', $addedContact['email'])->first();
            self::create([
                'first_name' => $originalContact['first_name'],
                'last_name' => $originalContact['last_name'],
                'email' => $originalContact['email'],
                'phone' => $originalContact['phone'],
                'user_id' => Auth::user()->id,
                'klaviyo_id' => $addedContact['id']
            ]);
        }

        return [
            'total' => count($contacts),
            'added' => count($addedContacts),
        ];
    }

    /**
     * @param int $id
     * @return bool
     */
    public static function deleteById(int $id)
    {
        $object = self::getByIdForAuthUser($id);
        if($object) {
            $klaviyoHelper = new KlaviyoHelper();
            $klaviyoHelper->removeMemberByEmail($object->email);
            $object->delete();
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param int $id
     * @return Contact|\Illuminate\Database\Eloquent\Builder|Model|object|null
     */
    public static function getByIdForAuthUser(int $id)
    {
        return self::where('user_id', Auth::user()->id)->where('id', $id)->first();
    }

    /**
     * @param array $data
     */
    public function updateFromRequestData(array $data)
    {
        $klaviyoHelper = new KlaviyoHelper();
        $klaviyoHelper->client->profiles->updateProfile( $this->klaviyo_id, [
            '$last_name' => $data['first_name'],
            '$first_name' => $data['last_name'],
            '$phone_number' => $data['phone'],
            '$email' => $data['email'],
        ]);


        $this->first_name = $data['first_name'];
        $this->last_name = $data['last_name'];
        $this->email = $data['email'];
        $this->phone = $data['phone'];

        $this->save();
    }
}
