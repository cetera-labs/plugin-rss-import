<?php
/************************************************************************************************
 *
 * Список материалов
 *************************************************************************************************/

include_once('common_bo.php');

$data = array();

$list_id = (int)$_REQUEST['id'];

$r = $application->getConn()->executeQuery('SELECT idcat as id FROM rss_import_list_dirs WHERE idrss=' . $list_id);

while ($f = $r->fetch()) {
    try {
        $f['name'] = '';
        $c = \Cetera\Catalog::getById($f['id']);
        foreach ($c->getPath() as $item) {
            if ($item->isRoot()) continue;
            if ($f['name']) $f['name'] .= ' / ';
            $f['name'] .= $item->name;
        }
        $data[] = $f;
    } catch (Exception $e) {
    }
}

echo json_encode(array(
    'success' => true,
    'total' => sizeof($data),
    'rows' => $data
));
