<?php
$model = new Models_Catalog;

$model->category_id = MG::get('category')->getCategoryList();
$model->category_id[] = 0;

$listCategories = MG::get('category')->getCategoryTitleList();
$arrayCategories = $model->category_id = MG::get('category')->getHierarchyCategory(0);
$this->select_categories = MG::get('category')->getTitleCategory($arrayCategories);
$this->categories = MG::get('category')->getCategoryListUl(0, 'admin');

$this->countCategory =  MG::get('category')-> getCategoryCount();