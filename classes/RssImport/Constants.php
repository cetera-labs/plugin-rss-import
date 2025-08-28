<?php

namespace RssImport;

enum Constants: string
{
    case TABLE_NAME_RSS_LIST = 'rss_import_list';
    case TABLE_NAME_IMPORTED_MATERIALS = 'rss_import_imported_materials';
    case TABLE_NAME_CATEGORIES = 'rss_import_list_dirs';
}

