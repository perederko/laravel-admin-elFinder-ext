<?php

namespace Perederko\Laravel\Ext\Admin\ElFinder;

use Encore\Admin\Extension;
use Illuminate\Foundation\Application;
use Encore\Admin\Admin;
use Illuminate\Routing\Router;

class ElFinder extends Extension
{
    const PACKAGE = 'perederko/laravel-admin-elfinder-ext';
    const VIEW_NAMESPACE = 'admin-elfinder';
    
    /**
     * The Illuminate application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;
    
    /**
     * ElFinder constructor.
     *
     * @param Application|null $app
     */
    public function __construct(Application $app = null)
    {
        $this->app = $app ?: app();
    }
    
    /**
     * {@inheritdoc}
     */
    public static function boot()
    {
        static::registerRoutes();
        
        Admin::extend('admin-elfinder', __CLASS__);
    }
    
    /**
     * Register routes for laravel-admin.
     *
     * @return void
     */
    public static function registerRoutes()
    {
        parent::routes(
            function (Router $router) {
                /* @var \Illuminate\Routing\Router $router */
                /**@var \Illuminate\Config\Repository $config * */
                $config = app('config');
                $attributes = $config->get('admin-elfinder.route', []);
                $attributes['prefix'] = 'elfinder';
                $attributes['namespace'] = 'Perederko\Laravel\Ext\Admin\ElFinder';
                
                $router->group(
                    $attributes, function (Router $router) {
                    $router->get('/', ['as' => 'admin-elfinder.index', 'uses' => 'ElFinderController@index']);
                    $router->any(
                        'connector',
                        ['as' => 'admin-elfinder.connector', 'uses' => 'ElFinderController@connector']
                    );
                    $router->get(
                        'popup/{input_id}',
                        ['as' => 'admin-elfinder.popup', 'uses' => 'ElFinderController@popup']
                    );
                    $router->get(
                        'filepicker/{input_id}',
                        ['as' => 'admin-elfinder.filepicker', 'uses' => 'ElFinderController@filePicker']
                    );
                    $router->get('tinymce', ['as' => 'admin-elfinder.tinymce', 'uses' => 'ElFinderController@tinyMCE']);
                    $router->get(
                        'tinymce4', ['as' => 'admin-elfinder.tinymce4', 'uses' => 'ElFinderController@tinyMCE4']
                    );
                    $router->get(
                        'ckeditor', ['as' => 'admin-elfinder.ckeditor', 'uses' => 'ElFinderController@ckeditor4']
                    );
                }
                );
            }
        );
    }
    
    public static function checkAccess($attr, $path, $data, $volume)
    {
        return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
            ? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
            : null;                                    // else elFinder decide it itself
    }
    
    public static function import()
    {
        static::addToMenu();
        /**@var \Illuminate\Config\Repository $config * */
        $config = app('config');
        $path = $config->get('admin.route.prefix', 'admin') . '/elfinder/*';
        parent::createPermission('File manager', 'ext.admin-elfinder', $path);
    }
    
    public static function addToMenu()
    {
        /**@var Menu $root * */
        $root = Menu::where('title', 'Files')
                    ->orWhere('title', 'Filesystem')
                    ->first();
        
        $lastOrder = Menu::max('order');
        
        Menu::create(
            [
                'parent_id' => $root ? $root->id : 0,
                'order'     => ++$lastOrder,
                'title'     => 'File manager',
                'uri'       => 'elfinder',
                'icon'      => 'fa-keyboard-o'
            ]
        );
    }
}
