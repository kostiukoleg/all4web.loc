<?php
 /**
 *  Файл представления Forgotpass - выводит сгенерированную движком информацию на странице востановления пароля.
 *  В этом файле доступны следующие данные:
 *   <code>
 *    $data['error'] => Сообщение об ошибке.
 *    $data['message'] => Информационное сообщение.
 *    $data['form'] =>  Отображение формы,
 *    $data['meta_title'] => 'Значение meta тега для страницы '
 *    $data['meta_keywords'] => 'Значение meta_keywords тега для страницы '
 *    $data['meta_desc'] => 'Значение meta_desc тега для страницы '
 *   </code>
 *   
 *   Получить подробную информацию о каждом элементе массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>     
 *    <php viewData($data['message']); ?>  
 *   </code>
 * 
 *   Вывести содержание элементов массива $data, можно вставив следующую строку кода в верстку файла.
 *   <code>     
 *    <php echo $data['message']; ?>  
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
<h1 class="new-products-title">Восстановление пароля</h1>
<!--Вывод сообщения об успешной отправке-->
<?php if($data['message']):?>
	<div class="alert-info"><?php echo $data['message']?></div>
<?php endif; ?>

<!--Вывод сообщения об ошибке-->
	<?php if($data['error']):?>
		<div class="mg-error"><?php echo $data['error']?></div>
	<?php endif;?>
	
	<?php switch($data['form']){case 1: ?>
    <div class="restore-pass">
        <p class="custom-text">На адрес электронной почты будет отправлена инструкция по восстановлению пароля.</p>
        <form action = "<?php echo SITE?>/forgotpass" method = "POST">
            <ul class="form-list">
                <li><input type = "text" name = "email" value="Email" onfocus="if (this.value == 'Email') {this.value = '';}" onblur="if (this.value == '') {this.value = 'Email';}"></li>
            </ul>
            <input type = "submit" name="forgotpass" class="enter-btn default-btn" value = "Отправить" >
        </form>
    </div>
	<?php break; case 2: ?>
    <div class="restore-pass">
        <form action="<?php echo SITE?>/forgotpass" method="POST">
            <ul class="form-list">
                <li>Новый пароль (не менее 5 символов):</li>
                <li><input type = "password" name = "newPass"></li>
                <li>Подтвердите новый пароль:<span class="red-star">*</span></li>
                <li><input type="password" name="pass2"></li>
            </ul>
            <div class="clear"></div>
            <input type = "submit" class="enter-btn default-btn" name="chengePass" value = "Сохранить">
        </form>
    </div>
	<?php } ?>