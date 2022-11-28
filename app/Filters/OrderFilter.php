<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class OrderFilter extends Filter {

    function status(array $value = []): Builder {
        return $this->builder->whereIn('status', $value);
    }

    function farmerId($id): Builder {
        return $this->builder->where('farmer_id', $id);
    }
}
