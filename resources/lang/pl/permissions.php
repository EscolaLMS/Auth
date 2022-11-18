<?php

use EscolaLms\Auth\Enums\AuthPermissionsEnum;

return [
    AuthPermissionsEnum::USER_MANAGE => 'Zarządzaj użytkownikiem',
    AuthPermissionsEnum::USER_CREATE => 'Utwórz użytkownika',
    AuthPermissionsEnum::USER_DELETE => 'Usuń użytkownika',
    AuthPermissionsEnum::USER_DELETE_SELF => 'Usuń konto',
    AuthPermissionsEnum::USER_UPDATE => 'Aktulizuj użytkownika',
    AuthPermissionsEnum::USER_UPDATE_SELF => 'Aktualizuj konto',
    AuthPermissionsEnum::USER_READ => 'Wyświetl użytkownika',
    AuthPermissionsEnum::USER_READ_SELF => 'Wyświetl swoje konto',
    AuthPermissionsEnum::USER_LIST => 'Lista użytkowników',
    AuthPermissionsEnum::USER_LIST_OWNED => 'Lista swoich użytkowników',
    AuthPermissionsEnum::USER_VERIFY_ACCOUNT => 'Weryfikuj konto',
    AuthPermissionsEnum::USER_GROUP_CREATE => 'Utwórz grupę',
    AuthPermissionsEnum::USER_GROUP_DELETE => 'Usuń grupę',
    AuthPermissionsEnum::USER_GROUP_LIST => 'Lista grup',
    AuthPermissionsEnum::USER_GROUP_LIST_SELF => 'Lista swoich grup',
    AuthPermissionsEnum::USER_GROUP_MEMBER_ADD => 'Dodaj członka grupy',
    AuthPermissionsEnum::USER_GROUP_MEMBER_REMOVE => 'Usuń członka grupy',
    AuthPermissionsEnum::USER_GROUP_READ => 'Wyświetl grupę',
    AuthPermissionsEnum::USER_GROUP_READ_SELF => 'Wyświetl swoją grupę',
    AuthPermissionsEnum::USER_GROUP_UPDATE => 'Aktualizuj grupę',
    AuthPermissionsEnum::USER_INTEREST_UPDATE => 'Aktualizuj zainteresowanie',
    AuthPermissionsEnum::USER_INTEREST_UPDATE_SELF => 'Aktualizuj swoje zainteresowanie',
    AuthPermissionsEnum::USER_SETTING_UPDATE => 'Aktualizuj ustawienie',
    AuthPermissionsEnum::USER_SETTING_UPDATE_SELF => 'Aktualizuj swoje ustawienie',
];