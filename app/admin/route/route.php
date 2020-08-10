<?php

use think\facade\Route;

Route::get('login', 'admin/account/login');
Route::post('checkLogin', 'admin/account/checkLogin');
Route::get('logout', 'admin/account/logout');


Route::get('home', 'admin/home/index');
Route::get('personalCenter', 'admin/home/personalCenter');
Route::get('welcome', 'admin/home/welcome');

// 我的文章
Route::get('MyArticle/myArticle/[:page]', 'admin/MyArticle/myArticle');
Route::get('MyArticle/addModifyArticle/[:articleId]', 'admin/MyArticle/addModifyArticle');
Route::post('MyArticle/delArticle', 'admin/MyArticle/delArticle');
Route::post('MyArticle/save', 'admin/MyArticle/save');

// 文章分类
Route::get('ArticleClass/articleClass/[:type]/[:value]', 'admin/ArticleClass/articleClass');
Route::post('ArticleClass/saveClass', 'admin/ArticleClass/saveClass');

// 文章审核
Route::get('ArticleCheck/checkArticle/[:page]', 'admin/ArticleCheck/checkArticle');
Route::get('ArticleCheck/viewArticle/:articleId', 'admin/ArticleCheck/viewArticle');
Route::get('ArticleCheck/modifyAdvice/:articleId', 'admin/ArticleCheck/modifyAdvice');