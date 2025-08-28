<?php
include_once('common_bo.php');
global $application;
if (!$application->getUser()->isAdmin()) die('access denied');

$data = array();

if (!isset($_REQUEST['sort'])) $_REQUEST['sort'] = 'id';
if (!isset($_REQUEST['dir'])) $_REQUEST['dir'] = 'ASC';

$r = $application->getConn()->executeQuery('SELECT id, rss_url, iduser FROM rss_import_list ORDER BY ' . $_REQUEST['sort'] . ' ' . $_REQUEST['dir']);
while ($f = $r->fetch()) {
    $user = \Cetera\User::getById($f['iduser']);
    $f['user'] = $user->getName();
    $data[] = $f;
}

foreach ($data as $key => $rss) {
    $catalogs = [];
    $r = $application->getConn()->executeQuery('SELECT id, idcat FROM rss_import_list_dirs WHERE idrss=' . (int)$rss['id']);
    while ($f = $r->fetch()) $catalogs[] = $f;
    $tmpCatsName = [];
    foreach ($catalogs as $cat) {
        $c = \Cetera\Catalog::getById($cat['idcat']);
        $tmpCatsName[] = $c->getName();
    }
    $data[$key]['cats'] = implode(', ', $tmpCatsName);
}


echo json_encode(array(
    'success' => true,
    'rows' => $data
));
