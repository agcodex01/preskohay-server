<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     *  setting global variable
     */
    public $data = [];

    /**
     *  setting global default error msg
     */
    public function __construct()
    {
        $this->data['error'] = true;
        $this->data['message'] = 'Something went wrong.';
    }

    /**
     *  common soft delete
     * @param Model $param
     *
     * return array
     */
    public function tempDelete($param)
    {
        $deleted = $param->delete();

        if ($deleted) {
            $this->data['error'] = false;
            $this->data['message'] = 'Successfully Deleted.';
        }

        return $this->data;
    }
}
