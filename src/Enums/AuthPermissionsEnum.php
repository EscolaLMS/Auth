<?php

namespace EscolaLms\Auth\Enums;

use EscolaLms\Core\Enums\BasicEnum;

class AuthPermissionsEnum extends BasicEnum
{
    const USER_MANAGE = 'user manage';
    //
    const USER_CREATE      = 'user create';
    const USER_DELETE      = 'user delete';
    const USER_DELETE_SELF = 'user delete self';
    const USER_UPDATE      = 'user update';
    const USER_UPDATE_SELF = 'user update self';
    const USER_READ        = 'user read';
    const USER_READ_SELF   = 'user read self';
    const USER_READ_OWNED  = 'user read owned';
    const USER_LIST        = 'user list any';
    const USER_LIST_OWNED  = 'user list owned';
    //
    const USER_GROUP_CREATE        = 'user group create';
    const USER_GROUP_DELETE        = 'user group delete';
    const USER_GROUP_LIST          = 'user group list';
    const USER_GROUP_LIST_SELF     = 'user group list self'; // list groups Student belongs to / Tutor courses belong to
    const USER_GROUP_MEMBER_ADD    = 'user group member add';
    const USER_GROUP_MEMBER_REMOVE = 'user group member remove';
    const USER_GROUP_READ          = 'user group read';
    const USER_GROUP_READ_SELF     = 'user group read self'; // get details about group Student belongs to / Tutor courses belong to
    const USER_GROUP_UPDATE        = 'user group update';
    //
    const USER_INTEREST_UPDATE      = 'user interest update';
    const USER_INTEREST_UPDATE_SELF = 'user interest update self';
    //
    const USER_SETTING_UPDATE      = 'user setting update';
    const USER_SETTING_UPDATE_SELF = 'user setting update self';
}
