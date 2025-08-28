<?php


$t = $this->getTranslator();
$t->addTranslation(__DIR__ . '/lang');

if ($this->getBo() && $this->getUser() && $this->getUser()->isAdmin()) {

    $this->getBo()->addModule(array(
        'id' => 'rss_import',
        'position' => MENU_SITE,
        'name' => $t->_('Импорт RSS'),
        'icon' => '',
        'iconCls' => 'x-fa fa-directions',
        'class' => 'Plugin.rss-import.Panel'
    ));

}


$this->registerCronJob(__DIR__ . '/scripts/cron.php');
