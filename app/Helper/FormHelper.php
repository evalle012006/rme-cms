<?php
const PROFILE_STORAGE_PATH = 'profile-image/';
const FEATURE_STORAGE_PATH = 'feature-image/';
const COMPANY_LOGO_STORAGE_PATH = 'company-logo/';

function setFormComponentValue($objOrArray, $fieldKey, $default = null)
{
    $valueToSend = null;

    if (count(old()) > 0 || is_null($objOrArray)) {
        $objOrArray = old();
    }
    if (is_array($objOrArray)) {
        if (isset($objOrArray[$fieldKey])) {
            $valueToSend = $objOrArray[$fieldKey];
        }
    } elseif (is_object($objOrArray)) {
        $valueToSend = $objOrArray->{$fieldKey};
    }

    return is_null($valueToSend) ? $default : $valueToSend;
}



