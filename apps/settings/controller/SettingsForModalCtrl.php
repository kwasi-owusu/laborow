<?php
require_once '../../../settings/model/SettingsForModal.php';

class SettingsForModalCtrl extends SettingsForModal
{

    public static function countries()
    {
        $getRst = SettingsForModal::allCountriesForModal();

        return $getRst;
    }
}
