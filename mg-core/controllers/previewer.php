<?php

/**
 * Контроллер Previewer
 *
 * Класс Controllers_Previewer показывает как будет выглядеть редактируемая страница.
 *
 * @author Авдеев Марк <mark-avdeev@mail.ru>
 * @package moguta.cms
 * @subpackage Controller
 */
class Controllers_Previewer extends BaseController {

  function __construct() {
    if (!USER::isAuth() || '1' != USER::getThis()->role) {
      MG::redirect('/');
    }

    $this->data = array('content' => $_POST['content']);
  }

}