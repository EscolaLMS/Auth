<?php

namespace EscolaLms\Auth\Enums;

use EscolaLms\Core\Enums\BasicEnum;

class AuthPermissionsEnum extends BasicEnum
{
    const USER_MANAGE = 'user_manage';
    //
    const USER_CREATE      = 'user_create';
    const USER_DELETE      = 'user_delete';
    const USER_DELETE_SELF = 'user_delete-self';
    const USER_UPDATE      = 'user_update';
    const USER_UPDATE_SELF = 'user_update-self';
    const USER_READ        = 'user_read';
    const USER_READ_SELF   = 'user_read-self';
    const USER_READ_OWNED  = 'user_read-owned';
    const USER_LIST        = 'user_list';
    const USER_LIST_OWNED  = 'user_list-owned';
    const USER_VERIFY_ACCOUNT = 'user_verify-account';
    //
    const USER_GROUP_CREATE        = 'user_group_create';
    const USER_GROUP_DELETE        = 'user_group_delete';
    const USER_GROUP_LIST          = 'user_group_list';
    const USER_GROUP_LIST_SELF     = 'user_group_list-self'; // list groups Student belongs to / Tutor courses belong to
    const USER_GROUP_MEMBER_ADD    = 'user_group-member_add';
    const USER_GROUP_MEMBER_REMOVE = 'user_group-member_remove';
    const USER_GROUP_READ          = 'user_group_read';
    const USER_GROUP_READ_SELF     = 'user_group_read-self'; // get details about group Student belongs to / Tutor courses belong to
    const USER_GROUP_UPDATE        = 'user_group_update';
    //
    const USER_INTEREST_UPDATE      = 'user_interest_update';
    const USER_INTEREST_UPDATE_SELF = 'user_interest_update-self';
    //
    const USER_SETTING_UPDATE      = 'user_setting_update';
    const USER_SETTING_UPDATE_SELF = 'user_setting_update-self';
}
