<?php

namespace RssImport;

use Exception;

class Importer
{


    /**
     * @var array<RssFeed> $feeds
     */
    protected array $feeds = [];


    public function __construct()
    {

        $a = \Cetera\Application::getInstance();
        $a->connectDb();
        $conn = $a->getConn();
        $table = Constants::TABLE_NAME_RSS_LIST->value;
        $r = $conn->executeQuery('SELECT id, rss_url, iduser FROM ' . $table);
        while ($f = $r->fetch()) {
            $this->feeds[] = new RssFeed($f['id'], $f['rss_url']);
        }
    }


    /**
     * @throws Exception
     */
    public function process(): void
    {
        foreach ($this->feeds as $feed) {
            $feed->import();
        }
    }

}
