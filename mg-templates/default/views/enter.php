<?php
/**
 *  Файл представления Enter - выводит сгенерированную движком информацию на странице сайта авторизации пользователей.
 *  В этом файле доступны следующие данные:
 *   <code>
 *    $data['msgError'] => Сообщение об ошибке авторизации,
 *    $data['meta_title'] => 'Значение meta тега для страницы '
 *    $data['meta_keywords'] => 'Значение meta_keywords тега для страницы '
 *    $data['meta_desc'] => 'Значение meta_desc тега для страницы '
 *   </code>
 *   
 *   Получить подробную информацию о каждом элементе массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>     
 *    <php viewData($data['msgError']); ?>  
 *   </code>
 * 
 *   Вывести содержание элементов массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>     
 *    <php echo $data['msgError']; ?>  
 *   </code>
 * 
 *   <b>Внимание!</b> Файл предназначен только для форматированного вывода данных на страницу магазина. Категорически не рекомендуется выполнять в нем запросы к БД сайта или реализовывать сложую программную логику логику.
 *   @author Авдеев Марк <mark-avdeev@mail.ru>
 *   @package moguta.cms
 *   @subpackage Views
 */
// Установка значений в метатеги title, keywords, description.
mgSEO($data);
?>

<h1 class="new-products-title">Авторизация пользователя</h1>
<?php echo!empty($data['msgError'])?$data['msgError']:'' ?>
<div class="user-login">
  <h2>Зарегистрированный пользователь</h2>
  <p class="custom-text">Если Вы уже зарегистрированы у нас в интернет-магазине, пожалуйста авторизуйтесь.</p>
  <form action="<?php echo SITE ?>/enter" method="POST">
    <ul class="form-list">
      <li>Email:<span class="red-star">*</span></li>
      <li><input type = "text" name = "email" value = "<?php echo!empty($_POST['email'])?$_POST['email']:'' ?>"></li>
      <li>Пароль:<span class="red-star">*</span></li>
      <li><input type="password" name="pass"></li>
      <?php echo!empty($data['checkCapcha'])?$data['checkCapcha']:'' ?>
    </ul>
    <a href="<?php echo SITE ?>/forgotpass" class="forgot-link">Забыли пароль?</a>
    <button type="submit" class="enter-btn default-btn">Войти</button>
  </form>
</div>