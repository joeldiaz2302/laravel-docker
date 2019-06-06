<?php
/**
 * @author joel diaz
*/
namespace App\Http\Controllers;

use App\Http\Controllers\RESTController;

class UsersController extends RESTController
{
    /**
     * The model class we need to run basic CRUD operations
     * @return string The \ escaped class string
     */
    public function getCrudModelClass()
    {
        return '\\App\\Models\\User';
    }
}