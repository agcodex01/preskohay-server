<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class OrderFilter extends Filter {

    function status(array $value = []): Builder {


        return $this->builder->whereIn('status', $value);
    }
}
