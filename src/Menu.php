<?php

namespace Perederko\Laravel\Ext\Admin\ElFinder;

use Encore\Admin\Auth\Database\Menu as BaseMenu;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ElFinder
 * @package Perederko\Laravel\Ext\Admin\ElFinder
 *
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @see Builder::where()
 *
 * @method static Model create(array $attributes = [])
 * @see Builder::create()
 *
 * @method static integer max($column)
 * @see \Illuminate\Database\Query\Builder::max()
 *
 * @property integer $id
 */
class Menu extends BaseMenu
{

}
