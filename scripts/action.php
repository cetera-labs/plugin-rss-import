<?php
/**
 * Cetera CMS 3
 *
 * AJAX-backend действия с RSS-импортом
 *
 * @package CeteraCMS
 * @version $Id$
 * @copyright 2000-2010 Cetera labs (http://www.cetera.ru)
 * @author Igor Samarin
 *
 **/


include_once('common_bo.php');
global $application;


$res = [
    'success' => false,
    'errors' => []
];

$action = $_POST['action'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

switch ($action) {
    case 'save_list':
        if (!isset($_POST['catalogs']) || !is_array($_POST['catalogs']) || count($_POST['catalogs']) === 0) {
            $res['message'] = 'Необходимо указать хотя бы один раздел.';
            \RssImport\Utils::respondAndExit($res);
        }

        try {
            \RssImport\Utils::validateRssByPath($_POST['rss_url']);
        } catch (Exception $e) {
            $res['message'] = $e->getMessage();
            \RssImport\Utils::respondAndExit($res);
        }

        $query = 'rss_import_list SET rss_url=?, iduser=?';
        if ($id) {
            $query = 'UPDATE ' . $query . ' WHERE id=' . $id;
        } else {
            $query = 'INSERT INTO ' . $query;
        }
        $userID = $application->getUser()->getId();
        $application->getConn()->executeQuery($query, [$_POST['rss_url'], $userID]);
        if (!$id) {
            $id = $application->getConn()->lastInsertId();
        }

        $application->getConn()->executeQuery('DELETE FROM rss_import_list_dirs WHERE idrss=' . (int)$id);
        foreach ($_POST['catalogs'] as $cid) {
            $application->getConn()->executeQuery(
                'INSERT INTO rss_import_list_dirs SET idrss=' . (int)$id . ', idcat=' . (int)$cid
            );
        }
        $res['success'] = true;
        \RssImport\Utils::respondAndExit($res);
        break;

    case 'get_list':
        $reqId = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
        $res['data'] = $application->getConn()->fetchAssoc('SELECT * FROM rss_import_list WHERE id=' . $reqId);
        $res['success'] = true;
        \RssImport\Utils::respondAndExit($res);
        break;

    case 'delete_rss':
        if ($id) {
            $application->getConn()->executeQuery('DELETE FROM rss_import_list WHERE id=' . $id);
            $application->getConn()->executeQuery('DELETE FROM rss_import_list_dirs WHERE idrss=' . $id);
            $res['success'] = true;
        } else {
            $res['errors'][] = 'No ID';
        }
        \RssImport\Utils::respondAndExit($res);
        break;


    case 'delete':
    {

        $imported = $application->getConn()->fetchAssoc('SELECT * FROM rss_import_imported_materials WHERE id=' . $id);

        $mId = $imported['material_id'];
        $catId = $imported['idcat'];

        $catalog = \Cetera\Catalog::getById($catId);
        $material = \Cetera\Material::getById($mId, $catalog->getMaterialsObjectDefinition());

        if ($material) {
            $material->delete();
            $application->getConn()->executeQuery('DELETE FROM rss_import_imported_materials WHERE id=' . $id);
            $res['success'] = true;
        }
        \RssImport\Utils::respondAndExit($res);
        break;
    }

    default:
        $res['errors'][] = 'Unknown action';
        \RssImport\Utils::respondAndExit($res);
}
