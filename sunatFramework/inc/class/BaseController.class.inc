<?php

abstract class BaseController
{

    const PARAM_MODE         = '_action';
    const METHOD_POSTFIX     = 'Action';
    //
    const SEARCH_TARGET_CORE = 1;
    const SEARCH_TARGET_WIDE = 2;

    protected $db;
    protected $env;
    protected $viewParams;
    protected $coreBook;
    protected $user;
    protected $userInfo;
    protected $loginInfo;

    /**
     * コンストラクタ.
     */
    public function __construct()
    {
        $this->db         = new mysql_db();
        $this->env        = new Env();
        $this->viewParams = array();
    }



    /**
     *
     */
    protected function preProcess()
    {

    }

    /**
     *
     * @throws Exception
     */
    protected function process()
    {

        $mode       = isset($_REQUEST[self::PARAM_MODE]) ? $_REQUEST[self::PARAM_MODE] : 'default';
        $methodName = $mode . self::METHOD_POSTFIX;
        $this->setViewParam('actionKey', self::PARAM_MODE);
        if (!method_exists($this, $methodName)) {
            throw new Exception();
        }

        call_user_func(array($this, $methodName), array());
    }

    /**
     *
     */
    public function execute()
    {
        session_start();
        $this->user = UserLogin::getInstance();
        if (!$this->user->isUserLogin()) {
            redirect('/login.php');
        }
        // 検索ターゲット種別
        $this->coreBook = $this->getCoreBook();
        $this->setViewParam('core_book', $this->coreBook);

        // ユーザー情報
        $this->userInfo  = $this->db->getRow('*', 'user', 'id = ?', array($this->user->id));
        $userInfo            = $this->userInfo;
        $userInfo['user_id'] = $userInfo['id'];
        unset($userInfo['id']);
        $this->setViewParams($userInfo);
        $this->setViewParam('timestamp', time());

        $this->loginInfo = $_SESSION['login_info'];

        $this->preProcess();
        $this->process();
    }

    /**
     * core_book取得.
     *
     * @return int core_book
     */
    protected function getCoreBook()
    {
        $form                 = new Form;
        $form->get->core_book = new FormFieldInt(FormField::TRIM | FormField::NOT_NULL);
        list($result, $value) = $this->getFormParams($form);
        if (!$result || !isset($value['core_book'])) {
            $sess = new SessionReference('*list.management', SessionReference::INIT);
            if (isset($sess->vars['core_book'])) {
                return $sess->vars['core_book'];
            }
            $this->user->error('アクセスエラー', '不正なアクセスです。');
        }
        return $value['core_book'];
    }


    /**
     *
     * @param Form $form
     * @return type
     */
    protected function getFormParams(Form $form)
    {
        try {
            $ret    = $form->get();
            $values = $ret['in'];
            return array(true, $values, $ret);
        } catch (FormCheckException $e) {
            $err    = $e->getValues();
            $values = $err['errors'];
            foreach ($values as $key => $val) {
                $error_key          = preg_replace('/:error$/s', '_error', $key);
                $values[$error_key] = $val;
            }
            return array(false, $values, $err);
        }
    }

    /**
     *
     * @param type $key
     * @param type $params
     */
    protected function setViewParam($key, $params)
    {
        if (!isset($this->viewParams)) {
            $this->viewParams = array();
        }
        $this->viewParams[$key] = $params;
    }

    /**
     *
     * @param array $params
     * @return type
     */
    protected function setViewParams(array $params)
    {
        if (!is_array($params)) {
            return;
        }
        foreach ($params as $key => $param) {
            $this->setViewParam($key, $param);
        }
    }

    /**
     * テンプレート表示.
     *
     * @param string $tpl テンプレート
     */
    protected function viewTemplate($tpl, $params = array())
    {
        $this->setViewParams($params);
        $twigPath = dirname(dirname(__DIR__)) . '/design_template';
        $values   = $this->viewParams;

        $app = new Silex\Application();
        $app->register(new Silex\Provider\TwigServiceProvider(), array(
            'twig.path' => $twigPath,
        ));
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $app->post('/', function() use($app, $values, $tpl) {
                return $app['twig']->render($tpl, $values);
            });
        } else {
            $app->get('/', function() use($app, $values, $tpl) {
                return $app['twig']->render($tpl, $values);
            });
        }
        $app->run();
    }

    /**
     * Jsonをレスポンスで返す.
     *
     * @param array $data データ
     */
    protected function sendJson($data = array())
    {
        // JSON形式
        header('Content-Type: application/json; charset=UTF-8');
        // IEがContent-Typeヘッダーを無視してコンテンツの内容を解析するのを防ぐ
        header('X-Content-Type-Options: nosniff');
        // JSONで出力する
        echo json_encode($data);
        exit;
    }

}
