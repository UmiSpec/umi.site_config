<?php

$INFO = Array();

$INFO['verison'] = "1.0.0.0";

$INFO['name'] = "site_config";
$INFO['title'] = "Настройки сайта";
$INFO['description'] = "Модуль Настройки сайта by UmiSpec";
$INFO['filename'] = "modules/site_config/class.php";
$INFO['config'] = "0";
$INFO['ico'] = "config";
$INFO['default_method'] = "get";
$INFO['default_method_admin'] = "view";

$INFO['func_perms'] = "Functions, that should have their own permissions.";
$INFO['func_perms/get'] = "Чтение настроек";

$SQL_INSTALL = Array();

$COMPONENTS = array();

$COMPONENTS[0] = "./classes/modules/site_config/.htaccess";
$COMPONENTS[1] = "./classes/modules/site_config/__admin.php";
$COMPONENTS[2] = "./classes/modules/site_config/class.php";
$COMPONENTS[3] = "./classes/modules/site_config/i18n.en.php";
$COMPONENTS[4] = "./classes/modules/site_config/i18n.php";
$COMPONENTS[5] = "./classes/modules/site_config/lang.php";
$COMPONENTS[6] = "./classes/modules/site_config/permissions.php";
$COMPONENTS[7] = "./classes/modules/site_config/events.php";

/*Создание типа данных*/

$hierarchyTypes = umiHierarchyTypesCollection::getInstance();
$hierarchyType = $hierarchyTypes->getTypeByName('site_config', 'site_config');

if(!$hierarchyType) {
    $type_id = $hierarchyTypes->addType('site_config', 'Настройки сайта', 'site_config');
} else {
    $type_id = $hierarchyType->getId();
}

$objectTypes = umiObjectTypesCollection::getInstance();
$objectTypeId = $objectTypes->getTypeByHierarchyTypeId($type_id);

if(!$objectTypeId) {
    $objectTypeId = $objectTypes->addType(0, 'Настройки сайта');
    $objectType = $objectTypes->getType($objectTypeId);

    $objectType->setHierarchyTypeId($type_id);
    $objectType->setIsGuidable(true);
    $objectType->commit();
} else {
    $objectType = $objectTypes->getType($objectTypeId);
}

$group = $objectType->getFieldsGroupByName('system_properties', true);

if(!$group) {
    $group_id = $objectType->addFieldsGroup('system_properties', 'Системные свойства', true, false);
    $group = $objectType->getFieldsGroupByName('system_properties');
}

$fields = $group->getFields();

$fieldDomain = false;
$fieldLang = false;

if($fields) {
    foreach($fields as $field) {
        $fieldName = $field->getName();

        if($fieldName == 'domain_id') {
            $fieldDomain = $field;
            continue;
        } elseif($fieldName == 'lang_id') {
            $fieldLang = $field;
            continue;
        }
    }
}

$fieldsCollection = umiFieldsCollection::getInstance();

$fieldTypesCollection = umiFieldTypesCollection::getInstance();
$typeInt = $fieldTypesCollection->getFieldTypeByDataType('int');
$restriction = baseRestriction::find('systemDomain', $typeInt);

$bObjectTypeCommit = false;

if(!$fieldDomain) {
    $domainFieldId = $fieldsCollection->addField('domain_id', 'Домен', $typeInt->getId(), false, true);
    $fieldDomain = $fieldsCollection->getField($domainFieldId);

    $fieldDomain->setIsSystem(true);
    $fieldDomain->setRestrictionId($restriction);
    $fieldDomain->commit();

    $group->attachField($domainFieldId);

    $bObjectTypeCommit = true;
} else {
    $bCommmit = false;

    if($fieldDomain->getFieldTypeId() != $typeInt->getId()) {
        $fieldDomain->setFieldTypeId($typeInt->getId());
        $bCommmit = true;
    }

    if($fieldDomain->getRestrictionId() !== $restriction) {
        $fieldDomain->setRestrictionId($restriction);
        $bCommmit = true;
    }

    if($bCommmit)
        $fieldDomain->commit();
}

if(!$fieldLang) {
    $langFieldId = $fieldsCollection->addField('lang_id', 'Языковая версия', $typeInt->getId(), false, true);
    $fieldLang = $fieldsCollection->getField($langFieldId);

    $fieldLang->setIsSystem(true);
    $fieldLang->commit();

    $group->attachField($langFieldId);

    $bObjectTypeCommit = true;
} else {
    if($fieldLang->getFieldTypeId() != $typeInt->getId()) {
        $fieldLang->setFieldTypeId($typeInt->getId());
        $fieldLang->commit();
    }
}

if($bObjectTypeCommit) {
    $objectType->commit();
}

/*Устанавливаем права по умолчанию для гостя*/

$permissions = permissionsCollection::getInstance();

$guestId = $permissions->getGuestId();

if(!$permissions->isAllowedMethod($guestId, 'site_config', $INFO['default_method'])) {
    $permissions->setModulesPermissions($guestId, 'site_config', $INFO['default_method']);
}
?>