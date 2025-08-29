<?php

namespace RssImport;

use Laminas\Feed\Reader\Reader;

class RssFeed
{
    protected int $id;
    protected array $cats = [];
    protected string $url;
    protected int $userID;

    public function __construct(int $id, string $url, int $userID = 1)
    {
        $this->id = $id;
        $this->url = $url;
        $this->userID = $userID;
        $this->fillCategories();
    }

    private function fillCategories(): void
    {
        $a = \Cetera\Application::getInstance();
        $a->connectDb();
        $conn = $a->getConn();
        $table = Constants::TABLE_NAME_CATEGORIES->value;
        $r = $conn->executeQuery('SELECT idcat FROM ' . $table . ' WHERE idrss = ?', [$this->id]);

        while ($f = $r->fetch()) {
            $this->cats[] = (int)$f['idcat'];
        }
    }

    /**
     * Импортирует все записи из RSS-ленты.
     * @throws \Exception
     */
    public function import(): void
    {
        try {
            $feed = Reader::import($this->url);
            foreach ($feed as $entry) {
                $this->importEntry($entry);
            }
        } catch (\Exception $e) {

            //add logs
            throw $e;
        }
    }

    /**
     * Импортирует одну запись RSS.
     * @param $entry
     */
    private function importEntry($entry): void
    {
        $url = $entry->getLink();
        $guid = $entry->getId();


        foreach ($this->cats as $cat) {
            $im = new ImportedMaterial($guid, $url, $cat);

            if ($im->isExist()) {
                continue;
            }
            $title = $entry->getTitle();
            $content = $entry->getContent();
            $enclosure = $entry->getEnclosure();
            $description = $entry->getDescription();
            $importedImage = '';

            if ($enclosure) {
                try {
                    $importedImage = Utils::processImage($enclosure->url);
                } catch (\Exception $e) {
                    // add logs
                }
            }

            $cmsCatalog = \Cetera\Catalog::getById($cat);
            $od = $cmsCatalog->getMaterialsObjectDefinition();
            $mData = $this->buildMaterialData($title, $content, $description, $importedImage, $cat);

            try {
                $res = \Cetera\Material::fetch($mData, $od);
                $res->save();
                $id = $res->getId();
                $im->save($id);
            } catch (\Exception $e) {
                // add logs
            }

        }
    }

    /**
     * Формирует массив данных для материала.
     * @param string $title
     * @param string $content
     * @param string $description
     * @param string $importedImage
     * @param int $cat
     * @return array
     */
    private function buildMaterialData(string $title, string $content, string $description, string $importedImage, int $cat): array
    {
        return [
            'publish' => true,
            'tag' => 100,
            'id' => null,
            'autor' => $this->userID,
            'idcat' => $cat,
            'name' => $title,
            'alias' => strtolower(translit($title)),
            'pic' => $importedImage,
            'text' => $content,
            'short' => $description
        ];
    }
}
