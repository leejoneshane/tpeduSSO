<?php
/*!
* Hybridauth
* https://hybridauth.github.io | https://github.com/hybridauth/hybridauth
*  (c) 2017 Hybridauth authors | https://hybridauth.github.io/license.html
*/

namespace Hybridauth\Provider;

use Hybridauth\Adapter\OAuth2;
use Hybridauth\Data;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\User;

/**
 * Tpedu OAuth2 provider adapter.
 */
class Tpedu extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    protected $scope = 'profile';

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://ldap.tp.edu.tw/api/v2/';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://ldap.tp.edu.tw/oauth/authorize';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://ldap.tp.edu.tw/oauth/token';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://github.com/leejoneshane/tpeduSSO/';

    /**
     * Currently authenticated user.
     */
    protected $userId = null;

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest('profile');

        $data = new Data\Collection($response);
        $org = $data->exists('organization') ? $data->get('organization') : null;

        if ($data->exists('error') || !$org) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.'.$data->get('error'));
        } elseif (!property_exists($org, 'meps') && $data->get('role') == '家長') {
            throw new UnexpectedApiResponseException('Only our School teachers and students can login!');
        }

        $userProfile = new User\Profile();

        if ($data->get('role') == '學生') {
            $userProfile->identifier = $data->get('studentId');
            $userProfile->data['groups'] = ['學生', $data->get('class')];
        } else {
            $userProfile->identifier = $data->get('teacherId');
            $userProfile->data['groups'][] = '教師';
            $units = $data->get('unit')->meps;
            foreach ($units as $dept) {
                $userProfile->data['groups'][] = $dept->name;
            }
        }
        $userProfile->displayName = $data->get('name');
        $userProfile->gender = $data->get('gender');
        $userProfile->language = 'zh_TW';
        $userProfile->phone = $data->get('mobile');
        $userProfile->email = $data->get('email');
        $userProfile->emailVerified = ($data->get('email_verified') == 'true') ? $userProfile->email : '';

        return $userProfile;
    }
}
