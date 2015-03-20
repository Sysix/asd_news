<?php

class rex_asd_news_config
{
    const OLD_SQL_COLUMNS = '|category|picture|text|';

    public static $tableName;
    public static $tableNameCategory;


    private static $defaultConfigFile;
    public static $configFile;

    private static $defaultConfig;
    private static $saveConfig;
    public static $config;

    public static $folderName;

    private static $baseUrl;

    public static $seoAddon;
    public static $urlControlPlugin = false;


    /**
     * get an entry from the config
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function getConfig($name, $default = null)
    {
        if (isset(self::$config[$name])) {
            return self::$config[$name];
        }

        return $default;
    }

    /**
     * @param $name
     * @param $value
     */
    public static function addSaveConfig($name, $value)
    {
        self::$saveConfig[$name] = $value;
    }

    /**
     * @param array $list
     */
    public static function addSaveConfigs(array $list)
    {
        foreach ($list as $name => $value) {
            self::addSaveConfig($name, $value);
        }
    }

    /**
     * save the config
     * @return int
     */
    public static function saveConfig()
    {
        $config = array_merge(self::$defaultConfig, self::$config, self::$saveConfig);

        self::$config = $config;

        return file_put_contents(self::$configFile, json_encode($config));
    }


    /**
     * @return string
     */
    public static function getTable()
    {
        return self::$tableName;
    }

    /**
     * @return string
     */
    public static function getTableCategory()
    {
        return self::$tableNameCategory;
    }

    /**
     * get the base url
     * @param null|string $func
     * @return string
     */
    public static function getBaseUrl($func = null)
    {
        if ($func == null) {
            return self::$baseUrl;
        }

        return self::$baseUrl . '&func=' . $func;
    }

    /**
     * return the addon name
     * @return string
     */
    public static function getName()
    {
        return self::$folderName;
    }

    /**
     * @return string
     */
    public static function getSeoAddon()
    {
        return self::$seoAddon;
    }

    /**
     * @return bool
     */
    public static function isControlPlugin()
    {
        return self::$urlControlPlugin;
    }

    /**
     * @return bool
     */
    public static function createDataConfigIfNotExists()
    {
        if (!file_exists(rex_path::addonData(self::getName(), 'config.json'))) {
            return rex_dir::copy(
                rex_path::addon(self::getName(), 'data'),
                rex_path::addonData(self::getName())
            );
        }

        return true;
    }

    /**
     * set all necessary information about the addon
     */
    public static function init($folder, $table, $tableCategory)
    {
        global $REX;

        self::$folderName = $folder;

        self::$tableName = $REX['TABLE_PREFIX'] . $table;
        self::$tableNameCategory = $REX['TABLE_PREFIX'] . $tableCategory;

        self::$defaultConfigFile = rex_path::addon(self::$folderName, 'data/config.json');
        self::$configFile = rex_path::addonData(self::$folderName, 'config.json');

        self::$defaultConfig = json_decode(file_get_contents(self::$defaultConfigFile), true);
        self::$config = json_decode(file_get_contents(self::$configFile), true);

        self::setBaseUrl();
    }

    /**
     * set the addon baseUrl
     */
    private static function setBaseUrl()
    {
        $subpage = rex_request('subpage');

        self::$baseUrl = 'index.php?page=' . self::$folderName . '&amp;subpage=' . $subpage;
    }

    /**
     * @param $addons,...
     */
    public static function setSeoAddon($addons)
    {
        $addons = func_get_args();

        foreach ($addons as $addon) {
            if (OOAddon::isAvailable($addon)) {
                self::$seoAddon = $addon;

                // Kompatibilität erhalten
                rex_asd_news::$SEO_ADDON = self::$seoAddon;
            }
        }

        self::$urlControlPlugin = OOPlugin::isAvailable(self::$seoAddon, 'url_control');

        // Kompatibilität erhalten
        rex_asd_news::$SEO_URL_CONTROL = self::$urlControlPlugin;
    }

}

?>