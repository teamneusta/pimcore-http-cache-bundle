<?php declare(strict_types=1);

return Pimcore\Model\DataObject\ClassDefinition::__set_state([
    'dao' => null,
    'id' => 'test_data_object',
    'name' => 'TestDataObject',
    'description' => '',
    'creationDate' => 0,
    'modificationDate' => 1685448671,
    'userOwner' => 1,
    'userModification' => 1,
    'parentClass' => '',
    'implementsInterfaces' => '',
    'listingParentClass' => '',
    'useTraits' => '',
    'listingUseTraits' => '',
    'encryption' => false,
    'encryptedTables' => [
    ],
    'allowInherit' => false,
    'allowVariants' => false,
    'showVariants' => false,
    'fieldDefinitions' => [
    ],
    'layoutDefinitions' => Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state([
        'name' => 'pimcore_root',
        'type' => null,
        'region' => null,
        'title' => null,
        'width' => 0,
        'height' => 0,
        'collapsible' => false,
        'collapsed' => false,
        'bodyStyle' => null,
        'datatype' => 'layout',
        'permissions' => null,
        'children' => [
            0 => Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state([
                'name' => 'Layout',
                'type' => null,
                'region' => null,
                'title' => '',
                'width' => null,
                'height' => null,
                'collapsible' => false,
                'collapsed' => false,
                'bodyStyle' => '',
                'datatype' => 'layout',
                'permissions' => null,
                'children' => [
                    0 => Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state([
                        'name' => 'content',
                        'title' => 'Content',
                        'tooltip' => '',
                        'mandatory' => true,
                        'noteditable' => false,
                        'index' => false,
                        'locked' => false,
                        'style' => '',
                        'permissions' => null,
                        'datatype' => 'data',
                        'fieldtype' => 'input',
                        'relationType' => false,
                        'invisible' => false,
                        'visibleGridView' => false,
                        'visibleSearch' => false,
                        'blockedVarsForExport' => [
                        ],
                        'width' => null,
                        'defaultValue' => null,
                        'columnLength' => 190,
                        'regex' => '',
                        'regexFlags' => [
                        ],
                        'unique' => false,
                        'showCharCount' => false,
                        'defaultValueGenerator' => '',
                    ]),
                ],
                'locked' => false,
                'blockedVarsForExport' => [
                ],
                'fieldtype' => 'panel',
                'layout' => null,
                'border' => false,
                'icon' => null,
                'labelWidth' => 100,
                'labelAlign' => 'left',
            ]),
        ],
        'locked' => false,
        'blockedVarsForExport' => [
        ],
        'fieldtype' => 'panel',
        'layout' => null,
        'border' => false,
        'icon' => null,
        'labelWidth' => 100,
        'labelAlign' => 'left',
    ]),
    'icon' => '',
    'previewUrl' => '',
    'group' => '',
    'showAppLoggerTab' => false,
    'linkGeneratorReference' => '',
    'previewGeneratorReference' => '',
    'compositeIndices' => [
    ],
    'generateTypeDeclarations' => true,
    'showFieldLookup' => false,
    'propertyVisibility' => [
        'grid' => [
            'id' => true,
            'key' => false,
            'path' => true,
            'published' => true,
            'modificationDate' => true,
            'creationDate' => true,
        ],
        'search' => [
            'id' => true,
            'key' => false,
            'path' => true,
            'published' => true,
            'modificationDate' => true,
            'creationDate' => true,
        ],
    ],
    'enableGridLocking' => false,
    'deletedDataComponents' => [
    ],
    'blockedVarsForExport' => [
    ],
    'activeDispatchingEvents' => [
    ],
]);
