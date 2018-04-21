<?php

namespace MsNatali\BitrixDebug;

class DebugVar
{
    /** @var DebugVar */
    static $debug;
    /** @var string Название куки куда запоминаем переключение */
    protected $cookieName = 'show_panel_debug_var';
    protected $cut_dir;
    protected $vars = [];

    /**
     * DebugVar constructor.
     * @param string $cut_dir
     * @param string $cookie_name
     */
    protected function __construct($cut_dir = '', $cookie_name = '')
    {
        $this->cut_dir = $cut_dir ?: realpath($_SERVER['DOCUMENT_ROOT'] . '/../');

        if ($cookie_name) {
            $this->cookieName = $cookie_name;
        }
    }

    /**
     * Получить объект отладки
     * @param string $cut_dir часть пути до файла, которую не нужно выводить в попап
     * @param string $cookie_name название куки, куда будет сохраняться информация о текущем режиме отладки (вкл/выкл)
     */
    public static function register($cut_dir = '', $cookie_name = '')
    {
        if (!is_null(static::$debug)) {
            return;
        }

        $em = \Bitrix\Main\EventManager::getInstance();
        $em->addEventHandler('main', 'OnPanelCreate', [DebugVarHandler::class, "addDebugVarButtonToTopPanel"]);
        $em->addEventHandler('main', 'OnBeforeProlog', [DebugVarHandler::class, "setCookie"]);
        $em->addEventHandler('main', 'OnAfterEpilog', [DebugVarHandler::class, "showPanel"]);

        static::$debug = new static($cut_dir, $cookie_name);
    }

    /**
     * Получить объект отладчика
     */
    public static function get()
    {
        if (is_null(static::$debug)) {
            static::register();
        }

        return static::$debug;
    }

    /**
     * Включена ли отладка данных
     */
    public function isDebug()
    {
        global $USER;
        if (!$USER->IsAdmin()) {
            return false;
        }
        return isset($_COOKIE[$this->cookieName]) && $_COOKIE[$this->cookieName] === 'Y';
    }

    /**
     * Добавить информацию о переменной в отладчик
     * @param mixed $var переменная, информацию о которой необходимо вывести в отладчик
     * @param string $name название переменной. По умолчанию будет использовано реальное название переменной или No Name
     * @param int $backtrace_i порядковый номер элемента стека вызова, который будет использоваться для получения информации о файле и строке вызова
     */
    public function debug($var, $name = '', $backtrace_i = 0)
    {
        if (!$this->isDebug()) {
            return;
        }

        $debug = debug_backtrace();
        $trace = str_replace($this->cut_dir, '', $debug[$backtrace_i]['file']) . ':' . $debug[$backtrace_i]['line'];
        if (!$name) {
            $vLine = file($debug[0]['file']);
            $fLine = $vLine[$debug[0]['line'] - 1];
            preg_match("#\\$(\w+)#", $fLine, $match);
            $name = "Переменная: " . ($match[0] ?: 'No Name');
        }

        $key = $name . $trace;
        if (!$this->vars[$key]) {
            $this->vars[$key] = ['name' => $name, 'trace' => $trace];
        }

        $this->vars[$key]['vars'][] = $var;
    }

    /**
     * Получить собранную информацию о переменных
     * @return array
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * Получить название куки
     * @return string
     */
    public function getCookieName()
    {
        return $this->cookieName;
    }
}