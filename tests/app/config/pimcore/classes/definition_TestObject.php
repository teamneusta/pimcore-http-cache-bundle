<?php declare(strict_types=1);

/**
 * Inheritance: no
 * Variants: no.
 *
 * Fields Summary:
 * - content [input]
 * - related [manyToManyObjectRelation]
 */

return Pimcore\Model\DataObject\ClassDefinition::__set_state([
    'dao' => null,
    'id' => 'test_object',
    'name' => 'TestObject',
    'description' => '',
    'creationDate' => 0,
    'modificationDate' => 1754659392,
    'userOwner' => 58,
    'userModification' => 58,
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
                'width' => '',
                'height' => '',
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
                        'mandatory' => false,
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
                        'width' => '',
                        'defaultValue' => null,
                        'columnLength' => 190,
                        'regex' => '',
                        'regexFlags' => [
                        ],
                        'unique' => false,
                        'showCharCount' => false,
                        'defaultValueGenerator' => '',
                    ]),
                    1 => Pimcore\Model\DataObject\ClassDefinition\Data\ManyToManyObjectRelation::__set_state([
                        'name' => 'related',
                        'title' => 'Related',
                        'tooltip' => '',
                        'mandatory' => false,
                        'noteditable' => false,
                        'index' => false,
                        'locked' => false,
                        'style' => '',
                        'permissions' => null,
                        'datatype' => 'data',
                        'fieldtype' => 'manyToManyObjectRelation',
                        'relationType' => true,
                        'invisible' => false,
                        'visibleGridView' => false,
                        'visibleSearch' => false,
                        'blockedVarsForExport' => [
                        ],
                        'classes' => [
                            0 => [
                                'classes' => 'TestObject',
                            ],
                        ],
                        'pathFormatterClass' => '',
                        'width' => '',
                        'height' => '',
                        'maxItems' => null,
                        'visibleFields' => [
                        ],
                        'allowToCreateNewObject' => false,
                        'optimizedAdminLoading' => false,
                        'enableTextSelection' => false,
                        'visibleFieldDefinitions' => [
                        ],
                    ]),
                ],
                'locked' => false,
                'blockedVarsForExport' => [
                ],
                'fieldtype' => 'panel',
                'layout' => null,
                'border' => false,
                'icon' => '',
                'labelWidth' => 0,
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
