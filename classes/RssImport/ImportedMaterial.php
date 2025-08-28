<?php

namespace RssImport;

use Cetera\Doctrine\DBAL\Connection;

class ImportedMaterial
{
    /**
     * @var string $guid GUID импортированной статьи из RSS-ленты
     */
    protected string $guid;
    /*
     * @var string $url URL импортированной статьи из RSS-ленты
     */
    protected string $url;
    /**
     * @var int $idcat ID раздела в каталоге
     */
    protected int $idcat;
    /**
     * @var int $materialId ID импортированного материала в каталоге
     */
    protected int $materialId;
    /**
     * @var int $id ID записи в таблице импортированных материалов
     */
    protected int $id;

    public function __construct(string $guid, string $url, int $idcat)
    {
        $this->guid = $guid;
        $this->url = $url;
        $this->idcat = $idcat;
        $this->materialId = 0;
    }

    /**
     * Сохраняет информацию о импортированной статье в базу данных, если она еще не существует.
     * @param int $materialId
     * @return void
     * @throws \Exception
     */
    public function save(int $materialId): void
    {
        if (!$this->isExist()) {
            $this->materialId = $materialId;
            $a = \Cetera\Application::getInstance();
            $a->connectDb();
            /** @var Connection $conn */
            $conn = $a->getConn();
            $conn->executeQuery(
                "INSERT INTO rss_import_imported_materials (guid, url, idcat, material_id, created_at) VALUES (?, ?, ?, ?, ?)",
                [
                    $this->guid,
                    $this->url,
                    $this->idcat,
                    $this->materialId,
                    date('Y-m-d H:i:s')
                ]
            );
        }
    }

    /**
     * Проверяет, существует ли статья из RSS фида с заданным GUID, URL и ID категории в базе данных.
     * @return bool
     * @throws \Exception
     */
    public function isExist(): bool
    {
        if ($this->materialId) {
            return true;
        }


        $a = \Cetera\Application::getInstance();
        $a->connectDb();
        $conn = $a->getConn();
        $table = Constants::TABLE_NAME_IMPORTED_MATERIALS->value;
        $r = $conn->executeQuery('SELECT * FROM ' . $table . ' WHERE guid = ? AND idcat = ? AND url = ?', [$this->guid, $this->idcat, $this->url]);
        if ($f = $r->fetch()) {
            $this->materialId = (int)$f['material_id'];
            return true;
        }
        return false;
    }


}
