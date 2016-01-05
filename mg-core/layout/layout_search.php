<?php mgAddMeta('<link type="text/css" href="'.SCRIPT.'standard/css/layout.search.css" rel="stylesheet"/>'); ?>
<?php mgAddMeta('<script type="text/javascript" src="'.SCRIPT.'standard/js/layout.search.js"></script>'); ?>

<div class="mg-search-block">
	<form method="get" action="<?php echo SITE?>/catalog" class="search-form">
		<input type="text" autocomplete="off" name="search" class="search-field" value="Ключевое слово" onfocus="if (this.value == 'Ключевое слово') {this.value = '';}" onblur="if (this.value == '') {this.value = 'Ключевое слово';}">
		<input type="submit" class="search-button" value="">
	</form>
	<div class="wraper-fast-result">
		<div class="fastResult">

		</div>
	</div>
</div>