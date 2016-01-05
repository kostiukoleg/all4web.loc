<?php
 /**
 *  Файл представления Feedback - выводит сгенерированную движком информацию на странице обратной связи.
 *  В этом файле доступны следующие данные:
 *   <code>
 *    $data['message'] => Сообщение,
 *    $data['dislpayForm'] => Флаг скрывающий форму,
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

<h1 class="new-products-title">Обратная связь</h1>

<?php 
if(!empty($data['html_content'])&&$data['html_content']!='&nbsp;'){?>
<div class="pade-desc">
  <?php echo $data['html_content'] ?>
</div>
<?php }?>

<?php if(!empty($data['error'])){?>
<div class="msgError">
  <?php	echo $data['error']; ?>
</div>
<?php }?>

<div class="feedback-form-wrapper">
<?php if($data['dislpayForm']){ ?>
	<p class="auth-text">Для связи с нами заполните форму ниже.</p>
	<form action="" method="post">
		<ul class="form-list">
			<li>Ф.И.О.:</li>
			<li><input type="text" name="fio" value="<?php echo !empty($_POST['fio'])?$_POST['fio']:'' ?>"></li>
			<li>Email:<span class="red-star">*</span></li>
			<li><input type="text" name="email" value="<?php echo !empty($_POST['email'])?$_POST['email']:'' ?>"></li>
			<li>Сообщение:<span class="red-star">*</span></li>
			<li><textarea class="address-area" name="message"><?php echo !empty($_REQUEST['message'])?$_REQUEST['message']:'' ?></textarea></li>
		  <?php  if(MG::getSetting('useCaptcha')=="true"){ ?>
        <li>Введите текст с картинки:</li>
        <li><img src = "captcha.html" width="140" height="36"></li>
        <li><input type="text" name="capcha" class="captcha"></li>
	  	<?php }?>
		</ul>
		<input type="submit" name="send" class="default-btn" value="Отправить сообщение">
	</form>
	<div class="clear">&nbsp;</div>


<?php } else { ?>
  <div class='successSend'> <?php echo $data['message']?> </div>
<?php }; ?>
</div>
