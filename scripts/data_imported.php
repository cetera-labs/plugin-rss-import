<?php
include_once('common_bo.php');
global $application;
if (!$application->getUser()->isAdmin()) die('access denied');

$data = array();

if (!isset($_REQUEST['sort'])) $_REQUEST['sort'] = 'id';
if (!isset($_REQUEST['dir'])) $_REQUEST['dir'] = 'ASC';

$r = $application->getConn()->executeQuery('SELECT id, idcat, guid, url, material_id, created_at FROM rss_import_imported_materials ORDER BY ' . $_REQUEST['sort'] . ' ' . $_REQUEST['dir']);
while ($f = $r->fetch()) {


    $catalog = \Cetera\Catalog::getById($f['idcat']);
    $material = \Cetera\Material::getById($f['material_id'], $catalog->getMaterialsObjectDefinition());
    $name = $material->getDynamicField('name');

    $data[] = [
        'id' => $f['id'],
        'guid' => $f['guid'],
        'url' => $f['url'],
        'material' => "$name [$material->id]",
        'catalog' => $catalog->getName(),
        'created_at' => $f['created_at']
    ];
}


echo json_encode(array(
    'success' => true,
    'rows' => $data
));
