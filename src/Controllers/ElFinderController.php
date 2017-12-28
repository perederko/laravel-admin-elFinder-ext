<?php

namespace Perederko\Laravel\Ext\Admin\ElFinder;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Application;
use Perederko\Laravel\Ext\Admin\ElFinder\Session\LaravelSession;

class ElFinderController extends Controller
{
    protected $package;
    
    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;
    
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->package = ElFinder::PACKAGE;
    }
    
    public function index()
    {
        return Admin::content(
            function (Content $content) {
                $content->header('ElFinder');
                $content->description('File manager');
                $content->body($this->getView('elfinder')->render());
            }
        );
    }
    
    public function tinyMCE()
    {
        return $this->getView('tinymce');
    }
    
    public function tinyMCE4()
    {
        return $this->getView('tinymce4');
    }
    
    public function ckeditor4()
    {
        return $this->getView('ckeditor4');
    }
    
    public function popup($input_id)
    {
        return $this->getView('standalonepopup', [compact('input_id')]);
    }
    
    public function filePicker($input_id)
    {
        $type = Request::input('type');
        $mimeTypes = implode(
            ',', array_map(
                   function ($t) {
                       return "'" . $t . "'";
                   }, explode(',', $type)
               )
        );
        
        return $this->getView('filepicker', [compact('input_id', 'type', 'mimeTypes')]);
    }
    
    public function connector()
    {
        $roots = $this->app->config->get('admin-elfinder.roots', []);
        if (empty($roots)) {
            $dirs = (array)$this->app['config']->get('admin-elfinder.dir', []);
            foreach ($dirs as $dir) {
                $roots[] = [
                    'driver'        => 'LocalFileSystem', // driver for accessing file system (REQUIRED)
                    'path'          => public_path($dir), // path to files (REQUIRED)
                    'URL'           => url($dir), // URL to files (REQUIRED)
                    'accessControl' => $this->app->config->get('admin-elfinder.access') // filter callback (OPTIONAL)
                ];
            }
            
            $disks = (array)$this->app['config']->get('admin-elfinder.disks', []);
            foreach ($disks as $key => $root) {
                if (is_string($root)) {
                    $key = $root;
                    $root = [];
                }
                $disk = app('filesystem')->disk($key);
                if ($disk instanceof FilesystemAdapter) {
                    $defaults = [
                        'driver'     => 'Flysystem',
                        'filesystem' => $disk->getDriver(),
                        'alias'      => $key,
                    ];
                    $roots[] = array_merge($defaults, $root);
                }
            }
        }
        
        if (app()->bound('session.store')) {
            $sessionStore = app('session.store');
            $session = new LaravelSession($sessionStore);
        } else {
            $session = null;
        }
        
        $rootOptions = $this->app->config->get('admin-elfinder.root_options', []);
        foreach ($roots as $key => $root) {
            $roots[$key] = array_merge($rootOptions, $root);
        }
        
        $opts = $this->app->config->get('admin-elfinder.options', []);
        $opts = array_merge($opts, ['roots' => $roots, 'session' => $session]);
        
        // run elFinder
        $connector = new Connector(new \elFinder($opts));
        $connector->run();
        return $connector->getResponse();
    }
    
    /**
     * @param string $name
     * @param array $with
     * @return View
     */
    public function getView(string $name, array $with = []): View
    {
        /** @var \Illuminate\View\Factory $viewFactory */
        $viewFactory = $this->app['view'];
    
        $view = $viewFactory->make(ElFinder::VIEW_NAMESPACE . '::' . $name)->with($this->getViewVars());
        
        if (!empty($with)) {
            foreach ($with as $arr) {
                $view->with($arr);
            }
        }
        
        return $view;
    }
    
    protected function getViewVars()
    {
        $dir = 'packages/' . $this->package;
        $locale = str_replace("-", "_", $this->app->config->get('app.locale'));
        if (!file_exists($this->app['path.public'] . "/$dir/js/i18n/elfinder.$locale.js")) {
            $locale = false;
        }
        $csrf = true;
        return compact('dir', 'locale', 'csrf');
    }
}
