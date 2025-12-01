<?php
namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use LogsActivity;
    protected $fillable = ['name', 'route', 'parent_id', 'icon'];

    // Role relationship (many-to-many)
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'menu_role');
    }

    // Parent menu
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    // Child menus
    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id');
    }

}
